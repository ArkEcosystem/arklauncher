<?php

declare(strict_types=1);

namespace App\Server\Components;

use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class ManageServerConfiguration extends Component
{
    public ?int $plan = null;

    public ?int $region = null;

    public ?string $serverName = '';

    public ?ServerProvider $selectedProvider = null;

    public ?string $selectedProviderType = null;

    public ?int $selectedProviderSelectedKey = null;

    public Token $token;

    /** @var mixed */
    protected $listeners = [
        'setProvider' => 'selectProvider',
    ];

    public function mount(Token $token): void
    {
        $this->token                = $token;
        $this->selectedProvider     = $this->selectedProvider ?? $this->getUniqueProviders()->first();

        if ($this->token->hasServerProviders() && ! $token->needsServerConfiguration()) {
            $serverConfig = $token->getMetaAttribute(ServerAttributeEnum::SERVER_CONFIG);

            $this->selectedProvider            = ServerProvider::where('id', $serverConfig['server_provider_id'])->firstOrFail();
            $this->selectedProviderType        = $this->selectedProvider->type;
            $this->selectedProviderSelectedKey = $this->selectedProvider->id;
            $this->serverName                  = $serverConfig['name'];
            $this->region                      = $serverConfig['server_provider_region_id'];
            $this->plan                        = $serverConfig['server_provider_plan_id'];
        }
    }

    public function render(): View
    {
        if ($this->selectedProviderSelectedKey !== null) {
            $this->selectedProvider     = $this->token->serverProviders()->findOrFail($this->selectedProviderSelectedKey);
        }

        if ($this->selectedProvider !== null) {
            $this->selectedProviderType = $this->selectedProvider->type;
        }

        return view('livewire.manage-server-configuration', [
            'selectProvider'                 => $this->selectedProvider,
            'selectedProviderSelectedKey'    => $this->selectedProviderSelectedKey,
        ]);
    }

    public function updatedSelectedProviderSelectedKey(): void
    {
        $this->plan   = null;
        $this->region = null;
    }

    public function getHasMultipleKeysOnProviderProperty(): bool
    {
        /** @var ServerProvider $selectedProvider */
        $selectedProvider = $this->selectedProvider;

        return $this->token->serverProviders()->where('type', '=', $selectedProvider->type)->count() > 1;
    }

    public function getProviderEntriesProperty(): Collection
    {
        /** @var ServerProvider $selectedProvider */
        $selectedProvider = $this->selectedProvider;

        return $this->token->serverProviders()->where('type', '=', $selectedProvider->type)->get();
    }

    public function getRegionsProperty(): Collection
    {
        /** @var ServerProvider $selectedProvider */
        $selectedProvider = $this->selectedProvider;

        return $selectedProvider->regions;
    }

    public function getPlansProperty(): Collection
    {
        /** @var ServerProvider $selectedProvider */
        $selectedProvider = $this->selectedProvider;

        return $selectedProvider->plans;
    }

    public function getFormattedPlansProperty(): Collection
    {
        /** @var ServerProvider $selectedProvider */
        $selectedProvider = $this->selectedProvider;

        $region = $selectedProvider->regions()->findOrFail($this->region);

        // Search by string...
        $resultsByString = $selectedProvider->plans()
            ->where('memory', '>=', config('deployer.deployment.minimumServerRam'))
            ->whereJsonContains('regions', [$region->uuid])
            ->get();

        if ($resultsByString->count() > 0) {
            $plans = $resultsByString;

            return $plans;
        }

        // Search by integer...
        $resultsByNumber = $selectedProvider->plans()
            ->where('memory', '>=', config('deployer.deployment.minimumServerRam'))
            ->whereJsonContains('regions', [(int) $region->uuid])
            ->get();

        if ($resultsByNumber->count() > 0) {
            $plans = $resultsByNumber;

            return $plans;
        }

        // Server Provider without regions...
        $plans = $selectedProvider->plans()
            ->where('memory', '>=', config('deployer.deployment.minimumServerRam'))
            ->get();

        return $plans;
    }

    public function selectProvider(int $providerId): void
    {
        $this->selectedProviderSelectedKey = null;

        $this->selectedProvider = $this->token->serverProviders()->findOrFail($providerId);

        $this->plan    = null;
        $this->region  = null;
    }

    public function getUniqueProviders(): Collection
    {
        return $this->token->serverProviders->unique('type');
    }

    public function canSelect(): bool
    {
        if ($this->getHasMultipleKeysOnProviderProperty() && $this->selectedProviderSelectedKey !== null) {
            return true;
        }

        if (! $this->getHasMultipleKeysOnProviderProperty() && $this->selectedProvider !== null) {
            return true;
        }

        return false;
    }

    public function canSubmit(): bool
    {
        return $this->serverName !== null &&
            $this->serverName !== '' &&
            $this->selectedProvider !== null &&
            $this->region !== null &&
            $this->plan !== null;
    }

    public function store(): void
    {
        $this->validate([
            'serverName'                  => ['required', 'min:3', 'max:50'],
            'selectedProvider'            => 'required',
            'selectedProviderSelectedKey' => Rule::requiredIf($this->getHasMultipleKeysOnProviderProperty()),
            'region'                      => 'required',
            'plan'                        => 'required',
        ]);

        /** @var ServerProvider $selectedProvider */
        $selectedProvider = $this->selectedProvider;

        /** @var string $serverName */
        $serverName = $this->serverName;

        $this->token->setMetaAttribute(ServerAttributeEnum::SERVER_CONFIG, [
            'server_provider_id'        => $selectedProvider->id,
            'name'                      => $serverName,
            'server_provider_region_id' => $selectedProvider->regions()->findOrFail($this->region)->id,
            'server_provider_plan_id'   => $selectedProvider->plans()->findOrFail($this->plan)->id,
        ]);

        $this->redirectRoute('tokens.show', ['token' => $this->token]);
    }

    public function cancel(): void
    {
        $this->redirectRoute('tokens.show', ['token' => $this->token]);
    }
}
