<?php

declare(strict_types=1);

namespace Domain\Server\DTO\Casters;

use Domain\Server\DTO\Region;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Caster;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

final class RegionCollectionCaster implements Caster
{
    /**
     * @param mixed $value
     * @throws UnknownProperties
     * @return Collection<Region>
     */
    public function cast(mixed $value): Collection
    {
        return new Collection(array_map(
            fn (mixed $data) => is_a($data, Region::class) ? $data : new Region(...$data),
            $value
        ));
    }
}
