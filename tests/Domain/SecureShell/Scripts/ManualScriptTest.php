<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use Domain\SecureShell\Scripts\ManualScript;
use Domain\Server\Models\Server;

it('generates a script', function () {
    $server = Server::factory()->genesis()->createForTest();

    $script = new ManualScript($server->token, NetworkTypeEnum::MAINNET);

    expect($script->script())->toContain('--token="'.$server->token->normalized_token.'"');
    expect($script->script())->not()->toContain('--token='.$server->token->normalized_token.'');
    expect($script->script())->not()->toContain('--token="'.$server->token->config['token'].'"');
    expect($script->script())->not()->toContain('--token='.$server->token->config['token'].'');

    expect($script->script())->toContain('mv .env app.json peers.json');
    expect($script->script())->toContain('mv crypto/*');
    expect($script->script())->toContain('success "Configured ARK Core!"');

    expect($script->name())->toBeString();
    expect($script->script())->toBeString();
    expect($script->user())->toBeString();
    expect($script->user())->toBe($server->token->normalized_token);
    expect($script->timeout())->toBeNumeric();
});
