<?php

declare(strict_types=1);

namespace App\Token\Components;

use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\Token\Events\TokenDeleted;
use Domain\Token\Models\Token;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;

final class DeleteToken extends Component
{
    use HasDefaultRender;
    use AuthorizesRequests;
    use HasModal;

    public ?int $tokenId = null;

    public Token $token;

    public array $options;

    public array $selectedOptions;

    public ?string $token_name = null;

    /** @var mixed */
    protected $listeners = [
        'deleteToken' => 'askForConfirmation',
        'setOption'   => 'selectOption',
    ];

    public function mount(): void
    {
        $this->options = [
            'blockchain' => trans('tokens.delete_token_modal.option_blockchain'),
            'servers'    => trans('tokens.delete_token_modal.option_servers'),
        ];

        $this->selectedOptions = ['blockchain'];
    }

    public function getSelectedOptionsProperty(): array
    {
        return $this->selectedOptions;
    }

    public function selectOption(string $option): void
    {
        if (! in_array($option, $this->selectedOptions, true)) {
            array_push($this->selectedOptions, $option);
        } else {
            $this->selectedOptions = array_diff($this->selectedOptions, [$option]);
        }
    }

    public function askForConfirmation(int $id): void
    {
        $this->openModal();

        $this->tokenId = $id;
        $this->token   = Token::findOrFail($id);
    }

    public function destroy(): void
    {
        $this->authorize('delete', $this->token);

        foreach ($this->selectedOptions as $action) {
            if ($action === 'servers') {
                $this->destroyWithServers();
            }
        }

        $this->destroyToken();

        $this->modalClosed();

        $this->redirectRoute('tokens');
    }

    public function cancel(): void
    {
        $this->tokenId = null;

        $this->modalClosed();
    }

    public function shouldBeDisabled(string $key): bool
    {
        if ($key === 'blockchain') {
            return false;
        }

        if ($key === 'servers') {
            return $this->token->servers->count() === 0;
        }

        return false;
    }

    public function hasConfirmedName(): bool
    {
        return $this->token_name === $this->token->name;
    }

    private function destroyToken(): void
    {
        $this->token->delete();

        TokenDeleted::dispatch($this->token, $shouldDeleteServers = false);
    }

    private function destroyWithServers(): void
    {
        foreach ($this->token->servers as $server) {
            $server->delete();
        }
    }
}
