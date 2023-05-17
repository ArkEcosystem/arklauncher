<?php

declare(strict_types=1);

namespace App\User\Components;

use Illuminate\Support\Collection;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\InteractsWithUser;

final class ManageSecureShellKeys extends Component
{
    use HasDefaultRender;
    use InteractsWithUser;

    /** @var mixed */
    protected $listeners = ['refreshSecureShellKeys' => '$refresh'];

    public function getKeysProperty(): Collection
    {
        return $this->user->secureShellKeys;
    }
}
