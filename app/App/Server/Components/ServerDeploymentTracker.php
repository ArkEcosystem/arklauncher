<?php

declare(strict_types=1);

namespace App\Server\Components;

use ARKEcosystem\Foundation\Fortify\Components\Concerns\InteractsWithUser;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Enums\ServerDeploymentStatus;
use Domain\Server\Models\Server;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Spatie\ModelStatus\Status;

final class ServerDeploymentTracker extends Component
{
    use HasModal;
    use InteractsWithUser;

    public Token $token;

    public int $serverId;

    public string $preset = '';

    public array $cachedGroups = [];

    public bool $showDeploymentFailedModal = false;

    public ?Status $currentStatus;

    public Collection $statuses;

    public string $failureReason = '';

    private ?Server $server = null;

    public function mount(Token $token, int $serverId) : void
    {
        $this->token    = $token;
        $this->serverId = $serverId;
        $this->statuses = collect([]);

        // Might be weird to see the method called inline here, but it's called just to
        // set up any additional state that might be set up if the server is missing...
        $this->server();
    }

    public function render() : View
    {
        // Wraps into `optional()` so Blade template can chain calls that will not throw an exception if server is not found...
        $server = optional($this->server());

        return view('livewire.server-deployment-tracker', [
            'server'              => $server,
            'hasFinalState'       => $server->isProvisioned() || $server->isFailed(),
            'userCanManageServer' => $this->userCanManageServer(),
        ]);
    }

    public function getGroupsProperty() : array
    {
        $server = $this->server();

        if ($server === null || count($this->cachedGroups) > 0) {
            return $this->cachedGroups;
        }

        $this->cachedGroups = (new ServerDeploymentStatus())->getGroupStates()[$server->preset];

        return $this->cachedGroups;
    }

    public function getFirstState() : string
    {
        $server = $this->server();

        if ($server === null) {
            return head(head($this->cachedGroups));
        }

        $states = (new ServerDeploymentStatus())->getGroupStates()[$server->preset];

        return head(head($states));
    }

    public function closeDisclaimerModal() : void
    {
        abort_unless($this->userCanManageServer(), 403);

        optional($this->server())->setMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN, true);

        $this->closeModal();
    }

    public function closeServerCreatedModal() : void
    {
        optional($this->server())->setMetaAttribute(ServerAttributeEnum::SERVER_CREATED_MODAL_SEEN, true);

        $this->closeModal();
    }

    public function getCredentials() : string
    {
        abort_unless($this->userCanManageServer(), 403);

        $usernameKey     = trans('pages.server.installation.passwords_modal.username');
        $userPasswordKey = trans('pages.server.installation.passwords_modal.user_password');
        $sudoPasswordKey = trans('pages.server.installation.passwords_modal.sudo_password');

        $server = $this->server();

        if ($server === null) {
            return '';
        }

        return
            "{$usernameKey}: {$server->token->normalized_token}".'\n'.
            "{$userPasswordKey}: {$server->user_password}".'\n'.
            "{$sudoPasswordKey}: {$server->sudo_password}";
    }

    public function hasEverHadStatus(string $status) : bool
    {
        return $this->statuses->where('name', $status)->isNotEmpty();
    }

    private function userCanManageServer() : bool
    {
        /** @var User */
        $user = $this->user;

        $server = $this->server();

        // If server is deleted, we "fake" that user has a permission to create servers just so Livewire doesn't show 403 modal...
        // All credentials are still hidden from the user...
        if ($server === null) {
            return true;
        }

        return $user->can('create', [
            Server::class, $server->token,
        ]);
    }

    private function server() : ?Server
    {
        if ($this->server !== null) {
            return $this->server;
        }

        /** @var Server|null $server */
        $server = $this->token->servers()->find($this->serverId);

        $this->server = $server;

        if ($this->server === null) {
            $this->showDeploymentFailedModal = true;
            $this->failureReason             = trans('pages.server.installation.failed_modal.generic_error');

            return null;
        }

        if ($this->server->isFailed()) {
            $this->showDeploymentFailedModal = true;
            $this->failureReason             = (string) $this->server->latestStatus();
        }

        $this->server->load('statuses');

        $this->preset        = $this->server->preset;
        $this->currentStatus = $this->server->status();
        $this->statuses      = $this->server->statuses;

        return $this->server;
    }
}
