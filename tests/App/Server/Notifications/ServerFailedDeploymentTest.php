<?php

declare(strict_types=1);

use App\Server\Notifications\ServerFailedDeployment;
use Domain\Server\Models\Server;
use Domain\User\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $server = Server::factory()->createForTest();

    $server->token->user->notify(new ServerFailedDeployment($server));

    Notification::assertSentTo($server->token->user, ServerFailedDeployment::class);
});

it('can determine if notification should be sent to a user', function () {
    $server = Server::factory()->createForTest();
    $user   = User::factory()->create();
    $other  = User::factory()->create();

    $server->token->shareWith($user, 'collaborator', []);

    $notification = new ServerFailedDeployment($server);

    expect($notification->shouldSend($user))->toBeTrue();
    expect($notification->shouldSend($other))->toBeFalse();
});

it('builds the notification as an array', function () {
    $server = Server::factory()->createForTest();

    $notification = new ServerFailedDeployment($server);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerFailedDeployment($server))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('danger');
});

it('should contain the right content', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerFailedDeployment($server))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_failed_deployment', ['server' => $server->name]));
});

it('should contain an action', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerFailedDeployment($server))->toArray();

    expect($notification)->toHaveKey('action');
    expect($notification['action'])->toHaveKey('title');
    expect($notification['action'])->toHaveKey('url');

    expect($notification['action']['title'])->toBe(trans('actions.view'));
    expect($notification['action']['url'])->toBe(route('tokens.servers.show', [$server->token, $server->network, $server]));
});

it('builds the notification as mail', function () {
    $server = Server::factory()->createForTest();

    $mail = new ServerFailedDeployment($server);

    expect($mail->toMail($server))->toBeInstanceOf(MailMessage::class);
});

it('builds the mail with the correct subject', function () {
    $server = Server::factory()->createForTest();

    $mail = new ServerFailedDeployment($server);

    expect($mail->toMail($server)->subject)->toBe(strip_tags(trans('notifications.subjects.server_failed_deployment', ['server' => $server->name])));
});

it('should contain a list of checked statuses in the mail', function () {
    $server = Server::factory()->createForTest();

    $server->setStatus('provisioning');
    $server->setStatus('configuring_locale');
    $server->setStatus('installing_system_dependencies');
    $server->setStatus('installing_nodejs');
    $server->setStatus('installing_yarn');

    $mailContent = view('mails.server-failed-deployment', [
        'subject' => '',
        'server'  => $server,
    ])->render();

    expect($mailContent)->toContain(trans('pages.server.installation.states.provisioning').' [x]');
    expect($mailContent)->toContain(trans('pages.server.installation.states.configuring_locale').' [x]');
    expect($mailContent)->toContain(trans('pages.server.installation.states.installing_system_dependencies').' [x]');
    expect($mailContent)->toContain(trans('pages.server.installation.states.installing_nodejs').' [x]');
    expect($mailContent)->toContain(trans('pages.server.installation.states.installing_yarn').' [x]');
    expect($mailContent)->toContain(trans('pages.server.installation.states.provisioned').' [ ]');
});
