<?php

declare(strict_types=1);

namespace Domain\Token\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

final class ReservedTokenName implements Rule
{
    public function __construct(
        private array $blacklist = []
    ) {
        $this->blacklist = (array) trans('tokens_blacklist');
    }

    public function passes($attribute, $value)
    {
        return ! collect($this->blacklist)
            ->containsStrict($this->normalizeValue($value));
    }

    public function message()
    {
        return trans('validation.custom.blacklisted');
    }

    private function normalizeValue(string $value): string
    {
        return (string) Str::of($value)->ascii()->lower();
    }
}
