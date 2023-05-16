<?php

declare(strict_types=1);

namespace App\Server\Notifications;

use Domain\Server\Models\Server;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class ServerFailedDeployment extends Notification
{
    use Queueable;

    public Token $token;

    public function __construct(public Server $server)
    {
        $this->token = $this->server->token;
    }

    public function via(): array
    {
        return ['database', 'mail'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromServer($this->server, [
                'content' => trans('notifications.subjects.server_failed_deployment', ['server' => $this->server->name]),
            ])
            ->withAction(trans('actions.view'), route('tokens.servers.show', [$this->token, $this->server->network, $this->server]))
            ->danger()
            ->getContent();
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject($subject = strip_tags(trans('notifications.subjects.server_failed_deployment', ['server' => $this->server->name])))
            ->view('mails.server-failed-deployment', [
                'subject' => $subject,
                'server'  => $this->server,
            ]);
    }

    public function shouldSend(User $notifiable) : bool
    {
        return $this->server->token->hasCollaborator($notifiable);
    }
}
