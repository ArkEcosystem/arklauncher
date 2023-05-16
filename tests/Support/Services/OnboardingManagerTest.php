<?php

declare(strict_types=1);

use Domain\Collaborator\Models\Invitation;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Enums\TokenAttributeEnum;
use Domain\Token\Enums\TokenOnboardingStatusEnum;
use Domain\Token\Models\Token;
use Support\Services\OnboardingManager;

it('determines if the onboarding is finished', function () {
    $manager = new OnboardingManager($token = $this->token());

    expect($manager->isFinished())->toBeFalse();

    $token->update(['onboarded_at' => now()]);

    expect($manager->isFinished())->toBeTrue();
});

it('determines if the onboarding is fulfilled', function () {
    $token = Token::factory()->withOnboardingSecureShellKey()->create();

    $manager = new OnboardingManager($token);

    expect($manager->fulfilled())->toBeTrue();
});

it('determines if the onboarding is not fulfilled', function () {
    $token = Token::factory()->create();

    $manager = new OnboardingManager($token);

    expect($manager->fulfilled())->toBeFalse();
});

it('determines if the configuration step is completed', function () {
    $manager = new OnboardingManager($this->token());

    expect($manager->completed(TokenOnboardingStatusEnum::CONFIGURATION))->toBeFalse();

    $manager->completeConfiguration();

    expect($manager->completed(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
});

it('determines if the configuration step is pending', function () {
    $manager = new OnboardingManager($this->token());

    expect($manager->pending(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();

    $manager->completeConfiguration();

    expect($manager->pending(TokenOnboardingStatusEnum::CONFIGURATION))->toBeFalse();
});

it('determines if the server providers step is completed', function () {
    $token = Token::factory()->withOnboardingConfigurationCompleted()->create();

    $manager = new OnboardingManager($token);

    expect($manager->completed(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeFalse();

    ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
});

it('determines if the server server_config step is completed', function () {
    $token = Token::factory()->withOnboardingServerProvider()->create();

    $manager = new OnboardingManager($token);

    expect($manager->completed(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeFalse();

    $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
        'server_provider_id' => 1,
    ]);

    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeTrue();
});

it('determines if the server secure_shell_keys step is completed', function () {
    $token = Token::factory()->withOnboardingServerConfiguration()->create();

    $manager = new OnboardingManager($token);

    expect($manager->completed(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeFalse();

    $token->secureShellKeys()->sync(SecureShellKey::factory()->create()->id);

    expect($manager->completed(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeTrue();
});

it('determines if the server collaborators step is completed', function () {
    $token = Token::factory()->withOnboardingSecureShellKey()->create();

    $manager = new OnboardingManager($token);

    expect($manager->completed(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::COLLABORATORS))->toBeFalse();

    $token->invitations()->create(Invitation::factory()->make()->toArray());

    expect($manager->completed(TokenOnboardingStatusEnum::COLLABORATORS))->toBeTrue();
});

it('another step is not completed', function () {
    $token = Token::factory()->withOnboardingSecureShellKey()->create();

    $manager = new OnboardingManager($token);

    expect($manager->completed(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeTrue();
    expect($manager->completed(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeTrue();

    $token->invitations()->create(Invitation::factory()->make()->toArray());

    expect($manager->completed('other'))->toBeFalse();
});

it('should be valid step', function () {
    $manager = new OnboardingManager($this->token());

    $currentStep = TokenOnboardingStatusEnum::SERVERS;

    expect($manager->isStep($currentStep))->toBeTrue();
});

it('determines if the configuration step is available', function () {
    $manager = new OnboardingManager($this->token());

    expect($manager->available(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
});

it('determines if the server server providers step is available', function () {
    $token = Token::factory()->create();

    $manager = new OnboardingManager($token);

    expect($manager->available(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeFalse();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeFalse();

    $manager->completeConfiguration();

    expect($manager->available(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeFalse();
});

it('determines if the server server_config step is available', function () {
    $token = Token::factory()->withOnboardingConfigurationCompleted()->create();

    $manager = new OnboardingManager($token);

    expect($manager->available(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeFalse();
    expect($manager->available(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeFalse();

    ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    expect($manager->available(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeFalse();
});

it('determines if the server secure_shell_keys step is available', function () {
    $token = Token::factory()->withOnboardingServerProvider()->create();

    $manager = new OnboardingManager($token);

    expect($manager->available(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeFalse();
    expect($manager->available(TokenOnboardingStatusEnum::COLLABORATORS))->toBeFalse();

    $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
        'server_provider_id' => 1,
    ]);

    expect($manager->available(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::COLLABORATORS))->toBeFalse();
});

it('determines if the server collaborators and servers step is available', function () {
    $token = Token::factory()->withOnboardingServerConfiguration()->create();

    $manager = new OnboardingManager($token);

    expect($manager->available(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::COLLABORATORS))->toBeFalse();
    expect($manager->available(TokenOnboardingStatusEnum::SERVERS))->toBeFalse();

    $token->secureShellKeys()->sync(SecureShellKey::factory()->create()->id);
    Server::factory()->ownedBy($token->networks()->first())->createForTest();

    expect($manager->available(TokenOnboardingStatusEnum::COLLABORATORS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVERS))->toBeTrue();
});

it('should be able to access ssh keys after deployment', function () {
    $token = Token::factory()->withOnboardingConfigurationCompleted()->create();

    $manager = new OnboardingManager($token);

    $this->actingAs($token->user)
        ->get(route('tokens.ssh-keys', $token))
        ->assertRedirect(route('tokens.show', $token));

    expect($manager->available(TokenOnboardingStatusEnum::CONFIGURATION))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVER_CONFIGURATION))->toBeFalse();
    expect($manager->available(TokenOnboardingStatusEnum::SECURE_SHELL_KEYS))->toBeFalse();
    expect($manager->available(TokenOnboardingStatusEnum::COLLABORATORS))->toBeFalse();
    expect($manager->available(TokenOnboardingStatusEnum::SERVERS))->toBeFalse();

    $token->secureShellKeys()->sync(SecureShellKey::factory()->create()->id);
    Server::factory()->ownedBy($token->networks()->first())->createForTest();

    $this->actingAs($token->user)
        ->get(route('tokens.ssh-keys', $token))
        ->assertViewHas('token', $token);

    expect($manager->available(TokenOnboardingStatusEnum::COLLABORATORS))->toBeTrue();
    expect($manager->available(TokenOnboardingStatusEnum::SERVERS))->toBeTrue();
});
