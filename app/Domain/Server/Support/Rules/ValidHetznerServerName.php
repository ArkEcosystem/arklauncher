<?php

declare(strict_types=1);

namespace Domain\Server\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

final class ValidHetznerServerName implements Rule
{
    /**
     * Server names must be unique per Project and valid hostnames as per RFC 1123
     * (i.e. may only contain letters, digits, periods, and dashes).
     *
     * @see https://docs.hetzner.cloud/#servers-update-a-server
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // RFC 1123
        $re = '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/m';

        return (bool) preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.messages.valid_hetzner_server_name');
    }
}
