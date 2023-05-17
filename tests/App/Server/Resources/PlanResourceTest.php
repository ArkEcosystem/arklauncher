<?php

declare(strict_types=1);

use App\Server\Resources\PlanResource;
use Domain\Server\DTO\Plan;
use Domain\Server\DTO\PlanCollection;
use Illuminate\Http\Request;

it('can transform a ressource', function () {
    $plan = new Plan([
            'id'      => 's-1vcpu-1gb',
            'disk'    => 25,
            'memory'  => 1024,
            'cores'   => 1,
            'regions' => ['ams2', 'ams3', 'blr1', 'fra1', 'lon1', 'nyc1', 'nyc2', 'nyc3', 'sfo1', 'sfo2', 'sgp1', 'tor1'],
        ]);

    $actual = (new PlanResource($plan))->toArray(new Request());

    expect($actual)->toBeArray();
});

it('can transform a collection', function () {
    $plans = new PlanCollection(items: [
        [
            'id'      => 's-1vcpu-1gb',
            'disk'    => 25,
            'memory'  => 1024,
            'cores'   => 1,
            'regions' => ['ams2', 'ams3', 'blr1', 'fra1', 'lon1', 'nyc1', 'nyc2', 'nyc3', 'sfo1', 'sfo2', 'sgp1', 'tor1'],
        ],
    ]);

    $actual = PlanResource::collection($plans->items)->toArray(new Request());

    expect($actual)->toBeArray();
    expect($actual)->toHaveCount(1);
});
