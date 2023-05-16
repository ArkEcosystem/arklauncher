<?php

declare(strict_types=1);

namespace App\Server\Components;

use ARKEcosystem\Foundation\Fortify\Components\Concerns\InteractsWithUser;
use Domain\Server\Enums\PresetTypeEnum;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Token\Events\ServerCreated;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Support\Helpers\Format;
use Support\Rules\BIP39Passphrase;

final class CreateServer extends Component
{
    use AuthorizesRequests;
    use InteractsWithUser;

    public Network $network;

    public ?string $plan = null;

    public string $preset = 'relay';

    public ?string $region = null;

    public string $serverName = '';

    public ServerProvider $selectedProvider;

    public string $selectedProviderType;

    public ?int $selectedProviderSelectedKey = null;

    public Token $token;

    public ?string $delegatePassphrase = null;

    public ?string $delegatePassword = null;

    /** @var mixed */
    protected $listeners = [
        'setPreset'   => 'selectPreset',
        'setProvider' => 'selectProvider',
    ];

    public function mount(Network $network): void
    {
        $this->token                = $network->token;
        $this->network              = $network;
        $this->selectedProvider     = $this->selectedProvider ?? $this->getUniqueProviders()->first();

        if ($network->servers_count === 0) {
            $this->preset = PresetTypeEnum::GENESIS;
        }
    }

    public function render(): View
    {
        if ($this->selectedProviderSelectedKey !== null) {
            $this->selectedProvider     = $this->token->serverProviders()->findOrFail($this->selectedProviderSelectedKey);
        }

        $this->selectedProviderType = $this->selectedProvider->type;

        return view('livewire.create-server');
    }

    public function updated(string $field): void
    {
        $this->validateOnly($field, [
            'delegatePassphrase' => ['nullable', new BIP39Passphrase()],
            'delegatePassword'   => ['nullable', 'alpha_num', 'min:8'],
        ]);
    }

    public function updatedSelectedProviderSelectedKey(): void
    {
        $this->plan   = null;
        $this->region = null;
    }

    public function selectPreset(string $preset): void
    {
        $this->preset = $preset;
    }

    public function selectProvider(int $providerId): void
    {
        $this->plan                        = null;
        $this->region                      = null;
        $this->selectedProviderSelectedKey = null;

        $this->selectedProvider = $this->token->serverProviders()->findOrFail($providerId);

        if ($this->getHasMultipleKeysOnProviderProperty()) {
            $this->selectedProviderSelectedKey = $this->selectedProvider->id;
        }
    }

    public function getHasMultipleKeysOnProviderProperty(): bool
    {
        return $this->token->serverProviders()->where('type', '=', $this->selectedProvider->type)->count() > 1;
    }

    public function getProviderEntriesProperty(): Collection
    {
        return $this->token->serverProviders()->where('type', '=', $this->selectedProvider->type)->get();
    }

    public function getRegionsProperty(): Collection
    {
        return $this->selectedProvider->regions;
    }

    public function getPlansProperty(): Collection
    {
        return $this->selectedProvider->plans;
    }

    public function getFormattedPlansProperty(): Collection
    {
        $region = $this->selectedProvider->regions()->findOrFail($this->region);

        // Search by string...
        $resultsByString = $this->selectedProvider->plans()
            ->where('memory', '>=', config('deployer.deployment.minimumServerRam'))
            ->when($this->preset === PresetTypeEnum::EXPLORER || $this->preset === PresetTypeEnum::GENESIS, fn ($query) => $query->where('cores', '>=', config('deployer.deployment.minimumCores')))
            ->whereJsonContains('regions', [$region->uuid])
            ->get();

        if ($resultsByString->count() > 0) {
            $plans = $resultsByString;

            return $plans;
        }

        // Search by integer...
        $resultsByNumber = $this->selectedProvider->plans()
            ->where('memory', '>=', config('deployer.deployment.minimumServerRam'))
            ->when($this->preset === PresetTypeEnum::EXPLORER || $this->preset === PresetTypeEnum::GENESIS, fn ($query) => $query->where('cores', '>=', config('deployer.deployment.minimumCores')))
            ->whereJsonContains('regions', [(int) $region->uuid])
            ->get();

        if ($resultsByNumber->count() > 0) {
            $plans = $resultsByNumber;

            return $plans;
        }

        // Server Provider without regions...
        $plans = $this->selectedProvider->plans()
            ->where('memory', '>=', config('deployer.deployment.minimumServerRam'))
            ->when($this->preset === PresetTypeEnum::EXPLORER || $this->preset === PresetTypeEnum::GENESIS, fn ($query) => $query->where('cores', '>=', config('deployer.deployment.minimumCores')))
            ->get();

        return $plans;
    }

    public function getPresetsProperty(): array
    {
        return [
            PresetTypeEnum::SEED,
            PresetTypeEnum::RELAY,
            PresetTypeEnum::FORGER,
            PresetTypeEnum::EXPLORER,
        ];
    }

    public function getUniqueProviders(): Collection
    {
        return $this->token->serverProviders->unique('type');
    }

    public function canSelect(): bool
    {
        if ($this->selectedProviderSelectedKey !== null && $this->getHasMultipleKeysOnProviderProperty()) {
            return true;
        }

        if (! $this->getHasMultipleKeysOnProviderProperty()) {
            return true;
        }

        return false;
    }

    public function store(): void
    {
        $this->authorize('create', [Server::class, $this->token]);

        $this->validate([
            'serverName'                  => ['required', 'min:3', 'max:50'],
            'preset'                      => ['required', Rule::in(['genesis', 'seed', 'relay', 'forger', 'explorer'])],
            'selectedProvider'            => 'required',
            'region'                      => 'required',
            'plan'                        => 'required',
            'delegatePassphrase'          => ['nullable', 'required_with:delegatePassword', new BIP39Passphrase()],
            'delegatePassword'            => ['nullable', 'alpha_num', 'min:8'],
        ]);

        /** @var ServerProviderImage $serverProviderImage */
        $serverProviderImage = $this->selectedProvider->images()->where('uuid', $this->selectedProvider->client()->getImageId())->first();

        $server = $this->network->servers()->create([
            'server_provider_id'        => $this->selectedProvider->id,
            'name'                      => Format::withToken($this->serverName),
            'server_provider_region_id' => $this->selectedProvider->regions()->findOrFail($this->region)->id,
            'server_provider_plan_id'   => $this->selectedProvider->plans()->findOrFail($this->plan)->id,
            'server_provider_image_id'  => $serverProviderImage->id,
            'preset'                    => $this->preset,
            'delegate_passphrase'       => $this->delegatePassphrase,
            'delegate_password'         => $this->delegatePassword,
        ]);

        /** @var User $user */
        $user = $this->user;

        $server->setMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN, false);
        $server->setMetaAttribute(ServerAttributeEnum::SERVER_CREATED_MODAL_SEEN, false);
        $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

        ServerCreated::dispatch($server);

        $this->redirect($server->pathShow());
    }

    public function cancel(): void
    {
        $this->redirectRoute('tokens');
    }
}
