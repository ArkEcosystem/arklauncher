<?php

declare(strict_types=1);

use App\Server\Jobs\ProvisionUser;
use App\Server\Jobs\ServerProvisioner;
use Carbon\Carbon;
use Domain\Server\Models\Server;

it('deletes the job if the server is provisioned', function () {
    $server = Server::factory()->createForTest();

    $server->touch('provisioned_at');
    $server->setStatus('online');

    (new ServerProvisioner($server))->handle();

    $this->addToAssertionCount(1);
});

it('sets server status to failed if it is older than 30 minutes', function () {
    $server = Server::factory()->create([
        'created_at' => Carbon::now()->subMinutes(45),
        'ip_address' => null,
    ]);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
    expect(Server::currentStatus('failed')->count())->toBe(0);

    (new ServerProvisioner($server))->handle();

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
    expect(Server::currentStatus('failed')->count())->toBe(1);
});

it('releases the job back onto the queue if the server is provisioning', function () {
    $this->doesntExpectJobs([ProvisionUser::class]);

    $server = Server::factory()->createForTest();

    $server->setStatus('updating_system');

    (new ServerProvisioner($server))->handle();

    $this->addToAssertionCount(1);
});

it('starts to provision the server if it is ready for provisioning', function () {
    $this->expectsJobs([ProvisionUser::class]);

    $server = Server::factory()->createForTest();

    expect($server->provisioning_job_dispatched_at)->toBeNull();

    (new ServerProvisioner($server))->handle();

    $this->addToAssertionCount(1);

    expect($server->fresh()->provisioning_job_dispatched_at)->not->toBeNull();
});

it('releases the job back onto the queue if all conditions are false', function () {
    $this->doesntExpectJobs([ProvisionUser::class]);

    $server = Server::factory()->createForTest(['ip_address' => null]);

    (new ServerProvisioner($server))->handle();

    $this->addToAssertionCount(1);
});

it('retries the job for 35 mins', function () {
    $server = Server::factory()->createForTest();

    Carbon::setTestNow('2020-01-01 10:00:00');

    $timeout = (new ServerProvisioner($server))->retryUntil();

    expect($timeout->toDateTimeString())->toBe('2020-01-01 10:35:00');

    Carbon::setTestNow();
});
