<?php

declare(strict_types=1);

use Support\Rules\Port;

it('can pass the validation', function () {
    $subject = new Port();

    expect($subject->passes('port', 3000))->toBeTrue();
});

it('can fail the validation', function ($port) {
    expect((new Port())->passes('port', $port))->toBeFalse();
})->with([
    1,
    22,
    1023,
    65536,
]);

it('shows the correct valdiation message', function () {
    $subject = new Port($this->user());

    expect($subject->message())->toBe(trans('validation.messages.port'));
});
