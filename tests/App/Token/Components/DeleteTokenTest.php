<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use App\Server\Jobs\DestroyServerOnServerProvider;
use App\Token\Components\DeleteToken;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerDeleted;
use Domain\Token\Events\TokenDeleted;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Livewire\Exceptions\NonPublicComponentMethodCall;
use Livewire\Livewire;

beforeEach(function () {
    $this->token = Token::factory()
        ->withNetwork(1)
        ->withServers(1)
        ->createForTest();
});

it('can ask for confirmation and set the server id', function () {
    $token = $this->token();

    Livewire::test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id);
});

it('destroy method should call the right actions', function () {
    $token = $this->token;

    Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::actingAs($token->user)
            ->test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('selectOption', 'servers')
            ->assertSet('selectedOptions', ['blockchain', 'servers'])
            ->call('destroy')
            ->assertRedirect(route('tokens'));
});

it('only allows authorized users to delete tokens', function () {
    $token = $this->token;

    $anotherUser = $this->user();

    Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::actingAs($anotherUser)
            ->test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('selectOption', 'servers')
            ->assertSet('selectedOptions', ['blockchain', 'servers'])
            ->call('destroy')
            ->assertForbidden();
});

it('destroy token should only soft delete the token', function () {
    $token = $this->token;

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::actingAs($token->user)
            ->test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('destroy');

    $this->assertSoftDeleted('tokens', ['id' => $token->id]);
});

it('triggers the token deleted event when the token is deleted', function () {
    Event::fake();

    $token = $this->token;

    Livewire::actingAs($token->user)
            ->test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('destroy');

    Event::assertDispatched(fn (TokenDeleted $event) => $event->token->is($token));
});

it('destroy with servers should only delete the servers of the token', function () {
    $token = $this->token;

    ServerProvider::factory()->ownedBy($token)->createForTest();

    $server = Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::actingAs($token->user)
            ->test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->call('selectOption', 'servers')
            ->assertSet('tokenId', $token->id)
            ->call('destroy');

    $this->assertDatabaseMissing('servers', ['id' => $server->id]);
});

it('should not delete servers on provider when token is deleted', function () {
    $token = $this->token;

    ServerProvider::factory()->ownedBy($token)->createForTest();

    $server = Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Bus::fake();

    Livewire::actingAs($token->user)
            ->test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->call('selectOption', 'blockchain')
            ->assertSet('tokenId', $token->id)
            ->call('destroy');

    $this->assertDatabaseMissing('servers', ['id' => $server->id]);

    Bus::assertNotDispatched(DestroyServerOnServerProvider::class);
});

it('should delete servers on provider when servers option is checked', function () {
    $token = $this->token;

    ServerProvider::factory()->ownedBy($token)->createForTest();

    $server = Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Bus::fake();

    Livewire::actingAs($token->user)
            ->test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->call('selectOption', 'blockchain')
            ->call('selectOption', 'servers')
            ->assertSet('tokenId', $token->id)
            ->call('destroy');

    $this->assertDatabaseMissing('servers', ['id' => $server->id]);

    Bus::assertDispatched(DestroyServerOnServerProvider::class);
});

it('triggers the server deleted event when destroy with servers', function () {
    Event::fake();

    $token = $this->token;

    ServerProvider::factory()->ownedBy($token)->createForTest();

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest();

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::actingAs($token->user)
            ->test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->call('selectOption', 'servers')
            ->assertSet('tokenId', $token->id)
            ->call('destroy');

    Event::assertDispatched(fn (ServerDeleted $event) => $event->server->is($server));
});

it('can cancel the confirmation', function () {
    $token = $this->token;

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('cancel')
            ->assertSet('tokenId', null);

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);
});

it('can add an option to selected options', function () {
    $token = $this->token;

    Livewire::test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('selectOption', 'servers')
            ->assertSet('selectedOptions', ['blockchain', 'servers']);
});

it('can add multiple options to selected options', function () {
    $token = $this->token;

    Livewire::test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('selectOption', 'servers')
            ->assertSet('selectedOptions', ['blockchain', 'servers']);
});

it('can remove an option from selected options', function () {
    $token = $this->token;

    Livewire::test(DeleteToken::class)
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('selectOption', 'servers')
            ->assertSet('selectedOptions', ['blockchain', 'servers'])
            ->call('selectOption', 'servers')
            ->assertSet('selectedOptions', ['blockchain']);
});

it('blockchain option should never be disabled', function () {
    $token = $this->token;

    $realComponent = new DeleteToken('1');
    $realComponent->mount();
    $realComponent->askForConfirmation($token->id);

    expect($realComponent->shouldBeDisabled('blockchain'))->toBeFalse();
});

it('servers option should be disabled if no servers', function () {
    $token = $this->token;

    $token->servers()->delete();

    $realComponent = new DeleteToken('1');
    $realComponent->mount();
    $realComponent->askForConfirmation($token->id);

    expect($realComponent->shouldBeDisabled('servers'))->toBeTrue();
});

it('servers option should be enabled if existing servers', function () {
    $token = $this->token;

    $realComponent = new DeleteToken('1');
    $realComponent->mount();
    $realComponent->askForConfirmation($token->id);

    expect($realComponent->shouldBeDisabled('servers'))->toBeFalse();
});

it('invalid option should return false', function () {
    $token = $this->token;

    $realComponent = new DeleteToken('1');
    $realComponent->mount();
    $realComponent->askForConfirmation($token->id);

    expect($realComponent->shouldBeDisabled('foo'))->toBeFalse();
});

it('selected options should return an array', function () {
    $token = $this->token;

    $realComponent = new DeleteToken('1');
    $realComponent->mount();
    $realComponent->askForConfirmation($token->id);

    expect($realComponent->getSelectedOptionsProperty())->toBeArray();
});

it('should check filled in name against token name', function () {
    $token = $this->token;

    Livewire::test(DeleteToken::class)
        ->call('askForConfirmation', $token->id)
        ->assertSet('tokenId', $token->id)
        ->assertSet('token_name', null)
        ->assertSeeHtml('<button class="inline-flex items-center button-cancel" wire:click="destroy" disabled>')
        ->set('token_name', $token->name)
        ->assertDontSeeHtml('<button class="inline-flex items-center button-cancel" wire:click="destroy" disabled>')
        ->set('token_name', $token->name.'123')
        ->assertSeeHtml('<button class="inline-flex items-center button-cancel" wire:click="destroy" disabled>');
});

it('should not have access to destroyToken method', function () {
    $token = $this->token;

    Livewire::test(DeleteToken::class)
        ->call('askForConfirmation', $token->id)
        ->assertSet('tokenId', $token->id)
        ->call('destroyToken');
})->throws(NonPublicComponentMethodCall::class);

it('should not have access to destroyWithServers method', function () {
    $token = $this->token;

    Livewire::test(DeleteToken::class)
        ->call('askForConfirmation', $token->id)
        ->assertSet('tokenId', $token->id)
        ->call('destroyWithServers');
})->throws(NonPublicComponentMethodCall::class);
