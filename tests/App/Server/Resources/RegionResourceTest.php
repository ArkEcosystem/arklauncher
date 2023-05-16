<?php

declare(strict_types=1);

use App\Server\Resources\RegionResource;
use Domain\Server\DTO\Region;
use Domain\Server\DTO\RegionCollection;
use Illuminate\Http\Request;

it('can transform a resource', function () {
    $region = new Region([
            'id'   => 'nyc1',
            'name' => 'New York 1',
        ]);

    $actual = (new RegionResource($region))->toArray(new Request());

    expect($actual)->toBeArray();
});

it('can transform a collection', function () {
    $regions = new RegionCollection(items: [
            [
                'id'   => 'nyc1',
                'name' => 'New York 1',
            ],
        ]);

    $actual = RegionResource::collection($regions->items)->toArray(new Request());

    expect($actual)->toBeArray();
    expect($actual)->toHaveCount(1);
});
