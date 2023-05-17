<?php

declare(strict_types=1);

namespace Domain\Server\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

final class ValidLinodeServerName implements Rule
{
    /**
     * Must start with an alpha character.
     * May only consist of alphanumeric characters, dashes (-), underscores (_) or periods (.).
     * Cannot have two dashes (--), underscores (__) or periods (..) in a row.
     *
     * @see https://developers.linode.com/api/v4/linode-instances-linode-id/#put
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $re = '/^[a-zA-Z]+[a-zA-Z0-9]*([._-][a-zA-Z0-9]+)*$/';

        return (bool) preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.messages.valid_linode_server_name');
    }
}
