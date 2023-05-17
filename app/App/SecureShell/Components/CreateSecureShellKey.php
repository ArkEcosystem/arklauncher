<?php

declare(strict_types=1);

namespace App\SecureShell\Components;

use Domain\SecureShell\Rules\SecureShellKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\HasFlashMessages;
use Support\Components\Concerns\InteractsWithUser;

final class CreateSecureShellKey extends Component
{
    use HasDefaultRender;
    use HasFlashMessages;
    use InteractsWithUser;

    public ?string $name = null;

    public ?string $public_key = null;

    public function store(): void
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

        $this->emit('refreshSecureShellKeys');
    }
}
