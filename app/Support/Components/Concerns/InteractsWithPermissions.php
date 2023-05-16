<?php

declare(strict_types=1);

namespace Support\Components\Concerns;

use Illuminate\Support\Arr;

trait InteractsWithPermissions
{
    public array $permissions = [];

    public function selectAll(): void
    {
        $this->permissions = Arr::flatten($this->getAvailablePermissionsProperty());
    }

    public function deselectAll(): void
    {
        $this->permissions = [];
    }
}
