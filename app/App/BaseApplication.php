<?php

declare(strict_types=1);

namespace App;

use Illuminate\Foundation\Application as LaravelApplication;

final class BaseApplication extends LaravelApplication
{
    protected $namespace = 'App\\';

    public function path($path = '')
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'app/App'.($path !== '' ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
