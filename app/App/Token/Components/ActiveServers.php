<?php

declare(strict_types=1);

namespace App\Token\Components;

use App\Enums\NetworkTypeEnum;
use App\Enums\ServerProviderTypeEnum;
use App\Server\Jobs\WaitForServerToStart;
use ARKEcosystem\Foundation\UserInterface\Components\Concerns\HandleToast;
use ARKEcosystem\Foundation\UserInterface\Support\Enums\FlashType;
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;
use Support\Components\Concerns\InteractsWithUser;
use Support\Components\Concerns\Sortable;

final class ActiveServers extends Component
{
    use AuthorizesRequests;
    use InteractsWithUser;
    use Sortable;
    use HandleToast;

    public Token $selectedToken;

    public Network $selectedNetwork;

    public Collection $selectedNetworkServers;

    public Collection $filteredNetworkServers;

    public ?string $network = null;

    public ?string $token = null;

    public ?string $title = null;

    public string $serverType = 'all';

    /** @var mixed */
    protected $listeners = [
        'setToken'       => 'setToken',
        'selectNetwork'  => 'selectNetwork',
        'sortingUpdated' => 'sortingUpdated',
    ];

    /** @var mixed */
    protected $queryString = [
        'network' => ['except' => ''],
    ];

    public function getActiveNetwork(): ?Network
    {
        $validNetworks = [
            NetworkTypeEnum::MAINNET,
            NetworkTypeEnum::DEVNET,
            NetworkTypeEnum::TESTNET,
        ];

        if ($this->network !== null && in_array($this->network, $validNetworks, true)) {
            return $this->selectedToken->network($this->network);
        }

        return $this->selectedToken->network(NetworkTypeEnum::MAINNET);
    }

    public function mount(): void
    {
        $this->setNetwork($this->getActiveNetwork());
    }

    public function render(): View
    {
        return view('livewire.active-servers', [
            'selectedToken'          => $this->selectedToken,
            'selectedNetwork'        => $this->selectedNetwork,
            'selectedNetworkServers' => $this->selectedNetworkServers,
            'filteredNetworkServers' => $this->filteredNetworkServers,
        ]);
    }

    public function setToken(int $id): void
    {
        $this->selectedToken = Token::findOrFail($id);

        $this->setNetwork($this->getActiveNetwork());
    }

    public function selectNetwork(int $id): void
    {
        $network = $this->selectedToken->networks()->findOrFail($id);

        $this->setNetwork($network);

        $this->network = $this->selectedNetwork->name;

        $this->emit('setNetwork', $this->selectedNetwork->id);
    }

    public function sortingUpdated(): void
    {
        if ($this->sortDirection === 'asc') {
            /* @phpstan-ignore-next-line  */
            $this->selectedNetworkServers = $this->selectedNetworkServers->sortBy($this->sortBy);
        } elseif ($this->sortDirection === 'desc') {
            $this->selectedNetworkServers = $this->selectedNetworkServers
                ->sortByDesc($this->sortBy ?? '');
        } else {
            $this->selectedNetworkServers = $this->selectedNetworkServers
                ->sortByDesc('created_at');
        }
    }

    public function startServer(int $id): void
    {
        $server = $this->selectedNetwork->servers()->findOrFail($id);

        $this->authorize('start', $server);

        try {
            $server->serverProvider->client()->start($server->provider_server_id);

            $server->markAsOnline();

            $this->toast(trans('notifications.server_started', ['server' => $server->name]));
        } catch (Exception $exception) {
            $this->handleException($exception, $server->name, $server->serverProvider);
        }
    }

    public function stopServer(int $id): void
    {
        $server = $this->selectedNetwork->servers()->findOrFail($id);

        $this->authorize('stop', $server);

        try {
            $server->serverProvider->client()->stop($server->provider_server_id);

            $server->markAsOffline();

            $this->toast(trans('notifications.server_stopped', ['server' => $server->name]));
        } catch (Exception $exception) {
            $this->handleException($exception, $server->name, $server->serverProvider);
        }
    }

    public function rebootServer(int $id): void
    {
        $server = $this->selectedNetwork->servers()->findOrFail($id);

        $this->authorize('restart', $server);

        try {
            $server->serverProvider->client()->reboot($server->provider_server_id);

            WaitForServerToStart::dispatch($server)->delay(
                now()->addSeconds(30)
            );

            $server->markAsOffline();

            $this->toast(trans('notifications.server_rebooted', ['server' => $server->name]));
        } catch (Exception $exception) {
            $this->handleException($exception, $server->name, $server->serverProvider);
        }
    }

    public function updatedServerType(): void
    {
        $this->filteredNetworkServers = $this->selectedNetworkServers->where('preset', $this->serverType);
    }

    private function setNetwork(?Network $network): void
    {
        if ($network !== null) {
            $this->selectedNetwork        = $network;
            $this->selectedNetworkServers = $network->servers()->latest()->get();
            $this->serverType             = 'all';
            $this->filteredNetworkServers = $this->selectedNetworkServers;
        }
    }

    private function handleException(Exception $exception, string $name, ServerProvider $provider): void
    {
        if ($exception instanceof ServerNotFound) {
            alert('notifications.server_not_found', FlashType::ERROR, ['server' => $name]);
        } elseif ($exception instanceof ServerProviderError && $exception->getMessage() !== '') {
            alert($exception->getMessage(), FlashType::ERROR);
        } elseif ($exception instanceof ServerProviderAuthenticationException) {
            alert(trans('notifications.server_provider_authentication_error', [
                'provider' => ServerProviderTypeEnum::label($provider->type),
                'name'     => $provider->name,
            ]), FlashType::ERROR);
        } else {
            report($exception);

            alert('notifications.something_went_wrong', FlashType::ERROR);
        }
    }
}
