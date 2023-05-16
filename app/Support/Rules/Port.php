<?php

declare(strict_types=1);

namespace Support\Rules;

use Illuminate\Contracts\Validation\Rule;

final class Port implements Rule
{
    public function passes($attribute, $value)
    {
        return $value > 1023 && $value < 65535;
    }

    public function message()
    {
        return trans('validation.messages.port');
    }
}
