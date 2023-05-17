<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\Token\Enums\TokenOnboardingStatusEnum;
use Domain\Token\Models\Token;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Support\Http\Controllers\Controller;

final class TokenCollaboratorController extends Controller
{
    public function __invoke(Token $token): View|RedirectResponse
    {
        $this->authorize('view', $token);
        $this->authorizeAny(['createCollaborator', 'deleteCollaborator'], $token);

        if ($token->canBeEdited() && ! $token->onboarding()->available(TokenOnboardingStatusEnum::COLLABORATORS)) {
            return redirect()->route('tokens.show', $token);
        }

        return view('app.tokens.collaborators', ['token' => $token]);
    }
}
