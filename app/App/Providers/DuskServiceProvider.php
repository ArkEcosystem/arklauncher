<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Laravel\Dusk\DuskServiceProvider as BaseDuskServiceProvider;

final class DuskServiceProvider extends BaseDuskServiceProvider
{
    public function boot(): void
    {
        if (App::environment('dusk') !== true) {
            return;
        }

        parent::boot();
    }
}
