<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\Token\Models\Token;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Support\Http\Controllers\Controller;

final class TokenController extends Controller
{
    public function index(): View
    {
        return view('app.tokens.index');
    }

    public function edit(Token $token): RedirectResponse|View
    {
        $this->authorize('view', $token);

        if (! $token->canBeEdited()) {
            return redirect()->route('tokens.show', $token);
        }

        return view('app.tokens.edit', ['token' => $token]);
    }

    public function show(Token $token): RedirectResponse
    {
        $this->authorize('view', $token);

        return redirect()->route('tokens.details', [$token]);
    }
}
