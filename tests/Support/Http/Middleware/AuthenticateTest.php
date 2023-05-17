<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('redirects the user if it is not authenticated', function () {
    Route::middleware('auth')->get('/test', function () {
        return [];
    });

    $this->get('/test')->assertRedirect('login');
});

it('returns a 401 if the user is not authenticated', function () {
    Route::middleware('auth')->get('/test', function () {
        return [];
    });

    $this->getJson('/test')->assertUnauthorized();
});

it('fulfill the request if the user is authenticated', function () {
    Route::middleware('auth')->get('/test', function () {
        return [];
    });

    $this->actingAs($this->user());

    $this->get('/test')->assertOk();
});
