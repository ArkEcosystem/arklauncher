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

final class ServerProvisioned extends Notification
{
    use Queueable;

    public Token $token;

    public function __construct(public Server $server)
    {
        $this->token = $server->token;
    }

    public function via(): array
    {
        return ['database', 'mail'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromServer($this->server, [
                'content' => trans('notifications.subjects.server_provisioned', ['server' => $this->server->name]),
            ])
            ->withAction(trans('actions.view'), $this->server->pathShow())
            ->success()
            ->getContent();
    }

    public function toMail(User $notifiable): MailMessage
    {
        $userCanCreateServers = $notifiable->can('create', [
            Server::class, $this->token,
        ]);

        return (new MailMessage())
            ->subject($subject = strip_tags(trans('notifications.subjects.server_provisioned', ['server' => $this->server->name])))
            ->view('mails.server-provisioned', [
                'subject'              => $subject,
                'token'                => $this->token,
                'server'               => $this->server,
                'username'             => $this->token->normalized_token,
                'userPassword'         => $userCanCreateServers ? $this->server->user_password : null,
                'sudoPassword'         => $userCanCreateServers ? $this->server->sudo_password : null,
                'userCanCreateServers' => $userCanCreateServers,
            ]);
    }
}
