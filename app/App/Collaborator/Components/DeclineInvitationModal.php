<?php

declare(strict_types=1);

namespace App\Collaborator\Components;

use App\Collaborator\Notifications\CollaboratorDeclinedInvite;
use App\Token\Controllers\TokenInvitationController;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use ARKEcosystem\Foundation\UserInterface\Support\Enums\FlashType;
use Domain\Collaborator\Models\Invitation;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;
use Support\Components\Concerns\HasDefaultRender;

final class DeclineInvitationModal extends Component
{
    use HasDefaultRender;
    use HasModal;

    public ?int $invitationId = null;

    public ?Invitation $invitation = null;

    /** @var mixed */
    protected $listeners = ['showDeclineInvitationModal' => 'showModal'];

    public function showModal(int $id): void
    {
        $this->invitationId = $id;
        $this->invitation   = Invitation::find($id);
    }

    public function close(): void
    {
        $this->invitationId = null;

        $this->modalClosed();
    }

    public function decline(): Redirector|RedirectResponse
    {
        $request = request();
        if (! TokenInvitationController::canBeHandled($request, $this->invitation)) {
            return redirect()->route('user.teams');
        }

        /** @var Token $token */
        $token = $this->invitation?->token;

        $this->invitation?->delete();

        alert('invitations.messages.decline_invitation', FlashType::SUCCESS);

        /** @var User $user */
        $user = $request->user();

        $token->user->notify(new CollaboratorDeclinedInvite($token, $user));

        return redirect()->route('user.teams')->with('status', TokenInvitationController::ALERT_STATUS);
    }
}
