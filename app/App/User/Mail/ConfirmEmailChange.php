<?php

declare(strict_types=1);

namespace App\User\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

final class ConfirmEmailChange extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $email,
        public string $name
    ) {
    }

    public function build() : self
    {
        return $this->subject($subject = trans('mails.subjects.email-confirm-change'))->view('mails.confirm-email-change', [
            'subject' => $subject,
            'name'    => $this->name,
            'url'     => $this->confirmationUrl(),
        ]);
    }

    private function confirmationUrl() : string
    {
        $ttl = now()->addHours(24);

        return URL::temporarySignedRoute('user.profile', $ttl, [
            'email' => $this->email,
        ]);
    }
}
