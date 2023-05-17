<?php

declare(strict_types=1);

namespace App\Collaborator\Mail;

use Domain\Collaborator\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class InviteNewUser extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Invitation $invitation)
    {
    }

    public function build(): self
    {
        return $this
            ->subject($subject = trans('mails.subjects.new_invitation'))
            ->view('mails.invite-new-user', [
                'subject'   => $subject,
                'tokenName' => $this->invitation->token->name,
                'url'       => route('register', ['invitation' => $this->invitation->uuid]),
            ]);
    }
}
