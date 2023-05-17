<?php

declare(strict_types=1);

use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Domain\Token\Rules\UniqueTokenExtraAttribute;

it('can pass validation', function () {
    $subject = new UniqueTokenExtraAttribute($this->token());

    expect($subject->passes('non_existing_key', 'foo'))->toBeTrue();
});

it('can fail validation', function () {
    $token = Token::factory()->createForTest();

    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $serverProvider->setMetaAttribute('foo', 'bar');

    $subject = new UniqueTokenExtraAttribute($serverProvider->token);

    expect($subject->passes('foo', 'bar'))->toBeFalse();
});

it('shows the correct validation message', function () {
    $subject = new UniqueTokenExtraAttribute($this->token());

    expect($subject->message())->toBe(trans('validation.messages.unique_token_extra_attribute'));
});
