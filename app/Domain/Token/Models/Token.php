<?php

declare(strict_types=1);

namespace Domain\Token\Models;

use ARKEcosystem\Foundation\Hermes\Contracts\HasNotificationLogo;
use Domain\Coin\Models\Coin;
use Domain\Collaborator\Models\Collaborator;
use Domain\Collaborator\Models\Invitation;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Server\Enums\PresetTypeEnum;
use Domain\Server\Enums\ServerDeploymentStatus;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Enums\TokenAttributeEnum;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Propaganistas\LaravelFakeId\RoutesWithFakeIds;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ModelStatus\HasStatuses;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\Eloquent\Concerns\HasSchemalessAttributes;
use Support\Eloquent\Model;
use Support\Services\OnboardingManager;
use Throwable;

/**
 * @property int $servers_count
 * @property int $server_providers_count
 * @property int $secure_shell_keys_count
 * @property Collection $collaborators
 * @property Collaborator $pivot
 * @property User $user
 * @property array $keypair
 * @property Coin $coin
 * @property string $status
 * @property string $logo
 */
final class Token extends Model implements HasMedia, HasNotificationLogo
{
    use HasSchemalessAttributes;
    use InteractsWithMedia;
    use HasSlug;
    use HasStatuses;
    use SoftDeletes;
    use RoutesWithFakeIds;

    protected $fillable = ['user_id', 'coin_id', 'name', 'config', 'keypair', 'onboarded_at'];

    protected $casts = [
        'config'           => 'array',
        'keypair'          => 'array',
        'extra_attributes' => 'array',
        'onboarded_at'     => 'datetime',
    ];

    protected $with = ['media'];

    protected $withCount = ['servers', 'serverProviders', 'secureShellKeys'];

    public function purge(bool $removeServersFromProvider = true) : void
    {
        $this->invitations()->delete();
        $this->statuses()->delete();

        if ($removeServersFromProvider) {
            $this->servers->each->delete();
        } else {
            // By not dispatching model events, jobs that delete servers from server provider will not fire...
            Server::withoutEvents(function () {
                $this->servers->each->delete();
            });
        }

        $this->networks()->delete();
        $this->serverProviders()->delete();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coin(): BelongsTo
    {
        return $this->belongsTo(Coin::class);
    }

    public function serverProviders(): HasMany
    {
        return $this->hasMany(ServerProvider::class);
    }

    public function networks(): HasMany
    {
        return $this->hasMany(Network::class);
    }

    public function servers(): HasManyThrough
    {
        return $this->hasManyThrough(Server::class, Network::class);
    }

    public function network(string $name): ?Network
    {
        return $this->networks()->where('name', $name)->first();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function collaborators(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'token_users', 'token_id', 'user_id')
            ->using(Collaborator::class)
            ->withPivot(['role', 'permissions', 'created_at']);
    }

    public function hasCollaborator(User $user) : bool
    {
        return $this->collaborators->contains($user) || $user->is($this->user);
    }

    public function secureShellKeys(): BelongsToMany
    {
        return $this->belongsToMany(SecureShellKey::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(250); // 255 but room for suffix
    }

    public function canBeEdited(): bool
    {
        return $this->servers_count === 0;
    }

    public function shareWith(User $user, string $role = 'collaborator', array $permissions = []): void
    {
        $this->collaborators()->detach($user);

        $this->collaborators()->attach($user, [
            'role'        => $role,
            'permissions' => $permissions,
        ]);

        unset($this->collaborators);
    }

    public function stopSharingWith(User $user): void
    {
        $this->collaborators()->detach($user);

        unset($this->collaborators);
    }

    public function getLogoAttribute(): string
    {
        return Cache::rememberForever(
            "tokens.{$this->id}.logo",
            fn () => $this->getFirstMediaUrl('logo')
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function hasSecureShellKeys(): bool
    {
        return $this->secure_shell_keys_count > 0;
    }

    public function hasServerProviders(): bool
    {
        return $this->server_providers_count > 0;
    }

    public function needsServerConfiguration(): bool
    {
        return $this->servers_count === 0 && ! $this->hasServerConfiguration();
    }

    public function hasServers(): bool
    {
        return $this->servers_count > 0;
    }

    public function hasProvisionedGenesisServer(): bool
    {
        return Cache::rememberForever("tokens.{$this->id}.hasProvisionedGenesisServer", function (): bool {
            return $this
                ->servers()
                ->where('preset', PresetTypeEnum::GENESIS)
                ->whereNotNull('provisioned_at')
                ->whereHas('statuses', fn (Builder $query) => $query->where('name', ServerDeploymentStatus::PROVISIONED))
                ->count() > 0;
        });
    }

    public function onboarding(): OnboardingManager
    {
        return new OnboardingManager($this);
    }

    public function setKeypair(array $keypair): void
    {
        $keypair['privateKey'] = encrypt($keypair['privateKey']);
        $this->keypair         = $keypair;
        $this->save();
    }

    public function getPrivateKey(): string
    {
        return decrypt($this->keypair['privateKey'] ?? null);
    }

    public function availableKeys(): Collection
    {
        return $this->collaborators
            ->map(fn ($collaborator) => $collaborator->secureShellKeys)
            ->flatten();
    }

    public function hasAuthorizedKeys(): bool
    {
        return $this
            ->collaborators
            ->map(fn ($collaborator) => $collaborator->secureShellKeys->pluck('public_key'))
            ->flatten()
            ->count() > 0;
    }

    public function allows(User $user, string $ability): bool
    {
        if ($user->ownsToken($this)) {
            return true;
        }

        try {
            $collaborator = $this->collaborators()->where('user_id', $user->id)->firstOrFail();

            return count(array_intersect($collaborator->pivot->permissions ?? [], ['*', $ability])) > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function getFirstIndexedServerProvider(): ?ServerProvider
    {
        return $this->serverProviders->filter->allIndexed()->first();
    }

    public function hasAnyIndexedServerProvider(): bool
    {
        return $this->serverProviders->filter->allIndexed()->count() > 0;
    }

    public function getNormalizedTokenAttribute(): string
    {
        return strtolower($this->config['token'] ?? null);
    }

    public function flushCache(): void
    {
        Cache::forget("tokens.{$this->id}.logo");
        Cache::forget("tokens.{$this->id}.hasProvisionedGenesisServer");
    }

    public function logo() : ?Media
    {
        return $this->getFirstMedia('logo');
    }

    public function fallbackIdentifier() : ?string
    {
        return $this->name;
    }

    public function hasServerConfiguration(): bool
    {
        return Arr::get($this->getMetaAttribute(TokenAttributeEnum::SERVER_CONFIG), 'server_provider_id') !== null;
    }

    public function forgetServerConfiguration(): void
    {
        $this->forgetMetaAttribute(TokenAttributeEnum::SERVER_CONFIG);
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function (self $token) {
            $token->shareWith($token->user, 'owner');

            $token->setStatus(TokenStatusEnum::PENDING);
        });
    }
}
