<?php

declare(strict_types=1);

namespace Domain\SecureShell\Scripts\Concerns;

use Domain\Token\Models\Token;

trait LocatesScript
{
    public function getScriptPath(Token $token, string $scriptType): string
    {
        $scriptPrefix    = 'scripts.';
        $scriptTokenPath = strtolower($token->coin->name).'.'.strtolower($token->name);

        if (view()->exists($scriptPrefix.$scriptTokenPath.'.'.$scriptType)) {
            return $scriptPrefix.$scriptTokenPath.'.'.$scriptType;
        }

        return $scriptPrefix.strtolower($token->coin->name).'.'.strtolower($token->coin->symbol).'.'.$scriptType;
    }
}
