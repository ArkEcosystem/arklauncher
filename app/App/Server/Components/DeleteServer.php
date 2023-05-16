<?php

declare(strict_types=1);

namespace App\Server\Components;

use App\Enums\NetworkTypeEnum;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;

final class DeleteServer extends Component
{
    use HasDefaultRender;
    use AuthorizesRequests;
    use HasModal;

    public ?int $serverId = null;

    public Network $network;

    public Token $token;

    /** @var mixed */
    protected $listeners = [
        'deleteServer' => 'askForConfirmation',
        'setNetwork'   => 'setNetwork',
        'setToken'     => 'setToken',
    ];

    public function mount(Token $token, Network $network): void
    {
        $this->token   = $token;
        $this->network = $network;
    }

    public function askForConfirmation(int $id): void
    {
        $this->serverId = $id;
    }

    public function setToken(int $id): void
    {
        $this->token   = Token::findOrFail($id);
        $this->network = $this->token->networks()->where('name', NetworkTypeEnum::MAINNET)->firstOrFail();
    }

    public function setNetwork(int $id): void
    {
        $this->network = $this->token->networks()->where('id', $id)->firstOrFail();
    }

    public function destroy(): void
    {
        $server = $this->network->servers()->findOrFail($this->serverId);

        $this->authorize('delete', $server);

        $server->delete();

        $this->serverId = null;

        $this->modalClosed();

        $this->redirect(url()->previous());
    }

    public function cancel(): void
    {
        $this->serverId = null;

        $this->modalClosed();
    }
}
