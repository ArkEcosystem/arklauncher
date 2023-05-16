<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\Coin\Models\Coin;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Events\TokenCreated;
use Domain\Token\Models\Token;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Support\Http\Controllers\Controller;
use Support\Services\Haiku;

final class TokenOnboardingController extends Controller
{
    public function index(Token $token): View|RedirectResponse
    {
        $this->authorize('view', $token);

        if (! $token->canBeEdited()) {
            return redirect()->route('tokens.show', $token);
        }

        return view('app.tokens.onboard', ['token' => $token]);
    }

    public function create(): View
    {
        // fetch existing draft token for user or create new, then redirect to onboarding
        $token = Token::where('user_id', Auth::id())->currentStatus(TokenStatusEnum::PENDING)->first();

        if ($token === null) {
            // Create draft token that we use
            $coin = Coin::where([
                'name'   => 'ARK',
                'symbol' => 'ARK',
            ])->firstOrFail();

            $token = Token::create([
                'user_id'  => Auth::id(),
                'coin_id'  => $coin->id,
                'name'     => Haiku::withToken(),
                'config'   => null,
            ]);

            $token->save();

            TokenCreated::dispatch($token);
        }

        return view('app.tokens.onboard', ['token' => $token]);
    }

    public function update(Token $token): RedirectResponse
    {
        abort_unless($token->onboarding()->fulfilled(), 403);

        $token->update(['onboarded_at' => now()]);

        return redirect()->route('tokens', $token);
    }
}
