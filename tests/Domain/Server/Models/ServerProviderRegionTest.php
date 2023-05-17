<?php

declare(strict_types=1);

use Domain\Server\Models\ServerProviderRegion;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('a_region_belongs_to_a_server_provider', function () {
    expect(ServerProviderRegion::factory()->create()->serverProvider())->toBeInstanceOf(BelongsToMany::class);
});

it('a_plan_has_many_servers', function () {
    expect(ServerProviderRegion::factory()->create()->servers())->toBeInstanceOf(HasMany::class);
});
