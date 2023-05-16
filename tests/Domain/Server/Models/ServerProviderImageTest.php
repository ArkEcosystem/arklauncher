<?php

declare(strict_types=1);

use Domain\Server\Models\ServerProviderImage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('an image belongs to a server provider', function () {
    expect(ServerProviderImage::factory()->create()->serverProvider())->toBeInstanceOf(BelongsToMany::class);
});

it('an image has many servers', function () {
    expect(ServerProviderImage::factory()->create()->servers())->toBeInstanceOf(HasMany::class);
});
