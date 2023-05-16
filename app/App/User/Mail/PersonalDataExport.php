<?php

declare(strict_types=1);

namespace App\User\Mail;

use Illuminate\Notifications\Messages\MailMessage;
use Spatie\PersonalDataExport\Notifications\PersonalDataExportedNotification as SpatiePersonalDataExportedNotification;

final class PersonalDataExport extends SpatiePersonalDataExportedNotification
{
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): mixed
    {
        $downloadUrl = route('personal-data-exports', $this->zipFilename);

        // @codeCoverageIgnoreStart
        if (static::$toMailCallback !== null) {
            return call_user_func(static::$toMailCallback, $notifiable, $downloadUrl);
        }
        // @codeCoverageIgnoreEnd

        return (new MailMessage())
            ->subject(trans('mails.subjects.download_personal_data'))
            ->view('mails.personal-data-export', [
                'subject' => trans('mails.subjects.download_personal_data'),
                'url'     => $downloadUrl,
                'date'    => $this->deletionDatetime->format('Y-m-d H:i:s'),
            ]);
    }
}
