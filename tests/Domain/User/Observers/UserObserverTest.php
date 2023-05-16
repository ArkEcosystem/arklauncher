<?php

declare(strict_types=1);

use Domain\Collaborator\Models\Invitation;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;

it('should delete owned tokens on delete', function () {
    $token = Token::factory()->withStatus(TokenStatusEnum::FINISHED)->createForTest();
    $user  = $token->user;

    expect($user->ownedTokens()->withTrashed()->count())->toBe(1);

    $user->delete();

    expect($user->ownedTokens()->withTrashed()->count())->toBe(0);
});

it('should delete ssh keys on delete', function () {
    $user = $this->user();

    SecureShellKey::factory()->ownedBy($user)->createForTest();

    expect($user->secureShellKeys()->count())->toBe(1);

    $user->delete();

    expect($user->secureShellKeys()->count())->toBe(0);
});

it('should delete invitations on delete', function () {
    $user       = $this->user();
    $invitation = Invitation::factory()->create([
        'user_id' => $user->id,
    ]);

    $user->delete();

    expect($invitation->fresh())->toBeNull();
});
