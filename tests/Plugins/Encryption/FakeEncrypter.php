<?php

declare(strict_types=1);

namespace Tests\Plugins\Encryption;

use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Contracts\Encryption\EncryptException;

final class FakeEncrypter implements EncrypterContract
{
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @param bool  $serialize
     *
     * @throws \Illuminate\Contracts\Encryption\EncryptException
     *
     * @return string
     */
    public function encrypt($value, $serialize = true)
    {
        $value = $serialize ? serialize($value) : $value;

        $json = json_encode($value, JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Decrypt the given value.
     *
     * @param string $payload
     * @param bool   $unserialize
     *
     * @throws \Illuminate\Contracts\Encryption\DecryptException
     *
     * @return mixed
     */
    public function decrypt($payload, $unserialize = true)
    {
        $decrypted = json_decode(base64_decode($payload, true), true);

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Encrypt a string without serialization.
     *
     * @param string $value
     *
     * @throws \Illuminate\Contracts\Encryption\EncryptException
     *
     * @return string
     */
    public function encryptString($value)
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt the given string without unserialization.
     *
     * @param string $payload
     *
     * @throws \Illuminate\Contracts\Encryption\DecryptException
     *
     * @return string
     */
    public function decryptString($payload)
    {
        return $this->decrypt($payload, false);
    }

    /**
     * Get the encryption key.
     *
     * @return string
     */
    public function getKey()
    {
        return 'wharever';
    }
}
