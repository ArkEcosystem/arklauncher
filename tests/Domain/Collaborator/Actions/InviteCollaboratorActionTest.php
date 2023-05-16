<?php

declare(strict_types=1);

use App\Collaborator\Mail\InviteExistingUser;
use App\Collaborator\Mail\InviteNewUser;
use Domain\Collaborator\Actions\InviteCollaboratorAction;
use Domain\Collaborator\Models\Collaborator;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

it('can invite an existing user', function () {
    $owner = $this->user();

    $token = Token::factory()->ownedBy($owner)->createForTest();

    $existingUser = User::factory()->create();

    resolve(InviteCollaboratorAction::class)($token, $existingUser->email, Collaborator::availablePermissions());

    Notification::assertSentTo($existingUser, InviteExistingUser::class);
});

it('can invite a new user', function () {
    Mail::fake();

    resolve(InviteCollaboratorAction::class)($this->token(), 'hello@world.com', Collaborator::availablePermissions());

    Mail::assertQueued(InviteNewUser::class);
});

it('cant invite an user if it is already present on the token', function () {
    Mail::fake();

    $token = $this->token();

    $this->createInvitation($token, $token->user);

    try {
        resolve(InviteCollaboratorAction::class)($token, $token->user->email, Collaborator::availablePermissions());

        $this->fail('ValidationException was expected but was not thrown.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('email');
        expect($e->errors()['email'])->toBe([trans('tokens.user_already_on_token', ['email' => $token->user->email])]);
    }
});

it('cant invite an user if it is already invited to the token', function () {
    Mail::fake();

    $user        = $this->user();
    $token       = $this->token();

    $this->createInvitation($token, $user);

    try {
        resolve(InviteCollaboratorAction::class)($token, $user->email, Collaborator::availablePermissions());

        $this->fail('ValidationException was expected but was not thrown.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('email');
        expect($e->errors()['email'])->toBe([trans('tokens.user_already_invited_to_token', ['email' => $user->email])]);
    }
});
