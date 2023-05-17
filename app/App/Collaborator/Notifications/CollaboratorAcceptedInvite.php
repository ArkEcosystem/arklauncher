<?php

declare(strict_types=1);

namespace App\Collaborator\Notifications;

use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class CollaboratorAcceptedInvite extends Notification
{
    use Queueable;

    public function __construct(public Token $token, public User $user)
    {
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromToken($this->token, [
                'content' => trans('notifications.subjects.collaborator_accepted_invitation', ['collaborator' => $this->user->name]),
            ])
            ->withAction(trans('actions.view'), route('user.teams'))
            ->withUser($this->user)
            ->success()
            ->getContent();
    }
}
