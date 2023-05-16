<?php

declare(strict_types=1);

use App\Listeners\FlushMediaCache;
use Domain\Token\Models\Token;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

it('should flush the cache', function () {
    $subject = new FlushMediaCache();

    $token = Token::factory()->createForTest();

    $media = $token
        ->addMedia(UploadedFile::fake()->image('thumbnail.jpg'))
        ->toMediaCollection('photo');

    $token->logo; // Create the cache value

    expect(cache()->has("tokens.{$token->id}.logo"))->toBeTrue();

    $subject->handle(new MediaHasBeenAdded($media));

    expect(cache()->has("tokens.{$token->id}.logo"))->toBeFalse();
});
