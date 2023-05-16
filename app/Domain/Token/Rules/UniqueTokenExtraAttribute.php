<?php

declare(strict_types=1);

namespace Domain\Token\Rules;

use Domain\Token\Models\Token;
use Illuminate\Contracts\Validation\Rule;

final class UniqueTokenExtraAttribute implements Rule
{
    public function __construct(private Token $token)
    {
    }

    public function passes($attribute, $value)
    {
        foreach ($this->token->serverProviders as $provider) {
            if ($provider->getMetaAttribute($attribute) === null || $provider->getMetaAttribute($attribute) === '') {
                continue;
            }

            if ($provider->getMetaAttribute($attribute) === $value) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return trans('validation.messages.unique_token_extra_attribute');
    }
}
