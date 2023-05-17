<?php

declare(strict_types=1);

namespace Domain\Server\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

final class ValidVultrServerName implements Rule
{
    /**
     * @see https://developers.linode.com/api/v4/linode-instances-linode-id/#put
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // @TODO The vultr docs doesnt mention any validation rule, we need to do
        // manual tests
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // @TODO If we found some restricitions we should define this message
        return trans('validation.messages.valid_vultr_server_name');
    }
}
