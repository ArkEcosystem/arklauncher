<?php

declare(strict_types=1);

namespace App\SecureShell\Components;

use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\HasFlashMessages;
use Support\Components\Concerns\InteractsWithUser;

final class DeleteSecureShellKey extends Component
{
    use HasDefaultRender;
    use HasFlashMessages;
    use InteractsWithUser;
    use HasModal;

    public ?int $keyId = null;

    /** @var mixed */
    protected $listeners = ['deleteSecureShellKey' => 'askForConfirmation'];

    public function askForConfirmation(int $id): void
    {
        $this->keyId = $id;
    }

    public function destroy(): void
    {
        $secureShellKey = $this->user->secureShellKeys()->findOrFail($this->keyId);

        $secureShellKey->delete();

        $this->toast(trans('pages.user-settings.delete_ssh_success'), 'success');

        $this->modalClosed();

        $this->reset();

        $this->emit('refreshSecureShellKeys');
    }

    public function cancel(): void
    {
        $this->keyId = null;

        $this->modalClosed();
    }
}
