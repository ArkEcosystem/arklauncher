<?php

declare(strict_types=1);

namespace Domain\User\Models;

use App\User\Mail\ConfirmEmailChange;
use ARKEcosystem\Foundation\Fortify\Models\User as Fortify;
use ARKEcosystem\Foundation\Hermes\Models\Concerns\HasNotifications;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Domain\Collaborator\Models\Collaborator;
use Domain\Collaborator\Models\Invitation;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Support\Eloquent\Concerns\HasSchemalessAttributes;

final class User extends Fortify
{
    use HasNotifications;
    use HasSchemalessAttributes;

    protected $casts = [
        'extra_attributes'  => 'array',
        'email_verified_at' => 'datetime',
        'onboarded_at'      => 'datetime',
        'last_login_at'     => 'datetime',
    ];

    public function ownedTokens() : HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function tokens(): BelongsToMany
    {
        return $this
            ->belongsToMany(Token::class, 'token_users', 'user_id', 'token_id')
            ->using(Collaborator::class)
            ->withPivot(['role', 'permissions'])
            ->whereHas('statuses', fn (Builder $query) => $query->where('name', TokenStatusEnum::FINISHED));
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class)->orWhere('email', $this->email);
    }

    public function secureShellKeys(): HasMany
    {
        return $this->hasMany(SecureShellKey::class);
    }

    public function starredNotifications(): MorphMany
    {
        return $this->notifications()->where('is_starred', true);
    }

    public function hasTokens(): bool
    {
        return $this->tokens->isNotEmpty();
    }

    public function onToken(Token $token): bool
    {
        return $token->collaborators->contains($this);
    }

    public function ownsToken(Token $token): bool
    {
        $ownerId = $token->user_id;

        return $ownerId !== 0 && $this->id === $ownerId;
    }

    public function roleOn(Token $token): ?string
    {
        if ($this->tokens->contains($token)) {
            // https://github.com/nunomaduro/larastan/issues/515
            /* @phpstan-ignore-next-line */
            return $this->tokens->find($token->id)?->pivot->role;
        }

        return null;
    }

    public function permissionsOn(Token $token): ?array
    {
        if ($this->tokens->contains($token)) {
            // https://github.com/nunomaduro/larastan/issues/515
            /* @phpstan-ignore-next-line */
            return $this->tokens->find($token->id)?->pivot->permissions;
        }

        return null;
    }

    public function notifications(): MorphMany
    {
        return $this
            ->morphMany(DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc')
            ->orderBy('id');
    }

    public function hasNewNotifications(): bool
    {
        $latestNotification = $this->notifications()->latest()->first();

        if ($latestNotification === null || $latestNotification->created_at === null) {
            return false;
        }

        if ($this->seen_notifications_at === null) {
            return true;
        }

        return $latestNotification->created_at->isAfter($this->seen_notifications_at);
    }

    /**
     * @codeCoverageIgnore
     */
    public function selectPersonalData(PersonalDataSelection $personalData): void
    {
        $personalData->add(sprintf('%s.json', Str::slug($this->name)), [
            'name'  => $this->name,
            'email' => $this->email,
        ]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function personalDataExportName(): string
    {
        return 'personal-data-'.Str::slug($this->name).'.zip';
    }

    public function waitingForEmailConfirmation() : bool
    {
        if ($this->getMetaAttribute('email_to_update') === null) {
            return false;
        }

        return Carbon::parse(
            $this->getMetaAttribute('email_to_update_stored_at')
        )->gte(now()->subDay());
    }

    public function sendEmailChangeConfirmationMail(string $email) : void
    {
        $email = strtolower($email);

        Mail::to($email)->send(new ConfirmEmailChange($email, $this->name));

        $this->setMetaAttribute('email_to_update', $email);
        $this->setMetaAttribute('email_to_update_stored_at', now()->toString());
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory()
    {
        return new UserFactory();
    }
}
