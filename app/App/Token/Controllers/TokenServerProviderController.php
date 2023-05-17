<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\Token\Enums\TokenOnboardingStatusEnum;
use Domain\Token\Models\Token;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Support\Http\Controllers\Controller;

final class TokenServerProviderController extends Controller
{
    public function index(Token $token): View|RedirectResponse
    {
        $this->authorize('view', $token);

        if ($token->canBeEdited() && ! $token->onboarding()->available(TokenOnboardingStatusEnum::SERVER_PROVIDERS)) {
            return redirect()->route('tokens.show', $token);
        }

        return view('app.tokens.server-providers', [
            'token' => $token,
        ]);
    }
}
