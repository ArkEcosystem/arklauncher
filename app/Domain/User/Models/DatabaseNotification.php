<?php

declare(strict_types=1);

namespace Domain\User\Models;

use ARKEcosystem\Foundation\Fortify\Models\Concerns\HasLocalizedTimestamps;
use Database\Factories\DatabaseNotificationFactory;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

final class DatabaseNotification extends \ARKEcosystem\Foundation\Hermes\Models\DatabaseNotification
{
    use HasFactory;
    use HasLocalizedTimestamps;

    public function getTokenAttribute(): Token
    {
        $notificationId = $this->id;
        $userId         = Auth::id();
        $tokenId        = $this->data['token'];

        return Cache::remember(
            md5("notifications.{$notificationId}.{$userId}.{$tokenId}"),
            300, // 300 seconds
            fn () => Token::withTrashed()->findOrFail($tokenId)
        );
    }

    public function name(): string
    {
        return $this->token->name;
    }

    public function title(): string
    {
        return $this->token->name;
    }

    public function logo(): string
    {
        return $this->token->logo;
    }

    public function route() : ?string
    {
        if ($this->relatable_type === Token::class && $this->relatable !== null) {
            return route('tokens.details', $this->relatable);
        }

        return null;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @phpstan-ignore-next-line
     * @return DatabaseNotificationFactory
     */
    protected static function newFactory() : Factory
    {
        return new DatabaseNotificationFactory();
    }
}
