<?php

declare(strict_types=1);

use App\Http\Components\ManageWelcomeScreens;
use Livewire\Livewire;

it('shows a welcome modal', function () {
    Livewire::actingAs($this->user())
            ->test(ManageWelcomeScreens::class)
            ->assertSee('Welcome');
});

it('can dismiss the modal', function () {
    $user = $this->user();

    expect($user->seen_welcome_screens_at)->toBeNull();

    Livewire::actingAs($user)
            ->test(ManageWelcomeScreens::class)
            ->assertSee('Welcome')
            ->call('close')
            ->assertDontSee('Welcome');

    expect($user->seen_welcome_screens_at)->toBeNull();
});

it('can dismiss the modal and hide forever if toggled', function () {
    $user = $this->user();

    expect($user->seen_welcome_screens_at)->toBeNull();

    Livewire::actingAs($this->user())
            ->test(ManageWelcomeScreens::class)
            ->set('hideForever', true)
            ->assertSee('Welcome')
            ->call('close')
            ->assertDontSee('Welcome');

    expect($user->seen_welcome_screens_at)->toBeNull();
});

it('can dismiss the modal and never show it again', function () {
    $user = $this->user();

    expect($user->seen_welcome_screens_at)->toBeNull();

    Livewire::actingAs($user)
            ->test(ManageWelcomeScreens::class)
            ->assertSee('Welcome')
            ->call('closeForever')
            ->assertDontSee('Welcome');

    expect($user->fresh()->seen_welcome_screens_at)->not->toBeNull();
});
