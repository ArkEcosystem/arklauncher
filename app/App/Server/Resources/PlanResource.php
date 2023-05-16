<?php

declare(strict_types=1);

namespace App\Server\Resources;

use Domain\Server\DTO\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Plan */
final class PlanResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'label'   => $this->disk.'GB / '.$this->memory.'MB / '.$this->cores.' Cores',
            'value'   => $this->id,
            'memory'  => $this->memory,
            'regions' => $this->regions,
        ];
    }
}
