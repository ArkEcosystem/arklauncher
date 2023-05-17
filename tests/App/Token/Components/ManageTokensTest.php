<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use App\Token\Components\ManageTokens;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Server\Models\Server;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;
use Livewire\Livewire;

beforeEach(function () {
    $this->token = Token::factory()
        ->withStatus(TokenStatusEnum::FINISHED)
        ->withServerProviders(1)
        ->withNetwork(1)
        ->withDefaultNetworks(1)
        ->createForTest();

    $this->user = $this->token->user;

    $key = SecureShellKey::factory()->ownedBy($this->user)->create();

    $this->token->secureShellKeys()->sync($key->id);

    $this->server = Server::factory()->ownedBy($this->token->network(NetworkTypeEnum::MAINNET))->createForTest();
});

it('redirects to the edit page', function () {
    $token = $this->token;

    Livewire::actingAs($token->user)
            ->test(ManageTokens::class)
            ->call('editToken', $token->id)
            ->assertRedirect(route('tokens.show', $token));
});

it('can select a token', function () {
    $user = $this->user();

    Livewire::actingAs($user)
            ->test(ManageTokens::class)
            ->assertSet('selectedToken', null);

    $token = Token::factory()
        ->ownedBy($user)
        ->withStatus(TokenStatusEnum::FINISHED)
        ->withServerProviders(1)
        ->withNetwork(1)
        ->withDefaultNetworks(1)
        ->createForTest();

    Livewire::actingAs($user)
            ->test(ManageTokens::class)
            ->assertSet('network', '')
            ->assertSet('token', '')
            ->call('selectToken', $token->id)
            ->assertSet('selectedToken.id', $token->id)
            ->assertSet('token', $token->name);
});

it('should change the selected token on token change', function () {
    $user  = $this->user;

    $firstToken = $this->token;

    $firstTokenNetwork = $firstToken->network(NetworkTypeEnum::MAINNET);

    $this->actingAs($user);

    Livewire::test(ManageTokens::class)
        ->assertSet('selectedToken.id', $firstToken->id);

    $secondToken = Token::factory()
        ->ownedBy($user)
        ->withStatus(TokenStatusEnum::FINISHED)
        ->withServerProviders(1)
        ->withNetwork(1)
        ->withDefaultNetworks(1)
        ->createForTest();

    $secondTokenNetwork = $secondToken->network(NetworkTypeEnum::MAINNET);

    Livewire::withQueryParams(['token' => 'test'])
        ->test(ManageTokens::class)
        ->assertSet('token', 'test')
        ->emit('setToken', $secondToken->id)
        ->assertSet('selectedToken.id', $secondToken->id)
        ->assertSet('token', $secondToken->name);
});

it('should not crash when the network specified as query parameter is invalid', function () {
    $user  = $this->user;
    $token = $this->token;

    $this->actingAs($user);

    $response = $this->get(route('tokens', ['network' => 'foo', 'token' => $token->name]));

    expect($response->status())->toBe(200);
});

it('should not crash when the token specified as query parameter is invalid', function () {
    $user = $this->user;

    $this->actingAs($user);

    $response = $this->get(route('tokens', ['network' => NetworkTypeEnum::MAINNET, 'token' => 'foo']));

    expect($response->status())->toBe(200);
});

it('should not crash when the network and token specified as query parameters are invalid', function () {
    $user = $this->user;

    $this->actingAs($user);

    $response = $this->get(route('tokens', ['network' => 'foo', 'token' => 'foo']));

    expect($response->status())->toBe(200);
});

it('should select the first network and first token available if both query parameters are invalid', function () {
    $user  = $this->user;

    $token = $this->token;

    Token::factory()->ownedBy($user)->createForTest();
    Token::factory()->ownedBy($user)->createForTest();

    $tokenNetwork = $token->network(NetworkTypeEnum::MAINNET);

    $this->actingAs($user);

    $response = $this->get(route('tokens', ['network' => 'foo', 'token' => 'foo']));

    expect($response->status())->toBe(200);

    Livewire::test(ManageTokens::class)
        ->assertSet('selectedToken.id', $token->id);
});

it('can set the index of the latest clicked token', function () {
    $user = $this->user;

    $instance = Livewire::actingAs($user)
        ->withQueryParams(['index' => 23])
        ->test(ManageTokens::class)
        ->assertSet('index', 23)
        ->call('setIndex', 1)
        ->assertSet('index', 1);
});

it('can sort the table servers', function () {
    $user  = $this->user;
    $token = $this->token;

    Livewire::actingAs($user)
        ->withQueryParams(['index' => 23])
        ->test(ManageTokens::class)
        ->assertSet('index', 23)
        ->call('setIndex', 1)
        ->assertSet('index', 1);
});
