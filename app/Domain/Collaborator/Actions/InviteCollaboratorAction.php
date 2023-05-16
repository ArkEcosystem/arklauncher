<?php

declare(strict_types=1);

namespace Domain\Collaborator\Actions;

use App\Collaborator\Mail\InviteExistingUser;
use App\Collaborator\Mail\InviteNewUser;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

final class InviteCollaboratorAction
{
    public function __invoke(Token $token, string $email, array $permissions): void
    {
        if ($this->emailAlreadyOnToken($token, $email)) {
            throw ValidationException::withMessages([
                'email' => trans('tokens.user_already_on_token', ['email' => $email]),
            ]);
        }

        if ($this->emailAlreadyInvited($token, $email)) {
            throw ValidationException::withMessages([
                'email' => trans('tokens.user_already_invited_to_token', ['email' => $email]),
            ]);
        }

        $invitedUser = User::where('email', $email)->first();

        $invitation = $token->invitations()->create([
            'uuid'               => Uuid::uuid4(),
            'user_id'            => $invitedUser?->id,
            'role'               => 'collaborator',
            'permissions'        => $permissions,
            'email'              => $email,
        ]);

        if (is_null($invitation->user_id)) {
            Mail::to($invitation->email)->send(new InviteNewUser($invitation));
        } else {
            /** @var User $invitedUser */
            $invitedUser->notify(new InviteExistingUser($invitation));
        }
    }

    private function emailAlreadyOnToken(Token $token, string $email): bool
    {
        return $token->collaborators()->where('email', $email)->exists();
    }

    private function emailAlreadyInvited(Token $token, string $email): bool
    {
        return $token->invitations()->where('email', $email)->exists();
    }
}
