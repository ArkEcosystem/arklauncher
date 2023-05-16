<?php

declare(strict_types=1);

use Domain\Server\Support\Rules\ValidDigitalOceanServerName;

it('accepts any string withouth spaces', function () {
    $rule = new ValidDigitalOceanServerName();

    expect($rule->passes('name', 'Server:myserver@ark.io'))->toBeTrue();
});

it('doesnt accepts an string with spaces', function () {
    $rule = new ValidDigitalOceanServerName();

    expect($rule->passes('name', 'Server myserver@ark.io'))->toBeFalse();
});

it('has a validation error message', function () {
    $rule = new ValidDigitalOceanServerName();

    expect($rule->message())->toBe(trans('validation.messages.valid_digital_ocean_server_name'));
});
