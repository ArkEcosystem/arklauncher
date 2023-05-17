<?php

declare(strict_types=1);

use Domain\Coin\Models\Coin;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('a coin has many tokens', function () {
    $coin = Coin::factory()->create();

    expect($coin->tokens())->toBeInstanceOf(HasMany::class);
});

it('a coin has a slug', function () {
    $coin = Coin::factory()->create();

    expect($coin->slug)->toBe('ark');
});

it('a coin has slug options', function () {
    $coin        = Coin::factory()->create();
    $slugOptions = $coin->getSlugOptions();

    expect($slugOptions->slugField)->toBe('slug');
    expect($slugOptions->maximumLength)->toBe(95);
});
