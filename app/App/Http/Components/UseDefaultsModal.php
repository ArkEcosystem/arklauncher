<?php

declare(strict_types=1);

namespace App\Http\Components;

use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;

final class UseDefaultsModal extends Component
{
    use HasDefaultRender;
    use HasModal;

    public bool $requiresConfirmation = false;

    public bool $isFee = false;

    /** @var mixed */
    protected $listeners = [
        'askForConfirmation' => 'showModal',
        'closeModal'         => 'close',
    ];

    public function showModal(bool $isFee = false): void
    {
        $this->requiresConfirmation = true;
        $this->isFee                = $isFee;
    }

    public function close(): void
    {
        $this->requiresConfirmation = false;

        $this->modalClosed();
    }

    public function emitDefaults(?bool $overwrite = false): void
    {
        if ($this->isFee === true) {
            $this->emit('setFeeDefaults', $overwrite);

            return;
        }
        $this->emit('setDefaults', $overwrite);
    }
}
