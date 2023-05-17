<?php

declare(strict_types=1);

use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Models\Token;
use Domain\User\Models\User;

it('can view the index', function () {
    $token = Token::factory()
        ->withNetwork(1)
        ->withServerProviders(1)
        ->createForTest();

    $this->actingAs($token->user)
        ->get(route('tokens.servers.index', [$token, $token->networks()->first()]))
        ->assertViewIs('app.tokens.servers.index');
});

it('can view the form to create a new server', function () {
    $token = Token::factory()
        ->withNetwork(1)
        ->withServerProviders(1)
        ->createForTest();

    ServerProviderRegion::factory()->create([
            'uuid'               => 'string',
        ]);

    ServerProviderPlan::factory()->create([
            'uuid'               => 'ccx21',
        ]);

    ServerProviderImage::factory()->create([
            'uuid'               => $token->serverProviders()->first()->client()->getImageId(),
        ]);

    $this
        ->actingAs($token->user)
        ->get(route('tokens.servers.create', [$token, $token->networks()->first()]))
        ->assertViewIs('app.tokens.servers.create');
});

it('prevents users without permission to access server creation page', function () {
    $token = Token::factory()
                ->withNetwork(1)
                ->withServerProviders(1)
                ->createForTest();

    $user = User::factory()->create();

    $token->shareWith($user, 'collaborator', [
        'server-provider:create',
    ]);

    $this->actingAs($user)
            ->get(route('tokens.servers.create', [$token, $token->networks()->first()]))
            ->assertForbidden();
});

it('can view the server', function () {
    $server = Server::factory()->createForTest();

    $this
            ->actingAs($server->token->user)
            ->get($server->pathShow())
            ->assertViewIs('app.tokens.servers.show');
});
