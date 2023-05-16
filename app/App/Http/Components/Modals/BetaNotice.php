<?php

declare(strict_types=1);

namespace App\Http\Components\Modals;

use App\Enums\Constants;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\InteractsWithUser;

final class BetaNotice extends Component
{
    use HasDefaultRender;
    use InteractsWithUser;
    use HasModal;

    public bool $agree = false;

    public function render(): View
    {
        return view('livewire.modals.beta-notice');
    }

    public function mount(): void
    {
        $this->modalShown = ! Cookie::has('beta_agreement_at');
    }

    public function close(): void
    {
        if (! $this->agree) {
            return;
        }

        Cookie::queue('beta_agreement_at', Carbon::now(), Constants::COOKIE_EXPIRY_FOREVER);

        $this->closeModal();
    }
}
