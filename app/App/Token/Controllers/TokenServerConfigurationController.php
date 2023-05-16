<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\Token\Enums\TokenOnboardingStatusEnum;
use Domain\Token\Models\Token;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Support\Http\Controllers\Controller;

final class TokenServerConfigurationController extends Controller
{
    public function index(Token $token): View|RedirectResponse
    {
        $this->authorize('view', $token);

        if (! $token->canBeEdited()) {
            return redirect()->route('tokens.servers.index', $token);
        }

        if (! $token->onboarding()->available(TokenOnboardingStatusEnum::SERVER_CONFIGURATION)) {
            return redirect()->route('tokens.show', $token);
        }

        return view('app.tokens.server-configuration', [
            'token' => $token,
        ]);
    }
}
