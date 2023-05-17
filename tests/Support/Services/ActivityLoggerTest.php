<?php

declare(strict_types=1);

use Domain\Status\Models\Activity;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Config;
use Support\Services\ActivityLogger;

it('should insert in the database', function () {
    expect(Activity::all())->toHaveCount(0);

    ActivityLogger::log($this->user(), 'foo');

    expect(Activity::all())->toHaveCount(1);
});

it('should insert in the database if log have extra attributes', function () {
    expect(Activity::all())->toHaveCount(0);

    ActivityLogger::log($this->user(), 'foo', null, ['bar' => 'baz', 'user_id' => $this->user()->id]);

    expect(Activity::all())->toHaveCount(1);

    $subject = Activity::all()->first();

    expect($subject->getExtraProperty('bar'))->toBe('baz');
});

it('should have a localized created_at and updated_at attribute', function () {
    expect(Activity::all())->toHaveCount(0);

    ActivityLogger::log($this->user(), 'foo', null, ['bar' => 'baz', 'user_id' => $this->user()->id]);

    expect(Activity::all())->toHaveCount(1);

    $subject = Activity::all()->first();

    expect($subject->created_at_local)->toBeInstanceOf(Illuminate\Support\Carbon::class);
    expect($subject->updated_at_local)->toBeInstanceOf(Illuminate\Support\Carbon::class);
});

it('should have a user_id attribute', function () {
    expect(Activity::all())->toHaveCount(0);

    $user = User::factory()->create();

    ActivityLogger::log($user, 'foo', null, ['bar' => 'baz', 'user_id' => $user->id]);

    expect(Activity::all())->toHaveCount(1);

    $subject = Activity::all()->first();

    expect($subject->getExtraProperty('user_id'))->toBe($user->id);
});

it('should be able to use the fallback to get the user_id if none is provided', function () {
    expect(Activity::all())->toHaveCount(0);

    $user = User::factory()->create();

    $token = Token::factory()->ownedBy($user)->createForTest();

    ActivityLogger::log($user, 'foo', null, ['bar' => 'baz'], $token->user->id);

    expect(Activity::all())->toHaveCount(1);

    $subject = Activity::all()->first();

    expect($subject->getExtraProperty('user_id'))->toBe($token->user->id);
});

it('should not be the same times depending on the user timezone', function () {
    expect(Activity::all())->toHaveCount(0);

    $user = User::factory()->create();

    ActivityLogger::log($user, 'foo', null, ['bar' => 'baz', 'user_id' => $user->id]);

    expect(Activity::all())->toHaveCount(1);

    $subject          = Activity::all()->first();
    $subjectCreatedAt = $subject->created_at_local;
    $subjectUpdatedAt = $subject->updated_at_local;

    $user->timezone = 'Asia/Tokyo';
    $user->save();

    Config::set('app.timezone', $user->timezone);

    expect($subjectCreatedAt->getTimezone()->toRegionName())->toBe('UTC');
    expect($subject->created_at_local->getTimezone()->toRegionName())->toBe('Asia/Tokyo');

    expect($subjectUpdatedAt->getTimezone()->toRegionName())->toBe('UTC');
    expect($subject->updated_at_local->getTimezone()->toRegionName())->toBe('Asia/Tokyo');
});
