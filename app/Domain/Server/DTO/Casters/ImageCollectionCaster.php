<?php

declare(strict_types=1);

namespace Domain\Server\DTO\Casters;

use Domain\Server\DTO\Image;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Caster;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

final class ImageCollectionCaster implements Caster
{
    /**
     * @param mixed $value
     * @throws UnknownProperties
     * @return Collection<Image>
     */
    public function cast(mixed $value): Collection
    {
        return new Collection(array_map(
            fn (mixed $data) => is_a($data, Image::class) ? $data : new Image(...$data),
            $value
        ));
    }
}
