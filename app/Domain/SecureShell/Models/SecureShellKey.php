<?php

declare(strict_types=1);

namespace Domain\SecureShell\Models;

use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Support\Eloquent\Model;

/**
 * @property User $user
 */
final class SecureShellKey extends Model
{
    protected $fillable = ['name', 'public_key', 'fingerprint'];

    public function token(): BelongsToMany
    {
        return $this->belongsToMany(Token::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fingerprint() : string
    {
        // Segments of a public key: algorithm / public key contents / identifier (email)
        $segments = explode(' ', $this->public_key, 3);
        $contents = (string) base64_decode($segments[1], true);

        return implode(':', str_split(md5($contents), 2));
    }

    protected static function booted() : void
    {
        static::saving(function (self $model) : void {
            $model->fill([
                'fingerprint' => $model->fingerprint(),
            ]);
        });
    }
}
