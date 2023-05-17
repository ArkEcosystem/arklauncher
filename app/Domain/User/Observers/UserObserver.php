<?php

declare(strict_types=1);

namespace Domain\User\Observers;

use Domain\User\Models\User;

final class UserObserver
{
    public function deleting(User $user): void
    {
        $this->forceDeleteUserOwnedTokens($user);
        $this->deleteUserSecureShellKeys($user);
        $this->deleteUserInvitations($user);
    }

    private function forceDeleteUserOwnedTokens(User $user): void
    {
        $user->ownedTokens()->withTrashed()->get()->each->forceDelete();
    }

    private function deleteUserSecureShellKeys(User $user): void
    {
        $user->secureShellKeys()->get()->each->delete();
    }

    private function deleteUserInvitations(User $user): void
    {
        $user->invitations()->get()->each->delete();
    }
}
