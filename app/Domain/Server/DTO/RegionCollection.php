<?php

declare(strict_types=1);

namespace Domain\Server\DTO;

use Domain\Server\DTO\Casters\RegionCollectionCaster;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
final class RegionCollection extends DataTransferObject
{
    /** @var Collection<Region> */
    #[CastWith(RegionCollectionCaster::class)]
    public Collection $items;
}
