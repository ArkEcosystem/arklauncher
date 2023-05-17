<?php

declare(strict_types=1);

namespace App\Server\Components;

use Domain\Token\Enums\TokenOnboardingStatusEnum;
use Domain\Token\Models\Token;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class RedirectOnServerProviderCompletion extends Component
{
    public Token $token;

    public function mount(Token $token): void
    {
        $this->token = $token;
    }

    public function render(): View
    {
        if ($this->token->onboarding()->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS) && $this->token->serverProviders()->whereNull('provider_key_id')->count() === 0) {
            redirect()->to(route('tokens.show', $this->token));
        }

        return view('livewire.redirect-on-server-provider-completion', ['token' => $this->token]);
    }
}
