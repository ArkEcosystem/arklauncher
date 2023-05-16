<?php

declare(strict_types=1);

use App\Server\Jobs\IndexServerProviderImages;
use App\Server\Notifications\IndexServerProviderImagesFailed;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('indexes available images for a server provider', function () {
    Http::fake([
            'hetzner.cloud/*' => Http::response($this->fixture('hetzner/images'), 200, []),
        ]);

    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();

    expect(ServerProviderImage::count())->toBe(0);

    (new IndexServerProviderImages($serverProvider))->handle();

    expect(ServerProviderImage::count())->toBeGreaterThan(0);
});

it('sends a notification to the token owner and the creator of the server provider if it fails to index available images for a server provider', function () {
    $user = User::factory()->create();

    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $serverProvider->token->shareWith($user, 'collaborator', []);

    expect(ServerProviderImage::count())->toBe(0);

    (new IndexServerProviderImages($serverProvider))->failed();

    expect(ServerProviderImage::count())->toBe(0);

    Notification::assertSentTo([$serverProvider->token->user, $user], IndexServerProviderImagesFailed::class);
    Notification::assertTimesSent(2, IndexServerProviderImagesFailed::class);
});

it('prevents duplicate notifications', function () {
    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $serverProvider->token->user->id);

    expect(ServerProviderImage::count())->toBe(0);

    (new IndexServerProviderImages($serverProvider))->failed();

    expect(ServerProviderImage::count())->toBe(0);

    Notification::assertSentTo($serverProvider->token->user, IndexServerProviderImagesFailed::class);
    Notification::assertTimesSent(1, IndexServerProviderImagesFailed::class);
});

it('does not notify users that are not members of the team', function () {
    $user = User::factory()->create();

    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    expect(ServerProviderImage::count())->toBe(0);

    (new IndexServerProviderImages($serverProvider))->failed();

    expect(ServerProviderImage::count())->toBe(0);

    Notification::assertSentTo($serverProvider->token->user, IndexServerProviderImagesFailed::class);
    Notification::assertTimesSent(1, IndexServerProviderImagesFailed::class);
});

it('has the right tags', function () {
    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();

    $job = new IndexServerProviderImages($serverProvider);

    expect($job->tags())->toBe(['images', 'serverProvider:'.$serverProvider->id]);
});
