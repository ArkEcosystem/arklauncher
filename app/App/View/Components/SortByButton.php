<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class SortByButton extends Component
{
    public function __construct(public string $name, public ?string $sortBy = null, public ?string $sortDirection = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.sort-by-button');
    }
}
