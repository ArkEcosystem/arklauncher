<?php

declare(strict_types=1);

namespace App\Server\Resources;

use Domain\Server\DTO\Region;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Region */
final class RegionResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'label' => $this->name,
            'value' => $this->id,
        ];
    }
}
