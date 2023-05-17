<?php

declare(strict_types=1);

namespace App\Token\Components;

use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\SecureShell\Rules\SecureShellKey;
use Domain\Token\Models\Token;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\HasFlashMessages;
use Support\Components\Concerns\InteractsWithUser;

final class ManageTokenSecureShellKeys extends Component
{
    use HasDefaultRender;
    use HasFlashMessages;
    use InteractsWithUser;
    use HasModal;
    use AuthorizesRequests;

    public Token $token;

    public ?string $name = null;

    public ?string $public_key = null;

    public array $selectedOptions = [];

    /** @var mixed */
    protected $listeners = [
        'setOption'              => 'selectOption',
        'handleSelect'           => 'handleSelect',
        'refreshSecureShellKeys' => '$refresh',
    ];

    public function mount(Token $token): void
    {
        $this->token           = $token;
        $this->selectedOptions = array_merge($this->selectedOptions, $this->getTokenKeys());
    }

    public function getKeysProperty(): Collection
    {
        return $this->token->availableKeys();
    }

    public function getCanSubmitProperty(): bool
    {
        return (count(array_diff($this->selectedOptions, $this->getTokenKeys())) > 0
            || count(array_diff($this->getTokenKeys(), $this->selectedOptions)) > 0)
            && count($this->selectedOptions) > 0;
    }

    public function getTokenKeys(): array
    {
        return $this->token->secureShellKeys->pluck('id')->toArray();
    }

    public function isRegistered(int $option): bool
    {
        return in_array($option, $this->selectedOptions, true);
    }

    public function selectOption(int $option): void
    {
        $this->authorize('manageKeys', $this->token);

        if (! in_array($option, array_values($this->selectedOptions), true)) {
            $this->selectedOptions[] = $option;
        } else {
            $this->selectedOptions = array_diff($this->selectedOptions, [$option]);
        }
    }

    public function selectAll(): void
    {
        $this->authorize('manageKeys', $this->token);

        $this->selectedOptions = Arr::flatten($this->getKeysProperty()->pluck('id'));
    }

    public function deselectAll(): void
    {
        $this->authorize('manageKeys', $this->token);

        $this->selectedOptions = [];
    }

    public function store(): void
    {
        $this->authorize('manageKeys', $this->token);

        $this->token->secureShellKeys()->sync($this->selectedOptions);

        $this->toast(trans('tokens.secure-shell-keys.store_success'), 'success');

        redirect()->to(route('tokens.show', $this->token));
    }

    public function storeKey(): void
    {
        $data = $this->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('secure_shell_keys', 'name')->where('user_id', Auth::id()),
            ],
            'public_key' => [
                'required',
                new SecureShellKey(),
                Rule::unique('secure_shell_keys', 'public_key')->where('user_id', Auth::id()),
            ],
        ]);

        $this->user->secureShellKeys()->create($data);

        $this->toast(trans('pages.user-settings.create_ssh_success'), 'success');

        $this->name       = null;
        $this->public_key = null;

        if ($this->modalShown) {
            $this->closeModal();
        }

        $this->emit('refreshSecureShellKeys');
    }

    public function toggleModal(): void
    {
        if ($this->modalShown) {
            $this->closeModal();
        } else {
            $this->openModal();
        }
    }
}
