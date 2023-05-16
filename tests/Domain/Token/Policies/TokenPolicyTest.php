<?php

declare(strict_types=1);

use Domain\Token\Models\Token;
use Domain\Token\Policies\TokenPolicy;

it('can determine if the user passes view any', function () {
    $token = Token::factory()->createForTest();

    expect((new TokenPolicy())->viewAny($token->user, $token))->toBeTrue();
    expect((new TokenPolicy())->viewAny($this->user(), $token))->toBeFalse();
});

it('can determine if the user passes view', function () {
    $token = Token::factory()->createForTest();

    expect((new TokenPolicy())->view($token->user, $token))->toBeTrue();
    expect((new TokenPolicy())->view($this->user(), $token))->toBeFalse();
});

it('can determine if the user passes update', function () {
    $token = Token::factory()->createForTest();

    expect((new TokenPolicy())->update($token->user, $token))->toBeTrue();
    expect((new TokenPolicy())->update($this->user(), $token))->toBeFalse();
});

it('can determine if the user passes delete', function () {
    $token = Token::factory()->createForTest();

    expect((new TokenPolicy())->delete($token->user, $token))->toBeTrue();
    expect((new TokenPolicy())->delete($this->user(), $token))->toBeFalse();
});
