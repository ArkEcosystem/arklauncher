<?php

declare(strict_types=1);

use App\Enums\ActivityDescriptionEnum;
use Domain\Server\Models\Server;
use Domain\Status\Models\Activity;
use Domain\Token\Events\ServerCreated;
use Domain\Token\Listeners\ServerCreated\CreateServerOnProvider;
use Illuminate\Support\Facades\Event;

it('runs all the intended listeners when a created server event is triggered', function () {
    $server = Server::factory()->create();

    expectListenersToBeCalled([
        CreateServerOnProvider::class,
    ], fn ($event) => $event->server->is($server));

    ServerCreated::dispatch($server);
});

it('logs the activity when fired', function () {
    Event::fakeExcept([ServerCreated::class]);

    $server = Server::factory()->create();

    expect(Activity::where('subject_type', Server::class)->exists())->toBeFalse();

    ServerCreated::dispatch($server);

    expect(Activity::where('subject_type', Server::class)->count())->toBe(1);

    $subject = Activity::where('subject_type', Server::class)->first();

    expect($subject->description)->toBe(ActivityDescriptionEnum::CREATED);
});
