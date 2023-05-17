<?php

declare(strict_types=1);

use Domain\Server\Models\Server;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use TiMacDonald\Log\LogEntry;

it('can handle setting a server status', function () {
    $server = Server::factory()->createForTest();
    $route  = URL::temporarySignedRoute('server.deployment.status', now()->addMinutes(1), $server);

    expect($server->hasEverHadStatus('storing_config'))->toBeFalse();

    $response = $this
            ->actingAs($server->token->user)
            ->post($route, ['status' => 'storing_config']);

    expect($server->hasEverHadStatus('storing_config'))->toBeTrue();
    expect($response->status())->toBe(204);
});

it('does not set an unknown status', function () {
    $server = Server::factory()->createForTest();
    $route  = URL::temporarySignedRoute('server.deployment.status', now()->addMinutes(1), $server);

    expect($server->hasEverHadStatus('storing_config'))->toBeFalse();

    $response = $this
            ->actingAs($server->token->user)
            ->post($route, ['status' => 'some_status_we_do_not_have_listed']);

    expect($server->hasEverHadStatus('some_status_we_do_not_have_listed'))->toBeFalse();
    expect($response->status())->toBe(204);
});

it('stores the core version when version is passed as a query parameter', function () {
    $server = Server::factory()->createForTest();
    $route  = URL::temporarySignedRoute('server.deployment.status', now()->addMinutes(1), $server);

    $response = $this
            ->actingAs($server->token->user)
            ->post($route, ['version' => '123']);

    expect($server->fresh()->core_version)->toBe('123');
    expect($response->status())->toBe(204);
});

it('does not store the version if version is empty value', function () {
    $server = Server::factory()->createForTest();
    $route  = URL::temporarySignedRoute('server.deployment.status', now()->addMinutes(1), $server);

    $response = $this
            ->actingAs($server->token->user)
            ->post($route, ['version' => '']);

    expect($server->fresh()->core_version)->toBeNull();
    expect($response->status())->toBe(204);
});

it('logs unhandled failed statuses', function () {
    $server = Server::factory()->createForTest();
    $route  = URL::temporarySignedRoute('server.deployment.status', now()->addMinutes(1), $server);

    expect($server->hasEverHadStatus('failed_some_nonexisting_status'))->toBeFalse();
    expect($server->isFailed())->toBeFalse();

    $response = $this
            ->actingAs($server->token->user)
            ->post($route, ['status' => 'failed_some_nonexisting_status']);

    expect($server->isFailed())->toBeTrue();
    expect($response->status())->toBe(204);

    Log::assertLogged(
        fn (LogEntry $log) => $log->level === 'warning'
        && $log->message === 'Missing case for failed deployment status: "failed_some_nonexisting_status"'
    );
});
