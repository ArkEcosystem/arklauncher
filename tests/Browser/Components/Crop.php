<?php

declare(strict_types=1);

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

final class Crop extends BaseComponent
{
    public function __construct(public string $modalName)
    {
    }

    public function selector(): string
    {
        return '[data-modal="'.$this->modalName.'"]';
    }

    public function assert(Browser $browser): void
    {
        $browser
            ->assertVisible($this->selector())
            ->assertSee(trans('ui::modals.crop-image.title'))
            ->assertSee(trans('ui::modals.crop-image.message'));
    }

    public function pressCropCancelButton(Browser $browser): void
    {
        $browser->click('@crop-cancel-button');
    }

    public function pressCropSaveButton(Browser $browser): void
    {
        $browser->click('@crop-save-button');
    }
}
