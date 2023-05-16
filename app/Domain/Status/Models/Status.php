<?php

declare(strict_types=1);

namespace Domain\Status\Models;

use ARKEcosystem\Foundation\Fortify\Models\Concerns\HasLocalizedTimestamps;

final class Status extends \Spatie\ModelStatus\Status
{
    use HasLocalizedTimestamps;
}
