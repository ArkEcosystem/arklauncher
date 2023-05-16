<?php

declare(strict_types=1);

namespace Domain\Status\Models;

use ARKEcosystem\Foundation\Fortify\Models\Concerns\HasLocalizedTimestamps;
use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * @property array $properties
 */
final class Activity extends BaseActivity
{
    use HasLocalizedTimestamps;

    public function userId() : ?int
    {
        return $this->properties['user_id'];
    }
}
