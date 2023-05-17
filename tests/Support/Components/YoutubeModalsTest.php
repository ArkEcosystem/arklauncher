<?php

declare(strict_types=1);

use Livewire\Livewire;
use Support\Components\YoutubeModal;

it('can open the modal', function () {
    Livewire::test(YoutubeModal::class, ['url' => 'fakeUrl'])
            ->assertSet('showModal', false)
            ->assertDontSee('fakeUrl')
            ->call('showModal')
            ->assertSet('showModal', true)
            ->assertSee('fakeUrl');
});

it('can close the modal', function () {
    Livewire::test(YoutubeModal::class, ['url' => 'fakeUrl'])
            ->assertSet('showModal', false)
            ->assertDontSee('fakeUrl')
            ->call('showModal')
            ->assertSet('showModal', true)
            ->assertSee('fakeUrl')
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertDontSee('fakeUrl');
});
