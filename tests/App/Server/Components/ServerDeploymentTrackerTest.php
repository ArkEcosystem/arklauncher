<?php

declare(strict_types=1);

use App\Server\Components\ServerDeploymentTracker;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Enums\ServerDeploymentStatus;
use Domain\Server\Models\Server;
use Livewire\Livewire;

it('can view the tracker for the genesis preset', function () {
    $server = Server::factory()->genesis()->createForTest();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.server.installation.states.online'));
});

it('can view the tracker for the seed preset', function () {
    $server = Server::factory()->seed()->createForTest();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.server.installation.states.online'));
});

it('can view the tracker for the relay preset', function () {
    $server = Server::factory()->relay()->createForTest();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.server.installation.states.online'));
});

it('can view the tracker for the forger preset', function () {
    $server = Server::factory()->forger()->createForTest();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.server.installation.states.online'));
});

it('can view the tracker for the explorer preset', function () {
    $server = Server::factory()->explorer()->createForTest();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.server.installation.states.online'));
});

it('can view the disclaimer modal with sufficient permissions', function () {
    $user   = $this->user();
    $server = Server::factory()->seed()->createForTest();

    $server->token->collaborators()->attach($user, [
            'role'        => 'collaborator',
            'permissions' => ['server:create'],
        ]);
    $server->setStatus('provisioning');

    expect($server->fresh()->getMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN))->toBeNull();

    Livewire::actingAs($user)
                ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
                ->assertSee(trans('pages.server.installation.passwords_modal.disclaimer'));
});

it('cannot view the disclaimer modal with insufficient permissions', function () {
    $user   = $this->user();
    $server = Server::factory()->seed()->createForTest();

    $server->token->shareWith($user);
    $server->setStatus('provisioning');

    expect($server->fresh()->getMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN))->toBeNull();

    Livewire::actingAs($user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.server.installation.states.online'))
            ->assertDontSee(trans('pages.server.installation.passwords_modal.disclaimer'));
});

it('can set initializing state to failed if it failed', function () {
    $server = Server::factory()->genesis()->createForTest();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.token.status.initializing'))
            ->assertDontSee(trans('pages.token.status.failed'));

    $server->setStatus('failed');

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.token.status.initializing'))
            ->assertSee(trans('pages.token.status.failed'));
});

it('can close the modal', function () {
    $server = Server::factory()->createForTest();

    expect($server->fresh()->getMetaAttribute(ServerAttributeEnum::SERVER_CREATED_MODAL_SEEN))->toBeNull();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.server.installation.states.online'))
            ->call('closeServerCreatedModal')
            ->assertDontSee('Relay Deployment successful');

    expect($server->fresh()->getMetaAttribute(ServerAttributeEnum::SERVER_CREATED_MODAL_SEEN))->toBeTrue();
});

it('can close the passwords modal', function () {
    $server = Server::factory()->createForTest();

    $server->setStatus('provisioning');

    expect($server->fresh()->getMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN))->toBeNull();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee(trans('pages.server.installation.passwords_modal.disclaimer'))
            ->call('closeDisclaimerModal')
            ->assertDontSee(trans('pages.server.installation.passwords_modal.disclaimer'));

    expect($server->fresh()->getMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN))->toBeTrue();
});

it('can get the credentials for the deployed server', function () {
    $server = Server::factory()->createForTest();
    $server->setStatus('provisioning');

    $realComponent = new ServerDeploymentTracker('1');
    $realComponent->mount($server->token, $server->id);

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSee($realComponent->getCredentials());
});

it('shows the deployment failed modal if server does not exist', function () {
    $server = Server::factory()->createForTest();
    $server->setStatus('provisioning');

    $server->delete();

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSet('showDeploymentFailedModal', true)
            ->assertSet('failureReason', trans('pages.server.installation.failed_modal.generic_error'));
});

it('shows the deployment failed modal if server has failed to deploy', function () {
    $server = Server::factory()->createForTest();
    $server->setStatus('failed');

    Livewire::actingAs($server->token->user)
            ->test(ServerDeploymentTracker::class, ['token' => $server->token, 'serverId' => $server->id])
            ->assertSet('showDeploymentFailedModal', true)
            ->assertSet('failureReason', 'failed');
});

it('does not show credentials if server is deleted', function () {
    $server = Server::factory()->createForTest();
    $server->setStatus('provisioning');

    $server->delete();

    $component = Livewire::actingAs($server->token->user)->test(ServerDeploymentTracker::class, [
        'token'    => $server->token,
        'serverId' => $server->id,
    ])->instance();

    expect($component->getCredentials())->toBe('');
});

it('can get first state if server is removed', function () {
    $server = Server::factory()->createForTest();
    $server->setStatus('provisioning');

    $component = Livewire::actingAs($server->token->user)->test(ServerDeploymentTracker::class, [
        'token'    => $server->token,
        'serverId' => $server->id,
    ]);

    $server->delete();

    expect($component->instance()->getFirstState())->toBe('provisioning');
});

it('can get first state from the cache if server is removed', function () {
    $server = Server::factory()->createForTest();
    $server->setStatus('provisioning');

    $server->delete();

    $states = (new ServerDeploymentStatus())->getGroupStates()[$server->preset];

    $component = Livewire::actingAs($server->token->user)->test(ServerDeploymentTracker::class, [
        'token'    => $server->token,
        'serverId' => $server->id,
    ])->set('cachedGroups', $states);

    expect($component->instance()->getFirstState())->toBe(head(head($states)));
});
