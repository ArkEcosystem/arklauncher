<?php

declare(strict_types=1);

namespace Domain\Server\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Support\Eloquent\Model;

final class ServerProviderPlan extends Model
{
    protected $fillable = ['uuid', 'disk', 'memory', 'cores', 'regions'];

    protected $casts = ['regions' => 'array'];

    public function serverProvider(): BelongsToMany
    {
        return $this->belongsToMany(ServerProvider::class);
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function getFormattedMemoryAttribute(): string
    {
        if ($this->memory < 1024) {
            return $this->memory.'MB';
        }

        return round($this->memory / 1024, 0).'GB';
    }

    protected static function booted()
    {
        static::addGlobalScope(
            'resources',
            fn (Builder $builder) => $builder->orderBy('cores')->orderBy('disk')->orderBy('memory')
        );
    }
}
