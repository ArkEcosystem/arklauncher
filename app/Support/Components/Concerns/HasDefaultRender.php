<?php

declare(strict_types=1);

namespace Support\Components\Concerns;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Str;

trait HasDefaultRender
{
    public function render(): View
    {
        return ViewFacade::make('livewire.'.Str::kebab(class_basename($this)));
    }
}
