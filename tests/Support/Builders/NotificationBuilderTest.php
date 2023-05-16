<?php

declare(strict_types=1);

use Domain\Collaborator\Models\Invitation;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Support\Builders\NotificationBuilder;

it('should return an array from invitation', function () {
    $invitation = Invitation::factory()->create();

    expect((new NotificationBuilder())->fromInvitation($invitation, [])->getContent())->toBeArray();
});

it('should return an array from server', function () {
    $server = Server::factory()->createForTest();

    expect((new NotificationBuilder())->fromServer($server, [])->getContent())->toBeArray();
});

it('should return an array from server provider', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect((new NotificationBuilder())->fromServerProvider($serverProvider, [])->getContent())->toBeArray();
});

it('should return an array from token', function () {
    $token = Token::factory()->createForTest();

    expect((new NotificationBuilder())->fromToken($token, [])->getContent())->toBeArray();
});

it('should contains a type key', function () {
    $token = Token::factory()->createForTest();

    $structure = (new NotificationBuilder())->fromToken($token, [])->getContent();

    expect($structure)->toHaveKey('type');
    expect($structure['type'])->toBe('success');
});

it('should be able to set an action', function () {
    $token = Token::factory()->createForTest();

    $structure = (new NotificationBuilder())->fromToken($token, [], 'danger')->withAction('foo', 'bar')->getContent();

    expect($structure)->toHaveKey('action');
    expect($structure['action'])->toHaveKey('title');
    expect($structure['action'])->toHaveKey('url');

    expect($structure['action']['title'])->toBe('foo');
    expect($structure['action']['url'])->toBe('bar');
});

it('should be able to set a type', function () {
    $token = Token::factory()->createForTest();

    $structure = (new NotificationBuilder())->fromToken($token, [])->success()->getContent();

    expect($structure['type'])->toBe('success');
});

it('should be able to set an user', function () {
    $token = Token::factory()->createForTest();

    $user = User::factory()->create();

    $structure = (new NotificationBuilder())->fromToken($token, [])->withAction('foo', 'bar')->withUser($user)->getContent();

    expect($structure)->toHaveKey('user');

    expect($structure['user'])->toBe($user->id);
});

it('should be able to set type to success', function () {
    $token = Token::factory()->createForTest();

    User::factory()->create();

    $structure = (new NotificationBuilder())->fromToken($token, [])->success()->getContent();

    expect($structure['type'])->toBe('success');
});

it('should be able to set type to warning', function () {
    $token = Token::factory()->createForTest();

    User::factory()->create();

    $structure = (new NotificationBuilder())->fromToken($token, [])->warning()->getContent();

    expect($structure['type'])->toBe('warning');
});

it('should be able to set type to danger', function () {
    $token = Token::factory()->createForTest();

    User::factory()->create();

    $structure = (new NotificationBuilder())->fromToken($token, [])->danger()->getContent();

    expect($structure['type'])->toBe('danger');
});
