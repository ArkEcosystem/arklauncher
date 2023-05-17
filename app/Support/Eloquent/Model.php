<?php

declare(strict_types=1);

namespace Support\Eloquent;

use ARKEcosystem\Foundation\Fortify\Models\Concerns\HasLocalizedTimestamps;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;

abstract class Model extends Eloquent
{
    use HasFactory;
    use HasLocalizedTimestamps;

    /**
     * Create a new factory instance for the model.
     */
    final protected static function newFactory(): Factory
    {
        $namespace    = 'Database\\Factories\\';
        $modelName    = Str::afterLast(static::class, '\\');
        $factoryClass = $namespace.$modelName.'Factory';

        return app($factoryClass);
    }
}
