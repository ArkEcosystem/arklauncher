<?php

declare(strict_types=1);

namespace Domain\Server\DTO;

use Domain\Server\DTO\Casters\PlanCollectionCaster;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
final class PlanCollection extends DataTransferObject
{
    /** @var Collection<Plan> */
    #[CastWith(PlanCollectionCaster::class)]
    public Collection $items;
}
