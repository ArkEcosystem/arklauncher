<?php

declare(strict_types=1);

use App\User\Mail\ConfirmEmailChange;
use Carbon\Carbon;
use Domain\Collaborator\Models\Collaborator;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;
use Domain\User\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->token = Token::factory()->withStatus(TokenStatusEnum::FINISHED)->createForTest();
});

it('an user has many tokens', function () {
    expect($this->user()->tokens())->toBeInstanceOf(BelongsToMany::class);
});

it('an user has many secure shell keys', function () {
    expect($this->user()->secureShellKeys())->toBeInstanceOf(HasMany::class);
});

it('an user has many invitations', function () {
    $this->actingAs($this->user());

    expect($this->user()->invitations())->toBeInstanceOf(HasMany::class);
});

it('can determine if the user has any tokens', function () {
    $token = $this->token;

    $user = $this->user();

    expect($user->hasTokens())->toBeFalse();

    $token->shareWith($user);

    expect($user->fresh()->hasTokens())->toBeTrue();

    $token->stopSharingWith($user);

    expect($user->fresh()->hasTokens())->toBeFalse();
});

it('can determine if the user is on a token', function () {
    $token = $this->token;

    $user = $this->user();

    expect($user->onToken($token))->toBeFalse();

    $token->shareWith($user);

    expect($user->fresh()->onToken($token))->toBeTrue();

    $token->stopSharingWith($user);

    expect($user->fresh()->onToken($token))->toBeFalse();
});

it('can determine if the user owns a token', function () {
    $token       = $this->token;
    $user        = $token->user;
    $anotherUser = $this->user();

    expect($user->ownsToken($token))->toBeTrue();
    expect($anotherUser->ownsToken($token))->toBeFalse();
});

it('can return all tokens owned by the user', function () {
    $user        = $this->user();
    $anotherUser = $this->user();

    $this->token($user, TokenStatusEnum::FINISHED);
    $this->token($user, TokenStatusEnum::FINISHED);

    $this->token($anotherUser);

    expect($user->tokens)->toHaveCount(2);
});

it('can determine what role the user has on a token', function () {
    $user               = $this->user();
    $token              = $this->token;
    $anotherToken       = Token::factory()->withStatus(TokenStatusEnum::FINISHED)->createForTest();

    $token->shareWith($user, 'owner');
    $anotherToken->shareWith($user, 'collaborator');

    expect($user->roleOn($token))->toBe('owner');
    expect($user->roleOn($anotherToken))->toBe('collaborator');
    expect($user->roleOn($this->token()))->toBeEmpty();
});

it('can return all tokens that are accessible to the user', function () {
    $user = $this->user();

    Token::factory()->withStatus(TokenStatusEnum::FINISHED)->createForTest()->shareWith($user, 'owner');
    Token::factory()->withStatus(TokenStatusEnum::FINISHED)->createForTest()->shareWith($user, 'owner');
    Token::factory()->withStatus(TokenStatusEnum::FINISHED)->createForTest()->shareWith($user, 'owner');

    expect($user->tokens)->toHaveCount(3);
});

it('can determine the permissions of an user for a given token', function () {
    $user           = $this->user();
    $anotherUser    = $this->user();
    $andAnotherUser = $this->user();
    $token          = $this->token($user, TokenStatusEnum::FINISHED);

    $token->shareWith($anotherUser, 'test', Collaborator::availablePermissions());

    expect($user->permissionsOn($token))->toBe([]);
    expect($anotherUser->permissionsOn($token))->toBe(Collaborator::availablePermissions());
    expect($andAnotherUser->permissionsOn($token))->toBeEmpty();
});

it('user can have a collection of starred notifications', function () {
    expect($this->user()->starredNotifications())->toBeInstanceOf(MorphMany::class);
});

it('should return true if user have some unseen notifications', function () {
    $user  = $this->user();
    $token = $this->token($user, TokenStatusEnum::FINISHED);

    DatabaseNotification::factory()->ownedBy($token)->create();

    $this->assertDatabaseHas('users', ['seen_notifications_at' => null]);

    expect($user->hasNewNotifications())->toBeTrue();
});

it('should return true if user have some new unseen notifications', function () {
    $user = $this->user();

    $lastCheckedDate = Carbon::now()->sub(1, 'hour');
    $user->update(['seen_notifications_at' => $lastCheckedDate]);

    $token = $this->token($user, TokenStatusEnum::FINISHED);

    DatabaseNotification::factory()->ownedBy($token)->create();

    $this->assertDatabaseHas('users', ['seen_notifications_at' => $lastCheckedDate]);

    expect($user->hasNewNotifications())->toBeTrue();
});

it('should return false if user have no unseen notifications', function () {
    $user  = $this->user();
    $token = $this->token($user, TokenStatusEnum::FINISHED);

    DatabaseNotification::factory()->ownedBy($token)->create();

    $this->assertDatabaseHas('users', ['seen_notifications_at' => null]);

    $user->update(['seen_notifications_at' => now()]);

    expect($user->hasNewNotifications())->toBeFalse();
});

it('should return false if user have no notifications at all', function () {
    $user  = $this->user();

    $this->assertDatabaseHas('users', ['seen_notifications_at' => null]);

    expect($user->hasNewNotifications())->toBeFalse();
});

it('can determine whether user is waiting for an email confirmation', function () {
    $user = $this->user();

    expect($user->waitingForEmailConfirmation())->toBeFalse();

    $user->setMetaAttribute('email_to_update', 'email-to-update@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', now()->toString());

    expect($user->waitingForEmailConfirmation())->toBeTrue();

    $user->setMetaAttribute('email_to_update_stored_at', now()->subDays(2)->toString());

    expect($user->waitingForEmailConfirmation())->toBeFalse();
});

it('can send email confirmation change email', function () {
    Mail::fake();

    $user = $this->user();
    $user->update(['name' => 'John Doe']);

    Carbon::setTestNow('2020-01-01 10:30:30');

    $user->sendEmailChangeConfirmationMail('EMAIL-to-update@example.com');

    $user->refresh();

    expect($user->waitingForEmailConfirmation())->toBeTrue();
    expect($user->getMetaAttribute('email_to_update'))->toBe('email-to-update@example.com');
    expect($user->getMetaAttribute('email_to_update_stored_at'))->toBe('Wed Jan 01 2020 10:30:30 GMT+0000');
    Mail::assertQueued(ConfirmEmailChange::class, fn ($mail) => $mail->email === 'email-to-update@example.com' && $mail->name === 'John Doe');

    Carbon::setTestNow();
});
