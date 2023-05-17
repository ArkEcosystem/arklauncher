<?php

declare(strict_types=1);

namespace Domain\Collaborator\Models;

use ARKEcosystem\Foundation\Fortify\Models\Concerns\HasLocalizedTimestamps;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class Collaborator extends Pivot
{
    use HasLocalizedTimestamps;

    protected $table = 'token_users';

    protected $casts = ['permissions' => 'json'];

    public static function availablePermissions(): array
    {
        return [
            'token:delete',
            'token:update',
            'collaborator:create',
            'collaborator:delete',
            'server-provider:create',
            'server-provider:delete',
            'server-provider:update',
            'server:create',
            'server:delete',
            'server:rename',
            'server:restart',
            'server:start',
            'server:stop',
            'ssh-key:manage',
        ];
    }
}
