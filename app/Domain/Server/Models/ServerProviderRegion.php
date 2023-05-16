<?php

declare(strict_types=1);

namespace Domain\Server\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Support\Eloquent\Model;

final class ServerProviderRegion extends Model
{
    protected $fillable = ['uuid', 'name'];

    public function serverProvider(): BelongsToMany
    {
        return $this->belongsToMany(ServerProvider::class);
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
