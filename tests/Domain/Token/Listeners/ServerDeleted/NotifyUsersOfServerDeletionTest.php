<?php

declare(strict_types=1);

use App\Server\Notifications\ServerDeleted as NotificationsServerDeleted;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\Server;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('notifies collaborators if server was provisioned', function () {
    [$owner, $creator, $collaborator] = User::factory()->times(3)->create();

    $token = Token::factory()->create([
        'user_id'    => $owner->id,
        'deleted_at' => now(),
    ]);

    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $token->shareWith($creator, 'collaborator', ['server:create']);
    $token->shareWith($collaborator, 'collaborator', ['server:create']);

    $server = Server::factory()->create([
        'network_id'     => $network->id,
        'provisioned_at' => now(),
    ]);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    Notification::fake();

    $server->delete();

    Notification::assertSentTo([$owner, $creator, $collaborator], NotificationsServerDeleted::class);
    Notification::assertTimesSent(3, NotificationsServerDeleted::class);
});

it('notifies even if token was soft deleted', function () {
    $owner   = User::factory()->create();
    $creator = User::factory()->create();

    $token = Token::factory()->create([
        'user_id'    => $owner->id,
        'deleted_at' => now(),
    ]);

    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $server = Server::factory()->create([
        'network_id'     => $network->id,
        'provisioned_at' => null,
    ]);

    $token->shareWith($owner, 'collaborator', []);
    $token->shareWith($creator, 'collaborator', []);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    Notification::fake();

    $server->delete();

    Notification::assertSentTo([$owner, $creator], NotificationsServerDeleted::class);
    Notification::assertTimesSent(2, NotificationsServerDeleted::class);
});

it('notifies only owner and the creator if server was not provisioned', function () {
    $owner   = User::factory()->create();
    $creator = User::factory()->create();

    $token = Token::factory()->create([
        'user_id' => $owner->id,
    ]);

    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $server = Server::factory()->create([
        'network_id'     => $network->id,
        'provisioned_at' => null,
    ]);

    $token->shareWith($owner, 'collaborator', []);
    $token->shareWith($creator, 'collaborator', []);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    Notification::fake();

    $server->delete();

    Notification::assertSentTo([$owner, $creator], NotificationsServerDeleted::class);
    Notification::assertTimesSent(2, NotificationsServerDeleted::class);
});

it('notifies only once if owner and the creator are the same user', function () {
    $user = User::factory()->create();

    $token = Token::factory()->create([
        'user_id' => $user->id,
    ]);

    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $server = Server::factory()->create([
        'network_id'     => $network->id,
        'provisioned_at' => null,
    ]);

    $token->shareWith($user, 'collaborator', []);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    Notification::fake();

    $server->delete();

    Notification::assertSentTo($user, NotificationsServerDeleted::class);
    Notification::assertTimesSent(1, NotificationsServerDeleted::class);
});

it('does not notify the users that are not members of a team', function () {
    $user    = User::factory()->create();
    $creator = User::factory()->create();

    $token = Token::factory()->create([
        'user_id' => $user->id,
    ]);

    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $server = Server::factory()->create([
        'network_id'     => $network->id,
        'provisioned_at' => null,
    ]);

    $token->shareWith($user, 'collaborator', []);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    Notification::fake();

    $server->delete();

    Notification::assertSentTo($user, NotificationsServerDeleted::class);
    Notification::assertTimesSent(1, NotificationsServerDeleted::class);
});
