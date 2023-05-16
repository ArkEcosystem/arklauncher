<?php

declare(strict_types=1);

namespace App\Collaborator\Mail;

use Domain\Collaborator\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Support\Builders\NotificationBuilder;

final class InviteExistingUser extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Invitation $invitation)
    {
    }

    public function via(): array
    {
        return ['database', 'mail'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromInvitation($this->invitation, [
                'content' => trans('notifications.subjects.new_invitation'),
            ])
            ->withAction(trans('actions.view'), route('user.teams'))
            ->success()
            ->getContent();
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject($subject = trans('mails.subjects.new_invitation'))
            ->view('mails.invite-existing-user', [
                'subject'   => $subject,
                'userName'  => optional($this->invitation->user)->name ?? null,
                'tokenName' => $this->invitation->token->name,
                'url'       => route('register', ['invitation' => $this->invitation->uuid]),
            ]);
    }
}
