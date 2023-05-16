<?php

declare(strict_types=1);

use Support\Rules\AddressPrefix;

it('can pass validation', function () {
    $subject = new AddressPrefix();

    expect($subject->passes('prefix', 'A'))->toBeTrue();
});

it('can fail validation', function () {
    $subject = new AddressPrefix();

    expect($subject->passes('prefix', 'invalid'))->toBeFalse();
});

it('shows the correct validation message', function () {
    $subject = new AddressPrefix();

    expect($subject->message())->toBe(trans('validation.messages.address_prefix'));
});
