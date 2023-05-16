<?php

declare(strict_types=1);

use App\Console\Commands\PurgeServersWithFailedDeployments;
use App\Server\Notifications\ServerFailedDeployment;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\Server;
use Domain\Token\Events\ServerDeleted;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('notifies the token owner and the server creator', function () {
    $user = User::factory()->create();

    $server = Server::factory()->createForTest();
    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $server->setStatus('failed');

    $server->token->shareWith($user, 'collaborator', []);

    Notification::fake();

    (new PurgeServersWithFailedDeployments())->handle();

    Notification::assertSentTo([$server->token->user, $server->creator()], ServerFailedDeployment::class);
    Notification::assertTimesSent(2, ServerFailedDeployment::class);
});

it('does not notify the creator if they do not exist', function () {
    $user = User::factory()->create();

    $server = Server::factory()->createForTest();
    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $server->token->shareWith($user, 'collaborator', []);

    $user->delete();

    $server->setStatus('failed');

    Notification::fake();

    (new PurgeServersWithFailedDeployments())->handle();

    Notification::assertSentTo($server->token->user, ServerFailedDeployment::class);
    Notification::assertTimesSent(1, ServerFailedDeployment::class);
});

it('does not notify the creator if they left the team', function () {
    $user = User::factory()->create();

    $server = Server::factory()->createForTest();
    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $server->setStatus('failed');

    Notification::fake();

    (new PurgeServersWithFailedDeployments())->handle();

    Notification::assertSentTo($server->token->user, ServerFailedDeployment::class);
    Notification::assertTimesSent(1, ServerFailedDeployment::class);
});

it('notifies only once if token owner and server creator are the same user', function () {
    $server = Server::factory()->createForTest();

    $server->setStatus('failed');

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $server->token->user_id);

    Notification::fake();

    (new PurgeServersWithFailedDeployments())->handle();

    Notification::assertSentTo($server->token->user, ServerFailedDeployment::class);
    Notification::assertTimesSent(1, ServerFailedDeployment::class);
});

it('should remove all servers with failed deployments', function () {
    $serverPassed = Server::factory()->createForTest();
    $serverFailed = Server::factory()->createForTest();

    $serverFailed->setStatus('failed');

    $this->assertDatabaseHas('servers', ['id' => $serverPassed->id]);
    $this->assertDatabaseHas('servers', ['id' => $serverFailed->id]);

    (new PurgeServersWithFailedDeployments())->handle();

    $this->assertDatabaseHas('servers', ['id' => $serverPassed->id]);
    $this->assertDatabaseMissing('servers', ['id' => $serverFailed->id]);
});

it('triggers the server deleted event when a server is deleted in this command', function () {
    Event::fake();
    $serverFailed = Server::factory()->createForTest();

    $serverFailed->setStatus('failed');

    $this->assertDatabaseHas('servers', ['id' => $serverFailed->id]);

    (new PurgeServersWithFailedDeployments())->handle();

    Event::assertDispatched(fn (ServerDeleted $event) => $event->server->is($serverFailed));
});
