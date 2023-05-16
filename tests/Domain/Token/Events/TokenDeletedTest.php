<?php

declare(strict_types=1);

use App\Enums\ActivityDescriptionEnum;
use Domain\Status\Models\Activity;
use Domain\Token\Events\TokenDeleted;
use Domain\Token\Listeners\TokenDeleted\NotifyCollaborators;
use Domain\Token\Listeners\TokenDeleted\PurgeTokenResources;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Event;

it('runs all the intended Listeners when a deleted token event is triggered', function () {
    $token = Token::factory()->create();

    expectListenersToBeCalled([
        PurgeTokenResources::class,
        NotifyCollaborators::class,
    ], fn ($event) => $event->token->is($token));

    TokenDeleted::dispatch($token);
});

it('logs the activity when fired', function () {
    Event::fakeExcept([TokenDeleted::class]);

    $token = $this->token();

    expect(Activity::all())->toHaveCount(0);

    TokenDeleted::dispatch($token);

    expect(Activity::all())->toHaveCount(1);

    $subject = Activity::all()->first();

    expect($subject->subject_type)->toBe(Token::class);
    expect($subject->description)->toBe(ActivityDescriptionEnum::DELETED);
});
