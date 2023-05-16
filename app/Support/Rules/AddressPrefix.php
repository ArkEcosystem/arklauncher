<?php

declare(strict_types=1);

namespace Support\Rules;

use Illuminate\Contracts\Validation\Rule;
use Support\AddressPrefixes;

final class AddressPrefix implements Rule
{
    public function passes($attribute, $value)
    {
        return AddressPrefixes::valid($value);
    }

    public function message()
    {
        return trans('validation.messages.address_prefix');
    }
}
