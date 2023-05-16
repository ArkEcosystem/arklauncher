<?php

declare(strict_types=1);

namespace Domain\Server\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

final class ValidDigitalOceanServerName implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // NO spaces
        $re = '/^\S*$/m';

        return (bool) preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.messages.valid_digital_ocean_server_name');
    }
}
