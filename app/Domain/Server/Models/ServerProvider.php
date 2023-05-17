<?php

declare(strict_types=1);

namespace Domain\Server\Models;

use Domain\Server\Contracts\ServerProviderClient;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Services\ServerProviderClientFactory;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Support\Eloquent\Concerns\HasSchemalessAttributes;
use Support\Eloquent\Model;

/**
 * @property int $servers_count
 * @property Token $token
 */
final class ServerProvider extends Model
{
    use HasSchemalessAttributes;
    use SoftDeletes;

    protected $fillable = ['token_id', 'type', 'name', 'provider_key_id'];

    protected $casts = ['extra_attributes' => 'array'];

    protected array $encryptedExtraAttributes = ['accessToken', 'accessKey'];

    protected $withCount = ['servers'];

    public function user() : ?User
    {
        return User::where('id', $this->getMetaAttribute(ServerAttributeEnum::CREATOR))->first();
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(ServerProviderPlan::class);
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(ServerProviderRegion::class);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(ServerProviderImage::class);
    }

    public function client(): ServerProviderClient
    {
        return ServerProviderClientFactory::make($this);
    }

    public function allIndexed(): bool
    {
        $hasPlans   = $this->plans->count() >= 1;
        $hasRegions = $this->regions->count() >= 1;
        $hasImages  = $this->images->count() >= 1;

        return $hasPlans && $hasRegions && $hasImages;
    }
}
