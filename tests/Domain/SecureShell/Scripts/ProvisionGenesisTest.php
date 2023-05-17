<?php

declare(strict_types=1);

use Domain\SecureShell\Scripts\ProvisionGenesis;
use Domain\Server\Models\Server;
use Domain\Token\Models\Token;

beforeEach(function () {
    $this->token = Token::factory()->withNetwork(1)->withServers(2)->createForTest();
});

it('generates a script', function () {
    $server = Server::factory()->createForTest();

    $script = new ProvisionGenesis($server);

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
