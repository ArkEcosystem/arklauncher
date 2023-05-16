<?php

declare(strict_types=1);

use App\Server\Notifications\ServerProvisioned;
use Domain\Server\Models\Server;
use Domain\User\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $server = Server::factory()->createForTest();

    $server->token->user->notify(new ServerProvisioned($server));

    Notification::assertSentTo($server->token->user, ServerProvisioned::class);
});

it('builds the notification as mail', function () {
    $server = Server::factory()->createForTest();

    $notification = new ServerProvisioned($server);

    // We want to render the HTML of the email to make assertions against it and because of the fact that `MailFake` does not have the `render` method,
    // and because Mail::clearResolvedInstances() does not reset the MailManager, we need to manually re-bind the manager to have the original instance...
    $this->app->singleton('mail.manager', function ($app) {
        return new \Illuminate\Mail\MailManager($app);
    });

    expect($notification->toMail($server->token->user))->toBeInstanceOf(MailMessage::class);
    expect($notification->toMail($server->token->user)->render())->toContain('User Password');
    expect($notification->toMail($server->token->user)->render())->toContain('Root Password');
});

it('hides passwords from users not having sufficient permission', function () {
    $server = Server::factory()->createForTest();

    $other = User::factory()->create();

    $server->token->shareWith($other, 'collaborator', ['server-provider:create']);

    // We want to render the HTML of the email to make assertions against it and because of the fact that `MailFake` does not have the `render` method,
    // and because Mail::clearResolvedInstances() does not reset the MailManager, we need to manually re-bind the manager to have the original instance...
    $this->app->singleton('mail.manager', function ($app) {
        return new \Illuminate\Mail\MailManager($app);
    });

    $notification = new ServerProvisioned($server);

    expect($notification->toMail($other)->render())->not->toContain('User Password');
    expect($notification->toMail($other)->render())->not->toContain('Root Password');
});

it('builds the notification as an array', function () {
    $server = Server::factory()->createForTest();

    $notification = new ServerProvisioned($server);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerProvisioned($server))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('success');
});

it('should contain the right content', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerProvisioned($server))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_provisioned', ['server' => $server->name]));
});

it('should contain an action', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerProvisioned($server))->toArray();

    expect($notification)->toHaveKey('action');
    expect($notification['action'])->toHaveKey('title');
    expect($notification['action'])->toHaveKey('url');

    expect($notification['action']['title'])->toBe(trans('actions.view'));
    expect($notification['action']['url'])->toBe(route('tokens.servers.show', [$server->token, $server->network, $server]));
});
