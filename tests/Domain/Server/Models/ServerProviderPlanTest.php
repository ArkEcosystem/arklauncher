<?php

declare(strict_types=1);

use Domain\Server\Models\ServerProviderPlan;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('a plan belongs to a server provider', function () {
    expect(ServerProviderPlan::factory()->create()->serverProvider())->toBeInstanceOf(BelongsToMany::class);
});

it('a plan has many servers', function () {
    expect(ServerProviderPlan::factory()->create()->servers())->toBeInstanceOf(HasMany::class);
});

it('should format the memory to MB or GB', function () {
    expect(ServerProviderPlan::factory()->create(['memory' => 512])->formatted_memory)->toBe('512MB');
    expect(ServerProviderPlan::factory()->create(['memory' => 1024])->formatted_memory)->toBe('1GB');
    expect(ServerProviderPlan::factory()->create(['memory' => 2048])->formatted_memory)->toBe('2GB');
    expect(ServerProviderPlan::factory()->create(['memory' => 4096])->formatted_memory)->toBe('4GB');
    expect(ServerProviderPlan::factory()->create(['memory' => 8196])->formatted_memory)->toBe('8GB');
    expect(ServerProviderPlan::factory()->create(['memory' => 16384])->formatted_memory)->toBe('16GB');
    expect(ServerProviderPlan::factory()->create(['memory' => 32768])->formatted_memory)->toBe('32GB');
    expect(ServerProviderPlan::factory()->create(['memory' => 65536])->formatted_memory)->toBe('64GB');
    expect(ServerProviderPlan::factory()->create(['memory' => 131072])->formatted_memory)->toBe('128GB');
});
