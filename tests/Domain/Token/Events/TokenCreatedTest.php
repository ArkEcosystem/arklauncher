<?php

declare(strict_types=1);

use App\Enums\ActivityDescriptionEnum;
use Domain\Status\Models\Activity;
use Domain\Token\Events\TokenCreated;
use Domain\Token\Listeners\TokenCreated\CreateDefaultNetworks;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Event;

it('runs all the intended Listeners when a created token event is triggered', function () {
    $token = Token::factory()->create();

    expectListenersToBeCalled([
        CreateDefaultNetworks::class,
    ], fn ($event) => $event->token->is($token));

    TokenCreated::dispatch($token);
});

it('logs the activity when fired', function () {
    Event::fakeExcept([TokenCreated::class]);

    $token = $this->token();

    expect(Activity::all())->toHaveCount(0);

    TokenCreated::dispatch($token);

    expect(Activity::all())->toHaveCount(1);

    $subject = Activity::all()->first();

    expect($subject->subject_type)->toBe(Token::class);
    expect($subject->description)->toBe(ActivityDescriptionEnum::CREATED);
});
