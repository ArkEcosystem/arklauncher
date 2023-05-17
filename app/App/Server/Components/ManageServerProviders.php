<?php

declare(strict_types=1);

namespace App\Server\Components;

use App\Enums\ServerProviderTypeEnum;
use App\SecureShell\Jobs\AddSecureShellKeyToServerProvider;
use App\Server\Jobs\IndexServerProviderImages;
use App\Server\Jobs\IndexServerProviderPlans;
use App\Server\Jobs\IndexServerProviderRegions;
use ARKEcosystem\Foundation\Fortify\Components\Concerns\InteractsWithUser;
use ARKEcosystem\Foundation\UserInterface\Support\Enums\FlashType;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Enums\TokenOnboardingStatusEnum;
use Domain\Token\Events\ServerProviderCreated;
use Domain\Token\Models\Token;
use Domain\Token\Rules\UniqueTokenExtraAttribute;
use Domain\User\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

final class ManageServerProviders extends Component
{
    use AuthorizesRequests;
    use InteractsWithUser;

    public Token $token;

    public Collection $providers;

    public string $provider;

    public ?string $name = null;

    public ?string $type = null;

    public ?string $access_token = null;

    public ?string $access_key = null;

    public bool $isSubmittingFirstProvider;

    /** @var mixed */
    protected $listeners = [
        'setProvider'            => 'selectProvider',
        'refreshServerProviders' => '$refresh',
    ];

    public function mount(Token $token): void
    {
        $this->token                     = $token;
        $this->providers                 = $token->serverProviders;
        $this->isSubmittingFirstProvider = false;
        $this->provider                  = ServerProviderTypeEnum::DIGITALOCEAN;
    }

    public function render(): View
    {
        return view('livewire.manage-server-providers', ['token' => $this->token, 'providers' => $this->providers, 'selectedProvider' => $this->provider]);
    }

    public function selectProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function store(): void
    {
        $this->authorize('create', [ServerProvider::class, $this->token]);

        $this->validate([
            'name'         => ['required', 'max:255', Rule::unique('server_providers', 'name')->where('token_id', $this->token->id)],
            'access_token' => ['required', 'max:255', new UniqueTokenExtraAttribute($this->token)],
            'access_key'   => ['required_if:type,===,aws', new UniqueTokenExtraAttribute($this->token)],
        ]);

        if (! $this->token->onboarding()->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS)) {
            $this->isSubmittingFirstProvider = true;

            $this->dispatchBrowserEvent('pending-first-provider', true);
        }

        $serverProvider       = new ServerProvider();
        $serverProvider->type = $this->provider;
        $serverProvider->extra_attributes->set([ServerAttributeEnum::ACCESS_TOKEN => $this->access_token]);

        if ($serverProvider->client()->valid()) {
            /** @var User $user */
            $user = $this->user;

            $serverProvider = $this->token->serverProviders()->create([
                'name'    => $this->name,
                'type'    => $this->provider,
            ]);

            $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);
            $serverProvider->setMetaAttribute(ServerAttributeEnum::ACCESS_TOKEN, $this->access_token);
            $serverProvider->setMetaAttribute(ServerAttributeEnum::ACCESS_KEY, $this->access_key);

            if ($this->hasRegisteredProviderOfSameType($serverProvider->type)) {
                $serverProvider->update(['provider_key_id' => $this->providers->where('type', '=', $serverProvider->type)->first()->provider_key_id]);
            } else {
                AddSecureShellKeyToServerProvider::dispatch($serverProvider, $user);
            }

            IndexServerProviderPlans::dispatch($serverProvider);
            IndexServerProviderRegions::dispatch($serverProvider);
            IndexServerProviderImages::dispatch($serverProvider);

            $this->providers->push($serverProvider);

            ServerProviderCreated::dispatch($serverProvider);

            if (count($this->providers) === 1) {
                alert('tokens.server-providers.added_success_redirect', FlashType::SUCCESS);
            } else {
                alert('tokens.server-providers.added_success', FlashType::SUCCESS);
            }
        } else {
            if (! $this->token->hasServerProviders()) {
                $this->isSubmittingFirstProvider = false;

                $this->dispatchBrowserEvent('pending-first-provider', false);
            }
            alert('tokens.server-providers.added_failed', FlashType::ERROR);
        }
    }

    private function hasRegisteredProviderOfSameType(string $type): bool
    {
        return $this->token->serverProviders()->where('type', $type)->count() > 1;
    }
}
