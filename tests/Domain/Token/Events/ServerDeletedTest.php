<?php

declare(strict_types=1);

use App\Enums\ActivityDescriptionEnum;
use Domain\Server\Models\Server;
use Domain\Status\Models\Activity;
use Domain\Token\Events\ServerDeleted;
use Domain\Token\Listeners\ServerDeleted\DestroyServerOnServerProvider;
use Domain\Token\Listeners\ServerDeleted\NotifyUsersOfServerDeletion;
use Illuminate\Support\Facades\Event;

it('runs all the intended listeners when a deleted server event is triggered', function () {
    $server = Server::factory()->create();

    expectListenersToBeCalled([
        NotifyUsersOfServerDeletion::class,
        DestroyServerOnServerProvider::class,
    ], fn ($event) => $event->server->is($server));

    ServerDeleted::dispatch($server);
});

it('logs the activity when fired', function () {
    Event::fakeExcept([ServerDeleted::class]);

    $server = Server::factory()->create();

    expect(Activity::where('subject_type', Server::class)->exists())->toBeFalse();

    $server->delete();

    expect(Activity::where('subject_type', Server::class)->count())->toBe(1);

    $subject = Activity::where('subject_type', Server::class)->first();

    expect($subject->description)->toBe(ActivityDescriptionEnum::DELETED);
});
