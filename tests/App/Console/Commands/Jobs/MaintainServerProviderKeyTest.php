<?php

declare(strict_types=1);

use App\Console\Commands\Jobs\MaintainServerProviderKey;
use App\Server\Notifications\ServerProviderAuthenticationFailed;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderDeleted;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('checks if api key for server provider is still valid', function () {
    Http::fakeSequence()
            ->push([], 404, []);

    $serverProvider = ServerProvider::factory()->createForTest();

    $user = User::factory()->create();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $serverProvider->token->shareWith($user, 'collaborator', []);

    (new MaintainServerProviderKey($serverProvider))->handle();

    Notification::assertSentTo([$serverProvider->token->user, $user], ServerProviderAuthenticationFailed::class);
    Notification::assertTimesSent(2, ServerProviderAuthenticationFailed::class);
});

it('prevents duplicate notifications', function () {
    Http::fakeSequence()
            ->push([], 404, []);

    $serverProvider = ServerProvider::factory()->createForTest();

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $serverProvider->token->user->id);

    (new MaintainServerProviderKey($serverProvider))->handle();

    Notification::assertSentTo($serverProvider->token->user, ServerProviderAuthenticationFailed::class);
    Notification::assertTimesSent(1, ServerProviderAuthenticationFailed::class);
});

it('triggers the server provider deleted event when and invalid provider is deleted', function () {
    Event::fake();

    Http::fakeSequence()
            ->push([], 404, []);

    $serverProvider = ServerProvider::factory()->createForTest();

    (new MaintainServerProviderKey($serverProvider))->handle();

    Event::assertDispatched(fn (ServerProviderDeleted $event) => $event->serverProvider->is($serverProvider));
});
