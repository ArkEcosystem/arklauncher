<?php

declare(strict_types=1);

use Domain\Server\Support\Rules\ValidLinodeServerName;

it('accepts a valid string with dashes, underscores, periods ands digits', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'my-server-0003.00_2020'))->toBeTrue();
});

it('accepts a string with dashes', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'my-server-name'))->toBeTrue();
});

it('accepts a string uppercase letter', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'MyServerName'))->toBeTrue();
});

it('accepts a string with numbers', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'MyServerName-001'))->toBeTrue();
});

it('doesnt accepts a string that start with a number', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', '001-MyServerName'))->toBeFalse();
});

it('doesnt accepts an string with spaces', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'server-name my-server.com'))->toBeFalse();
});

it('doesnt accepts an string with two low dash in arow', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'server-name__my-server.com'))->toBeFalse();
});

it('doesnt accepts an string with two dash in arow', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'server-name--my-server.com'))->toBeFalse();
});

it('doesnt accepts an string with two periods in arow', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'server-name..my-server.com'))->toBeFalse();
});

it('doesnt accepts an string with weird chars', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->passes('name', 'server-name$'))->toBeFalse();
    expect($rule->passes('name', 'server-name%'))->toBeFalse();
    expect($rule->passes('name', 'server-name@'))->toBeFalse();
    expect($rule->passes('name', 'server-name#'))->toBeFalse();
});

it('has a validation error message', function () {
    $rule = new ValidLinodeServerName();

    expect($rule->message())->toBe(trans('validation.messages.valid_linode_server_name'));
});
