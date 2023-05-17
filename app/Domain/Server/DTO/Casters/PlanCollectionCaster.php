<?php

declare(strict_types=1);

namespace Domain\Server\DTO\Casters;

use Domain\Server\DTO\Plan;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Caster;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

final class PlanCollectionCaster implements Caster
{
    /**
     * @param mixed $value
     * @throws UnknownProperties
     * @return Collection<Plan>
     */
    public function cast(mixed $value): Collection
    {
        return new Collection(array_map(
            fn (mixed $data) => is_a($data, Plan::class) ? $data : new Plan(...$data),
            $value
        ));
    }
}
