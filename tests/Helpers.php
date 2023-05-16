<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Tests\TestCase;

function actingAs(Authenticatable $user, string $driver = null): TestCase
{
    return test()->actingAs($user, $driver);
}

function expectListenersToBeCalled(array $listeners, Closure $callback): void
{
    collect($listeners)->each(function ($listener) use ($callback) {
        $mock = Mockery::mock(new $listener())->makePartial();
        app()->instance($listener, $mock);

        $mock
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn ($event) => $callback($event));
    });
}
