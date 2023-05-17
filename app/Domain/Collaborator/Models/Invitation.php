<?php

declare(strict_types=1);

namespace Domain\Collaborator\Models;

use Carbon\Carbon;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Support\Eloquent\Model;

/**
 * @property Token $token
 * @property User $user
 */
final class Invitation extends Model
{
    use Notifiable;

    protected $fillable = ['token_id', 'uuid', 'user_id', 'email', 'role', 'permissions'];

    protected $casts = ['permissions' => 'json'];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return Carbon::now()->subWeek()->gte($this->created_at);
    }

    public static function findByUuid(string $token): self
    {
        return static::where('uuid', $token)->firstOrFail();
    }
}
