<?php

declare(strict_types=1);

use App\Enums\ActivityDescriptionEnum;
use Domain\Status\Models\Activity;
use Domain\Token\Events\NetworkCreated;
use Domain\Token\Models\Network;
use Illuminate\Support\Facades\Event;

it('logs the activity when fired', function () {
    Event::fakeExcept([NetworkCreated::class]);

    expect(Activity::all())->toHaveCount(0);

    $network = Network::factory()->create();

    expect(Activity::all())->toHaveCount(1);

    $subject = Activity::all()->first();

    expect($subject->subject_type)->toBe(Network::class);
    expect($subject->description)->toBe(ActivityDescriptionEnum::CREATED);
});
