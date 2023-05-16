<?php

declare(strict_types=1);

use Carbon\Carbon;
use Domain\Collaborator\Models\Invitation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('an invitation belongs to a token and an user', function () {
    $user  = $this->user();
    $token = $this->token();

    $invitation = Invitation::factory()->create([
            'token_id' => $token->id,
            'user_id'  => $user->id,
        ]);

    expect($invitation->token())->toBeInstanceOf(BelongsTo::class);
    expect($invitation->user())->toBeInstanceOf(BelongsTo::class);

    expect($token->id)->toBe($invitation->token_id);
    expect($user->id)->toBe($invitation->user_id);
});

it('can determine if the invitation is expired', function () {
    $invitation             = new Invitation();
    $invitation->created_at = Carbon::now()->subWeeks(2);

    expect($invitation->isExpired())->toBeTrue();

    $invitation->created_at = Carbon::now()->addWeeks(2);

    expect($invitation->isExpired())->toBeFalse();
});

it('can find an invitation by uuid', function () {
    $user  = $this->user();
    $token = $this->token();

    $invitation = Invitation::factory()->create([
            'token_id' => $token->id,
            'user_id'  => $user->id,
        ]);

    $this->assertDatabaseHas('invitations', ['uuid' => $invitation->uuid]);

    expect(Invitation::findByUuid($invitation->uuid))->not()->toBeNull();
});
