<?php

declare(strict_types=1);

use Domain\SecureShell\Scripts\ProvisionSeed;
use Domain\Server\Models\Server;

it('generates_a_script', function () {
    $server = Server::factory()->createForTest();

    $script = new ProvisionSeed($server);

    expect($script->script())->toContain('--token="'.$server->token->normalized_token.'"');
    expect($script->script())->not()->toContain('--token='.$server->token->normalized_token.'');
    expect($script->script())->not()->toContain('--token="'.$server->token->config['token'].'"');
    expect($script->script())->not()->toContain('--token='.$server->token->config['token'].'');

    expect($script->name())->toBeString();
    expect($script->script())->toBeString();
    expect($script->user())->toBeString();
    expect($script->user())->toBe($server->token->normalized_token);
    expect($script->timeout())->toBeNumeric();
});
