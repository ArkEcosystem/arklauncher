<?php

declare(strict_types=1);

namespace App\Collaborator\Components;

use Domain\Collaborator\Models\Invitation;
use Domain\User\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\HasFlashMessages;
use Support\Components\Concerns\InteractsWithToken;

final class MailedInvitations extends Component
{
    use HasDefaultRender;
    use HasFlashMessages;
    use InteractsWithToken;

    /** @var mixed */
    protected $listeners = ['refreshCollaborators' => '$refresh'];

    public function cancel(string $invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);

        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->ownsToken($invitation->token), 403);

        $invitation->delete();

        $this->emit('refreshCollaborators');

        $this->toast(trans('tokens.invitations.mailed_invitation_removed_success'), 'success');
    }

    public function getInvitationsProperty(): Collection
    {
        return $this->token->invitations;
    }
}
