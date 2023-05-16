<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use App\Collaborator\Notifications\CollaboratorAcceptedInvite;
use ARKEcosystem\Foundation\UserInterface\Support\Enums\FlashType;
use Domain\Collaborator\Models\Invitation;
use Domain\User\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Support\Components\Concerns\InteractsWithUser;
use Support\Http\Controllers\Controller;

final class TokenInvitationController extends Controller
{
    use InteractsWithUser;

    public const ALERT_STATUS = 'invitation';

    public function update(Request $request, int $invitation): RedirectResponse
    {
        /** @var Invitation $invitation */
        $invitation = Invitation::find($invitation);

        if (! static::canBeHandled($request, $invitation)) {
            return redirect()->back()->with('status', self::ALERT_STATUS);
        }

        $token = $invitation->token;

        /** @var User $user */
        $user = $request->user();

        $token->shareWith($user, $invitation->role, $invitation->permissions);

        $invitation->delete();

        alert('invitations.messages.accepted', FlashType::SUCCESS);

        $token->collaborators->each->notify(new CollaboratorAcceptedInvite($token, $user));

        return redirect()->route('user.teams')->with('status', self::ALERT_STATUS);
    }

    public static function canBeHandled(Request $request, ?Invitation $invitation): bool
    {
        if (is_null($invitation)) {
            alert('invitations.messages.invitation_removed', FlashType::ERROR);

            return false;
        }

        if ($invitation->isExpired()) {
            alert('invitations.messages.invitation_expired', FlashType::ERROR);

            return false;
        }

        if (is_null($invitation->user_id)) {
            /** @var User $user */
            $user = $request->user();
            abort_unless($user->email === $invitation->email, 404);
        } else {
            abort_unless(Auth::id() === $invitation->user_id, 404);
        }

        return true;
    }
}
