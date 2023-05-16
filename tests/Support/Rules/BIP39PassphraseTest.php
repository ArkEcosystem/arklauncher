<?php

declare(strict_types=1);

use Support\Rules\BIP39Passphrase;

it('can pass the validation', function () {
    $subject = new BIP39Passphrase();

    expect($subject->passes('delegate_passphrase', 'until kind false pudding broccoli custom crumble rose inject bronze bundle digital'))->toBeTrue();
});

it('can fail the validation', function () {
    $subject = new BIP39Passphrase();

    expect($subject->passes('delegate_passphrase', 'abandon'))->toBeFalse();
});

it('shows the correct valdiation message', function () {
    $subject = new BIP39Passphrase();

    expect($subject->message())->toBe(trans('validation.messages.bip39_passphrase'));
});
