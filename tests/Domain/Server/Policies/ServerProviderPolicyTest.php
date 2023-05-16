<?php

declare(strict_types=1);

use Domain\Server\Models\ServerProvider;
use Domain\Server\Policies\ServerProviderPolicy;

it('can_determine_if_the_user_passes_view_any', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect((new ServerProviderPolicy())->viewAny($serverProvider->token->user, $serverProvider))->toBeTrue();
    expect((new ServerProviderPolicy())->viewAny($this->user(), $serverProvider))->toBeFalse();
});

it('can_determine_if_the_user_passes_view', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect((new ServerProviderPolicy())->view($serverProvider->token->user, $serverProvider))->toBeTrue();
    expect((new ServerProviderPolicy())->view($this->user(), $serverProvider))->toBeFalse();
});

it('can_determine_if_the_user_passes_create', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect((new ServerProviderPolicy())->create($serverProvider->token->user, $serverProvider->token))->toBeTrue();
    expect((new ServerProviderPolicy())->create($this->user(), $serverProvider->token))->toBeFalse();
});

it('can_determine_if_the_user_passes_update', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect((new ServerProviderPolicy())->update($serverProvider->token->user, $serverProvider))->toBeTrue();
    expect((new ServerProviderPolicy())->update($this->user(), $serverProvider))->toBeFalse();
});

it('can_determine_if_the_user_passes_delete', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect((new ServerProviderPolicy())->delete($serverProvider->token->user, $serverProvider))->toBeTrue();
    expect((new ServerProviderPolicy())->delete($this->user(), $serverProvider))->toBeFalse();
});
