<?php

declare(strict_types=1);

namespace Domain\Server\Models;

use Carbon\Carbon;
use Domain\SecureShell\Contracts\Script;
use Domain\Server\Enums\PresetTypeEnum;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Token\Events\ServerDeleted;
use Domain\Token\Events\ServerUpdating;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\ModelStatus\HasStatuses;
use Support\Eloquent\Concerns\HasSchemalessAttributes;
use Support\Eloquent\Model;
use Support\Services\PasswordGenerator;
use Znck\Eloquent\Relations\BelongsToThrough;

/**
 * @property ServerProvider $serverProvider
 * @property int $provider_server_id
 * @property Token $token
 * @property Network $network
 * @property ServerProviderRegion $region
 * @property ServerProviderPlan $plan
 * @property string $status
 */
final class Server extends Model
{
    use \Znck\Eloquent\Traits\BelongsToThrough;
    use HasSchemalessAttributes;
    use HasStatuses;

    public const PROVISIONING_STATES = [
        'provisioning',
        'configuring_locale',
        'installing_system_dependencies',
        'installing_nodejs',
        'installing_yarn',
        'installing_pm2',
        'installing_program_dependencies',
        'installing_postgresql',
        'installing_ntp',
        'updating_system',
        'securing_node',
        'generating_network_configuration',
        'installing_core',
        'creating_core_alias',
        'configuring_forger',
        'configuring_database',
        'installing_docker',
        'cloning_explorer',
        'configuring_explorer',
        'building_explorer',
        'creating_boot_script',
        'starting_processes',
    ];

    protected $fillable = [
        'network_id',
        'server_provider_id',
        'server_provider_plan_id',
        'server_provider_region_id',
        'server_provider_image_id',
        'name',
        'user_password',
        'sudo_password',
        'delegate_passphrase',
        'delegate_password',
        'provider_server_id',
        'ip_address',
        'provisioning_job_dispatched_at',
        'provisioned_at',
        'preset',
        'core_version',
    ];

    protected $casts = [
        'provisioning_job_dispatched_at' => 'datetime',
        'provisioned_at'                 => 'datetime',
        'extra_attributes'               => 'array',
        'sudo_password'                  => 'encrypted',
        'user_password'                  => 'encrypted',
        'delegate_passphrase'            => 'encrypted',
        'delegate_password'              => 'encrypted',
    ];

    protected array $enums = [
        'preset' => PresetTypeEnum::class.':nullable',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'deleted'  => ServerDeleted::class,
        'updating' => ServerUpdating::class,
    ];

    public function creator() : ?User
    {
        return User::where('id', $this->getMetaAttribute(ServerAttributeEnum::CREATOR))->first();
    }

    public function token(): BelongsToThrough
    {
        return $this->belongsToThrough(Token::class, Network::class);
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }

    public function serverProvider(): BelongsTo
    {
        return $this->belongsTo(ServerProvider::class);
    }

    public function plan(): HasOne
    {
        return $this->hasOne(ServerProviderPlan::class, 'id', 'server_provider_plan_id');
    }

    public function region(): HasOne
    {
        return $this->hasOne(ServerProviderRegion::class, 'id', 'server_provider_region_id');
    }

    public function image(): HasOne
    {
        return $this->hasOne(ServerProviderImage::class, 'id', 'server_provider_image_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ServerTask::class);
    }

    public function isGenesis(): bool
    {
        if ($this->preset !== null) {
            return PresetTypeEnum::isGenesis($this->preset);
        }

        return false;
    }

    public function isSeed(): bool
    {
        if ($this->preset !== null) {
            return PresetTypeEnum::isSeed($this->preset);
        }

        return false;
    }

    public function isRelay(): bool
    {
        if ($this->preset !== null) {
            return PresetTypeEnum::isRelay($this->preset);
        }

        return false;
    }

    public function isForger(): bool
    {
        if ($this->preset !== null) {
            return PresetTypeEnum::isForger($this->preset);
        }

        return false;
    }

    public function isExplorer(): bool
    {
        if ($this->preset !== null) {
            return PresetTypeEnum::isExplorer($this->preset);
        }

        return false;
    }

    public function hasAuthorizedKeys(): bool
    {
        return (count($this->getAuthorizedKeys()) - 1) > 0;
    }

    public function getAuthorizedKeys(): array
    {
        // Include user keys and our app's key
        return array_merge(
            $this->token->secureShellKeys()->pluck('public_key')->toArray(),
            [$this->token->keypair['publicKey'] ?? []]
        );
    }

    public function addTask(Script $script): ServerTask
    {
        $type = get_class($script);

        $task = $this->tasks()->whereType($type)->first();

        if ($task !== null) {
            return $task;
        }

        $options = ['timeout' => $script->timeout()];

        $task = $this->tasks()->create([
            'type'    => $type,
            'name'    => $script->name(),
            'user'    => $script->user(),
            'options' => $options,
            'script'  => $script->script(),
            'output'  => '',
        ]);

        $task->setStatus('pending');

        return $task;
    }

    public function isReadyForProvisioning(): bool
    {
        if ($this->isProvisioning()) {
            return false;
        }

        return (bool) $this->ip_address;
    }

    public function isProvisioning(): bool
    {
        return in_array($this->status, static::PROVISIONING_STATES, true);
    }

    public function isProvisioned(): bool
    {
        return $this->provisioned_at !== null;
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsOnline() : self
    {
        $this->setStatus('online');

        return $this;
    }

    public function isOnline() : bool
    {
        return $this->status === 'online';
    }

    public function markAsOffline() : self
    {
        $this->setStatus('offline');

        return $this;
    }

    public function isOffline() : bool
    {
        return $this->status === 'offline';
    }

    public function olderThan(int $minutes): bool
    {
        return $this->created_at?->lte(Carbon::now()->subMinutes($minutes)) ?? false;
    }

    // We use those methods to get faster and more consistent access to commonly used nested URLs.
    public function pathShow(): string
    {
        return route('tokens.servers.show', [$this->token, $this->network, $this]);
    }

    public function pathStart(): string
    {
        return route('tokens.servers.start', [$this->token, $this->network, $this]);
    }

    public function pathStop(): string
    {
        return route('tokens.servers.stop', [$this->token, $this->network, $this]);
    }

    public function pathReboot(): string
    {
        return route('tokens.servers.reboot', [$this->token, $this->network, $this]);
    }

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted() : void
    {
        static::deleting(function (self $server) {
            $server->tasks()->delete();
        });

        static::creating(function (self $server) {
            $server->user_password = PasswordGenerator::make(length: 32);
            $server->sudo_password = PasswordGenerator::make(length: 32);
        });
    }
}
