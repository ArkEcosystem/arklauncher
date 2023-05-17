<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('redirects the user if it is authenticated', function () {
    Route::middleware('guest')->get('/test', function () {
        return [];
    });

    $this->actingAs($this->user());

    $this->get('/test')->assertRedirect('/app/tokens');
});

it('fulfill the request if the user is not authenticated', function () {
    Route::middleware('guest')->get('/test', function () {
        return [];
    });

    $this->get('/test')->assertOk();
});
