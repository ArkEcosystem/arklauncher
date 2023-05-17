<?php

declare(strict_types=1);

namespace App\Collaborator\Components;

use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\User\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\HasFlashMessages;
use Support\Components\Concerns\InteractsWithToken;
use Support\Components\Concerns\InteractsWithUser;

final class DeleteCollaborator extends Component
{
    use HasDefaultRender;
    use HasFlashMessages;
    use AuthorizesRequests;
    use InteractsWithUser;
    use InteractsWithToken;
    use HasModal;

    public ?int $collaboratorId = null;

    public ?string $name = null;

    /** @var mixed */
    protected $listeners = ['deleteCollaborator' => 'askForConfirmation'];

    public function askForConfirmation(int $id): void
    {
        $this->collaboratorId = $id;
        $this->name           = User::findOrFail($id)->name;
    }

    public function destroy(): void
    {
        $this->authorize('deleteCollaborator', $this->token);

        $collaborator = User::findOrFail($this->collaboratorId);

        abort_unless($this->canBeRemoved($collaborator), 403);

        $this->token->stopSharingWith($collaborator);

        $this->reset();

        $this->emit('refreshCollaborators');

        $this->toast(trans('tokens.collaborators.removed'), 'success');

        $this->modalClosed();
    }

    public function close(): void
    {
        $this->collaboratorId = null;
        $this->name           = null;

        $this->modalClosed();
    }

    private function canBeRemoved(User $collaborator): bool
    {
        return $this->user->id !== $collaborator->id && $collaborator->id !== $this->token->user->id;
    }
}
