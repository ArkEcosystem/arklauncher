<?php

declare(strict_types=1);

use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;
use Domain\User\Models\DatabaseNotification;

beforeEach(function () {
    $this->token = Token::factory()->withStatus(TokenStatusEnum::FINISHED)->createForTest();
});

it('should be able to retrieve the name belonging to a notification', function () {
    $user  = $this->user();
    $token = $this->token($user, TokenStatusEnum::FINISHED);

    $notification = DatabaseNotification::factory()->ownedBy($token)->create();

    expect($notification->name())->toBe($token->name);
});

it('should be able to retrieve the title belonging to a notification', function () {
    $user  = $this->user();
    $token = $this->token($user, TokenStatusEnum::FINISHED);

    $notification = DatabaseNotification::factory()->ownedBy($token)->create();

    expect($notification->title())->toBe($token->name);
});

it('should be able to retrieve the logo belonging to a notification', function () {
    $user  = $this->user();
    $token = $this->token($user, TokenStatusEnum::FINISHED);

    $notification = DatabaseNotification::factory()->ownedBy($token)->create();

    expect($notification->logo())->toBe($token->logo);
});

it('should be able to get the route for a token', function () {
    $user  = $this->user();
    $token = $this->token($user, TokenStatusEnum::FINISHED);

    $notification = DatabaseNotification::factory()->ownedBy($token)->create();

    expect($notification->route())->toBe(route('tokens.details', $token));
});

it('should return null when a token has been deleted', function () {
    $user  = $this->user();
    $token = $this->token($user, TokenStatusEnum::FINISHED);

    $notification = DatabaseNotification::factory()->ownedBy($token)->create();
    $token->delete();

    expect($notification->route())->toBeNull();
});
