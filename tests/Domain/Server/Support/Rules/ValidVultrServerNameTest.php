<?php

declare(strict_types=1);

use Domain\Server\Support\Rules\ValidVultrServerName;

// @TODO Confirm with vultr if there are any validation rules
it('accepts any string as server name', function () {
    $rule = new ValidVultrServerName();

    expect($rule->passes('name', 'wharever'))->toBeTrue();
});

it('has a validation error message', function () {
    $rule = new ValidVultrServerName();

    expect($rule->message())->toBe(trans('validation.messages.valid_vultr_server_name'));
});
