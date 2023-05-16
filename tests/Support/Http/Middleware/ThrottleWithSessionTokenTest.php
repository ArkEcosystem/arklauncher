<?php

declare(strict_types=1);

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

it('throttles user when too many requests are made', function () {
    Route::middleware([StartSession::class, 'throttleWithSessionToken:1,1'])->get('/test', function () {
        return [];
    });

    $this->withSession(['app:auth:id', 1])->get('/test')->assertSuccessful();
    $this->withSession(['app:auth:id', 1])->get('/test')->assertStatus(429);
});
