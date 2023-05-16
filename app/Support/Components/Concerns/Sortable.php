<?php

declare(strict_types=1);

namespace Support\Components\Concerns;

trait Sortable
{
    public ?string $sortBy = null;

    public ?string $sortDirection = null;

    public function sortBy(?string $name): void
    {
        if ($this->sortBy === $name) {
            if ($this->sortDirection === 'asc') {
                $this->sortDirection = 'desc';
            } else {
                $this->sortDirection = null;
                $this->sortBy        = null;
            }
        } else {
            $this->sortBy        = $name;
            $this->sortDirection = 'asc';
        }

        $this->sortingUpdated();
    }
}
