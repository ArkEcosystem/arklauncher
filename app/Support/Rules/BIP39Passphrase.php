<?php

declare(strict_types=1);

namespace Support\Rules;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39Mnemonic;
use BitWasp\Bitcoin\Mnemonic\Bip39\Wordlist\EnglishWordList;
use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;

final class BIP39Passphrase implements Rule
{
    public function passes($attribute, $value)
    {
        try {
            $bip39 = new Bip39Mnemonic(Bitcoin::getEcAdapter(), new EnglishWordList());
            $bip39->mnemonicToEntropy($value);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function message()
    {
        return trans('validation.messages.bip39_passphrase');
    }
}
