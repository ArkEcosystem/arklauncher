<?php

declare(strict_types=1);

use App\Http\Components\UseDefaultsModal;
use Livewire\Livewire;

it('can show and hide the modal', function () {
    $this->actingAs($this->user());

    Livewire::test(UseDefaultsModal::class)
            ->assertSet('requiresConfirmation', false)
            ->call('showModal')
            ->assertSet('requiresConfirmation', true)
            ->assertSet('isFee', false)
            ->assertSee(trans('actions.fill_empty'))
            ->call('close')
            ->assertSet('requiresConfirmation', false)
            ->assertSet('isFee', false)
            ->assertDontSee(trans('actions.fill_empty'))
            ->call('showModal', true)
            ->assertSet('requiresConfirmation', true)
            ->assertSet('isFee', true);
});

it('can emit value depending on fee property', function () {
    $this->actingAs($this->user());

    Livewire::test(UseDefaultsModal::class)
            ->assertSet('isFee', false)
            ->call('emitDefaults', true)
            ->assertEmitted('setDefaults', true)
            ->set('isFee', true)
            ->call('emitDefaults')
            ->assertEmitted('setFeeDefaults');
});
