<?php

declare(strict_types=1);

use Domain\SecureShell\Scripts\Concerns\LocatesScript;
use Domain\Token\Models\Token;

// use LocatesScript;

it('gets the script path for the token coin with fallback', function () {
    $token = Token::factory()->createForTest();

    expect($this->getScriptPath($token, 'test'))->toBe('scripts.ark.ark.test');
});

it('gets_the_script_path_for_the_token_coin_without_fallback', function () {
    $token = Token::factory()->createForTest(['name' => 'ARK']);

    expect($this->getScriptPath($token, 'provision-explorer'))->toBe('scripts.ark.ark.provision-explorer');
});
