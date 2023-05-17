<?php

declare(strict_types=1);

use Domain\Server\Models\Server;
use Domain\Server\Policies\ServerPolicy;
use Domain\Token\Models\Token;

it('can determine if the user passes view any', function () {
    $server = Server::factory()->createForTest();

    expect((new ServerPolicy())->viewAny($server->token->user, $server))->toBeTrue();
    expect((new ServerPolicy())->viewAny($this->user(), $server))->toBeFalse();
});

it('can determine if the user passes view', function () {
    $server = Server::factory()->createForTest();

    expect((new ServerPolicy())->view($server->token->user, $server))->toBeTrue();
    expect((new ServerPolicy())->view($this->user(), $server))->toBeFalse();
});

it('can determine if the user passes create', function () {
    $token  = Token::factory()->withNetwork(1)->createForTest();
    $server = Server::factory()->ownedBy($token->networks()->first())->createForTest();

    expect((new ServerPolicy())->create($server->token->user, $token))->toBeTrue();
    expect((new ServerPolicy())->create($this->user(), $token))->toBeFalse();
});

it('can determine if the user passes update', function () {
    $server = Server::factory()->createForTest();

    expect((new ServerPolicy())->update($server->token->user, $server))->toBeTrue();
    expect((new ServerPolicy())->update($this->user(), $server))->toBeFalse();
});

it('can determine if the user passes delete', function () {
    $server = Server::factory()->createForTest();

    expect((new ServerPolicy())->delete($server->token->user, $server))->toBeTrue();
    expect((new ServerPolicy())->delete($this->user(), $server))->toBeFalse();
});

it('can determine if the user passes start', function () {
    $server = Server::factory()->createForTest();

    expect((new ServerPolicy())->start($server->token->user, $server))->toBeTrue();
    expect((new ServerPolicy())->start($this->user(), $server))->toBeFalse();
});

it('can determine if the user passes stop', function () {
    $server = Server::factory()->createForTest();

    expect((new ServerPolicy())->stop($server->token->user, $server))->toBeTrue();
    expect((new ServerPolicy())->stop($this->user(), $server))->toBeFalse();
});

it('can determine if the user passes restart', function () {
    $server = Server::factory()->createForTest();

    expect((new ServerPolicy())->restart($server->token->user, $server))->toBeTrue();
    expect((new ServerPolicy())->restart($this->user(), $server))->toBeFalse();
});
