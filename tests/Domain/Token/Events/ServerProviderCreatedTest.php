<?php

declare(strict_types=1);

use App\Enums\ActivityDescriptionEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Status\Models\Activity;
use Domain\Token\Events\ServerProviderCreated;
use Illuminate\Support\Facades\Event;

it('logs the activity when fired', function () {
    Event::fakeExcept([ServerProviderCreated::class]);

    $serverProvider = ServerProvider::factory()->create();

    expect(Activity::where('subject_type', ServerProvider::class)->exists())->toBeFalse();

    ServerProviderCreated::dispatch($serverProvider);

    expect(Activity::where('subject_type', ServerProvider::class)->count())->toBe(1);

    $subject = Activity::where('subject_type', ServerProvider::class)->first();

    expect($subject->description)->toBe(ActivityDescriptionEnum::CREATED);
});
