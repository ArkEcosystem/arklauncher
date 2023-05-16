<?php

declare(strict_types=1);

use App\Server\Jobs\IndexServerProviderPlans;
use App\Server\Notifications\IndexServerProviderPlansFailed;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderPlan;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('indexes available plans for a server provider', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/plans'), 200, []),
        ]);

    $serverProvider = ServerProvider::factory()->createForTest();

    expect(ServerProviderPlan::count())->toBe(0);

    (new IndexServerProviderPlans($serverProvider))->handle();

    expect(ServerProviderPlan::count())->toBeGreaterThan(0);
});

it('sends a notification to the token owner and the creator of the server provider if it fails to index available images for a server provider', function () {
    $user = User::factory()->create();

    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $serverProvider->token->shareWith($user, 'collaborator', []);

    expect(ServerProviderPlan::count())->toBe(0);

    (new IndexServerProviderPlans($serverProvider))->failed();

    expect(ServerProviderPlan::count())->toBe(0);

    Notification::assertSentTo([$serverProvider->token->user, $user], IndexServerProviderPlansFailed::class);
    Notification::assertTimesSent(2, IndexServerProviderPlansFailed::class);
});

it('prevents duplicate notifications', function () {
    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $serverProvider->token->user->id);

    expect(ServerProviderPlan::count())->toBe(0);

    (new IndexServerProviderPlans($serverProvider))->failed();

    expect(ServerProviderPlan::count())->toBe(0);

    Notification::assertSentTo($serverProvider->token->user, IndexServerProviderPlansFailed::class);
    Notification::assertTimesSent(1, IndexServerProviderPlansFailed::class);
});

it('does not notify users that are not members of the team', function () {
    $user = User::factory()->create();

    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    expect(ServerProviderPlan::count())->toBe(0);

    (new IndexServerProviderPlans($serverProvider))->failed();

    expect(ServerProviderPlan::count())->toBe(0);

    Notification::assertSentTo($serverProvider->token->user, IndexServerProviderPlansFailed::class);
    Notification::assertTimesSent(1, IndexServerProviderPlansFailed::class);
});

it('has the right tags', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $job = new IndexServerProviderPlans($serverProvider);

    expect($job->tags())->toBe(['plans', 'serverProvider:'.$serverProvider->id]);
});
