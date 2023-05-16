<?php

declare(strict_types=1);

namespace Domain\Server\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

final class ValidAWSServerName implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // @TODO: obtain the rules for validate a server name in AED
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // @TODO: create this validation rule
        return trans('validation.messages.valid_aws_server_name');
    }
}
