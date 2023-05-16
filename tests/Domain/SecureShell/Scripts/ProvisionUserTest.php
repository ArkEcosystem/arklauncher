<?php

declare(strict_types=1);

use Domain\SecureShell\Scripts\ProvisionUser;
use Domain\Server\Models\Server;

it('generates_a_script', function () {
    $server = Server::factory()->createForTest();

    $script = new ProvisionUser($server);

    expect($script->name())->toBeString();
    expect($script->script())->toBeString();
    expect($script->user())->toBeString();
    expect($script->timeout())->toBeNumeric();
});
