<?php

declare(strict_types=1);

use Domain\SecureShell\Models\SecureShellKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

$dummyKey = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCd4GRay6+/91/G8sBD9Kl03csgXrVhsg2rIHVbqUQSUWDwuTk/RqGplv7YPVxKUnEeJYKtsw64EKb0V2AjJ/zViRuvkeXWewNGvlzjsUyXZ6rmCIGQIylkLngDGUX6UYpN9/Osb1RDEowlLk4/HW96paBaJLNB/WQzXrvikJYD8E4NEFwFwrS94tK+5tuEA2OXTIbXRCDONheGFmLtTCKsNAl9K/4LM5bl+J/4868FzRnClbjIvXi4ni+7YAcA9bkH8O4Nmq8RS6QVPOH5nQ4MLH17921HAbjYDvyb2xRR930dZpeO/Gx/DpRL/La55bThDEAmbyeHzeIIcFSw6A4h';

$fingerprint = '3a:c7:67:e6:26:22:16:d9:90:09:1d:b9:59:c0:a5:aa';

it('a secure shell key belongs to an user', function () {
    $key = SecureShellKey::factory()->createForTest();

    expect($key->user())->toBeInstanceOf(BelongsTo::class);
});

it('a secure shell key belongs to many tokens', function () {
    $key = SecureShellKey::factory()->createForTest();

    expect($key->token())->toBeInstanceOf(BelongsToMany::class);
});

it('generates the fingerprint from the public key', function () use ($dummyKey, $fingerprint) {
    $key = new SecureShellKey([
        'name'       => 'dummy',
        'public_key' => $dummyKey,
    ]);

    expect($key->fingerprint())->toBe($fingerprint);
});

it('saves the fingerprint when model is persisted to database', function () use ($dummyKey, $fingerprint) {
    $key = SecureShellKey::factory()->create([
        'name'       => 'dummy',
        'public_key' => $dummyKey,
    ]);

    expect($key->fingerprint)->toBe($fingerprint);
});
