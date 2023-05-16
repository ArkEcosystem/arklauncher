<?php

declare(strict_types=1);

namespace Domain\Token\Models;

use App\Enums\NetworkTypeEnum;
use Domain\Server\Models\Server;
use Domain\Token\Events\NetworkCreated;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Support\AddressPrefixes;
use Support\Eloquent\Model;

/**
 * @property Token $token
 * @property int $servers_count
 */
final class Network extends Model
{
    protected $fillable = ['name'];

    protected $withCount = ['servers'];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => NetworkCreated::class,
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function scopeName(Builder $query, string $network): Builder|EloquentModel|null
    {
        return $query->where('name', $network)->first();
    }

    public function hasGenesis(): bool
    {
        return $this->servers()->where('preset', 'genesis')->count() > 0;
    }

    public function epoch() : ?string
    {
        return $this->created_at?->toISOString();
    }

    /**
     * @throws Exception
     */
    public function base58Prefix() : ?int
    {
        return AddressPrefixes::get(match ($this->name) {
            NetworkTypeEnum::MAINNET => $this->token->config['mainnetPrefix'] ?? null,
            NetworkTypeEnum::DEVNET  => $this->token->config['devnetPrefix'] ?? null,
            NetworkTypeEnum::TESTNET => $this->token->config['testnetPrefix'] ?? null,
            default                  => throw new Exception('Unknown NetworkType'),
        });
    }

    public function hasProvisionedGenesis(): bool
    {
        if ($this->hasGenesis()) {
            return $this->servers()->where('preset', 'genesis')->first()?->isProvisioned() ?? false;
        }

        return false;
    }

    public function getGenesis(): Server
    {
        return $this->servers()->where('preset', 'genesis')->firstOrFail();
    }

    // We use those methods to get faster and more consistent access to commonly used nested URLs.
    public function pathShow(): string
    {
        return route('tokens.show', [$this->token, $this]);
    }

    public function configurationPath() : string
    {
        return hash('sha256', $this->id.$this->name).'.zip';
    }
}
