<?php

declare(strict_types=1);

namespace Domain\Server\DTO;

use Domain\Server\DTO\Casters\ImageCollectionCaster;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
final class ImageCollection extends DataTransferObject
{
    /** @var Collection<Image> */
    #[CastWith(ImageCollectionCaster::class)]
    public Collection $items;
}
