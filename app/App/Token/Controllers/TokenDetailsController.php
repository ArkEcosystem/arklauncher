<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\Token\Models\Token;
use Illuminate\Contracts\View\View;
use Support\Http\Controllers\Controller;

final class TokenDetailsController extends Controller
{
    public function __invoke(Token $token): View
    {
        $this->authorize('view', $token);

        $configuration = $token->config;

        return view('app.tokens.details', [
            'token'         => $token,
            'configuration' => $configuration,
        ]);
    }
}
