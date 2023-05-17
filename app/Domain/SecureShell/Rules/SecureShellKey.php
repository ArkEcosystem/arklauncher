<?php

declare(strict_types=1);

namespace Domain\SecureShell\Rules;

use Illuminate\Contracts\Validation\Rule;

final class SecureShellKey implements Rule
{
    public function passes($attribute, $value)
    {
        $keyParts = explode(' ', $value, 3);

        if (count($keyParts) < 2) {
            return false;
        }

        $algorithm = $keyParts[0];
        $key       = $keyParts[1];

        if (! in_array($algorithm, ['ssh-rsa', 'ssh-dss', 'ssh-ed25519'], true)) {
            return false;
        }

        $keyBase64Decoded = base64_decode($key, true);

        if ($keyBase64Decoded === false) {
            return false;
        }

        $check = (string) base64_decode(substr($key, 0, 16), true);
        $check = preg_replace("/[^\w\-]/", '', $check);

        return $check === $algorithm
            || $check === 'ssh-ed25';
    }

    public function message()
    {
        return trans('validation.messages.secure_shell_key');
    }
}
