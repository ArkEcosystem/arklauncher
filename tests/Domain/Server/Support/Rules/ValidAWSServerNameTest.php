<?php

declare(strict_types=1);

use Domain\Server\Support\Rules\ValidAWSServerName;

// @TODO define real rules for this provider
it('accepts any string as server name', function () {
    $rule = new ValidAWSServerName();

    expect($rule->passes('name', 'wharever'))->toBeTrue();
});

it('has a validation error message', function () {
    $rule = new ValidAWSServerName();

    expect($rule->message())->toBe(trans('validation.messages.valid_aws_server_name'));
});
