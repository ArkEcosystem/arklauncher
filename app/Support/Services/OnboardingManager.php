<?php

declare(strict_types=1);

namespace Support\Services;

use Carbon\Carbon;
use Domain\Token\Enums\TokenOnboardingStatusEnum;
use Domain\Token\Models\Token;

final class OnboardingManager
{
    private array $requiredSteps = [
        TokenOnboardingStatusEnum::CONFIGURATION,
        TokenOnboardingStatusEnum::SERVER_PROVIDERS,
        TokenOnboardingStatusEnum::SERVER_CONFIGURATION,
        TokenOnboardingStatusEnum::SECURE_SHELL_KEYS,
    ];

    public function __construct(private Token $token)
    {
    }

    public function isFinished(): bool
    {
        return ! is_null($this->token->onboarded_at);
    }

    public function fulfilled(): bool
    {
        foreach ($this->requiredSteps as $step) {
            if (! $this->completed($step)) {
                return false;
            }
        }

        return true;
    }

    public function completeConfiguration(): void
    {
        $this->token->setMetaAttribute('onboarding.configuration_completed_at', Carbon::now());
    }

    public function completed(string $name): bool
    {
        if ($name === TokenOnboardingStatusEnum::CONFIGURATION) {
            return $this->token->getMetaAttribute('onboarding.configuration_completed_at') !== null;
        }

        if ($name === TokenOnboardingStatusEnum::SERVER_PROVIDERS) {
            return $this->completed(TokenOnboardingStatusEnum::CONFIGURATION)
                && $this->token->serverProviders()->exists();
        }

        if ($name === TokenOnboardingStatusEnum::SERVER_CONFIGURATION) {
            return $this->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS)
                && $this->token->hasServerConfiguration();
        }

        if ($name === TokenOnboardingStatusEnum::SECURE_SHELL_KEYS) {
            return $this->completed(TokenOnboardingStatusEnum::SERVER_CONFIGURATION)
                && $this->token->secureShellKeys()->exists();
        }

        if ($name === TokenOnboardingStatusEnum::COLLABORATORS) {
            return $this->completed(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS)
                && $this->token->invitations()->exists();
        }

        return $this->token->servers()->exists();
    }

    public function pending(string $name): bool
    {
        return ! $this->completed($name);
    }

    public function available(string $name): bool
    {
        if ($this->completed(TokenOnboardingStatusEnum::SERVERS)) {
            return true;
        }

        if ($name === TokenOnboardingStatusEnum::CONFIGURATION) {
            return true;
        }

        if ($name === TokenOnboardingStatusEnum::SERVER_PROVIDERS) {
            return $this->completed(TokenOnboardingStatusEnum::CONFIGURATION);
        }

        if ($name === TokenOnboardingStatusEnum::SERVER_CONFIGURATION) {
            return $this->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS);
        }

        if ($name === TokenOnboardingStatusEnum::SECURE_SHELL_KEYS) {
            return $this->completed(TokenOnboardingStatusEnum::SERVER_CONFIGURATION);
        }

        return $this->completed(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS);
    }

    public function isStep(string $title): bool
    {
        return in_array($title, TokenOnboardingStatusEnum::all(), true);
    }
}
