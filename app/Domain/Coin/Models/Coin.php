<?php

declare(strict_types=1);

namespace Domain\Coin\Models;

use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\Eloquent\Model;

final class Coin extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'symbol'];

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(95); // 100 but room for suffix
    }
}
