<?php

declare(strict_types=1);

namespace App\Http\Components;

use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\InteractsWithUser;

final class ManageWelcomeScreens extends Component
{
    use HasDefaultRender;
    use InteractsWithUser;
    use HasModal;

    public bool $visible = false;

    public bool $hideForever = false;

    public function mount() : void
    {
        $this->visible = $this->user->seen_welcome_screens_at === null;

        $this->openModal();
    }

    public function close() : void
    {
        if ($this->hideForever) {
            $this->user->touch('seen_welcome_screens_at');
        }

        $this->closeModal();
    }

    public function closeForever() : void
    {
        $this->user->touch('seen_welcome_screens_at');

        $this->closeModal();
    }
}
