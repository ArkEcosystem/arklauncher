<?php

declare(strict_types=1);

namespace App\Server\Components;

use App\Enums\ServerProviderTypeEnum;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Closure;
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Models\Server;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Throwable;

final class RenameServer extends Component
{
    use HasDefaultRender;
    use AuthorizesRequests;
    use HasModal;

    public ?int $serverId = null;

    public ?string $name = null;

    public Network $network;

    public Token $token;

    public string $nameValidator = '';

    protected ?Server $server = null;

    /** @var mixed */
    protected $listeners = [
        'renameServer' => 'renameServer',
    ];

    public function mount(Token $token, Network $network): void
    {
        $this->token   = $token;
        $this->network = $network;
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName, [
            'name' => [
                'required',
                $this->getNameValidator(),
            ],
        ]);
    }

    public function renameServer(int $id): void
    {
        $this->serverId      = $id;

        /** @var Server $server */
        $server = $this->network->servers()->findOrFail($this->serverId);

        $this->server        = $server;
        $this->name          = $this->server->name;

        $this->nameValidator = $this->server->serverProvider->client()::nameValidator();
    }

    public function rename(): void
    {
        $this->validate([
            'name' => [
                'required',
                $this->getNameValidator(),
            ],
        ]);

        $server = $this->network->servers()->findOrFail($this->serverId);

        $this->authorize('rename', $server);

        try {
            $server->update(['name' => $this->name]);
        } catch (Throwable $e) {
            $this->handleException($e, $server, fn ($message) => throw ValidationException::withMessages([
                'name' => [$message],
            ]));
        }

        $this->serverId      = null;
        $this->server        = null;
        $this->name          = null;
        $this->nameValidator = '';

        $this->closeModal();

        $this->redirect(route('tokens', request()->query()));
    }

    public function cancel(): void
    {
        $this->serverId      = null;
        $this->server        = null;
        $this->name          = null;
        $this->nameValidator = '';
        $this->resetErrorBag();

        $this->closeModal();
    }

    private function getNameValidator(): Rule
    {
        return App::make($this->nameValidator);
    }

    private function handleException(Throwable $exception, Server $server, Closure $fail) : void
    {
        if ($exception instanceof ServerNotFound) {
            $fail(trans('notifications.server_not_found', ['server' => $server->getOriginal('name')]));
        } elseif ($exception instanceof ServerProviderAuthenticationException) {
            $fail(trans('notifications.server_provider_authentication_error', [
                'provider' => ServerProviderTypeEnum::label($server->serverProvider->type),
                'name'     => $server->serverProvider->name,
            ]));
        } else {
            report($exception);

            $fail(trans('notifications.something_went_wrong'));
        }
    }
}
