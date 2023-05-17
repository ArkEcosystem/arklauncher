<?php

declare(strict_types=1);

use Domain\Server\Support\Rules\ValidHetznerServerName;

it('accepts a valid RFC 1123 string', function () {
    $rule = new ValidHetznerServerName();

    expect($rule->passes('name', 'server-name.my-server.com'))->toBeTrue();
});

it('accepts a string with dashes', function () {
    $rule = new ValidHetznerServerName();

    expect($rule->passes('name', 'my-server-name'))->toBeTrue();
});

it('accepts a string uppercase letter', function () {
    $rule = new ValidHetznerServerName();

    expect($rule->passes('name', 'MyServerName'))->toBeTrue();
});

it('accepts a string with numbers', function () {
    $rule = new ValidHetznerServerName();

    expect($rule->passes('name', 'MyServerName-001'))->toBeTrue();

    expect($rule->passes('name', '001'))->toBeTrue();
});

it('doesnt accepts an string with spaces', function () {
    $rule = new ValidHetznerServerName();

    expect($rule->passes('name', 'server-name my-server.com'))->toBeFalse();
});

it('doesnt accepts an string with low dash', function () {
    $rule = new ValidHetznerServerName();

    expect($rule->passes('name', 'server-name_my-server.com'))->toBeFalse();
});

it('doesnt accepts an string with weird chars', function () {
    $rule = new ValidHetznerServerName();

    expect($rule->passes('name', 'server-name$'))->toBeFalse();
    expect($rule->passes('name', 'server-name%'))->toBeFalse();
    expect($rule->passes('name', 'server-name@'))->toBeFalse();
    expect($rule->passes('name', 'server-name#'))->toBeFalse();
});

it('has a validation error message', function () {
    $rule = new ValidHetznerServerName();

    expect($rule->message())->toBe(trans('validation.messages.valid_hetzner_server_name'));
});
