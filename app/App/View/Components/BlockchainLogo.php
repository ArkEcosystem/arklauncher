<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\Token\Models\Token;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class BlockchainLogo extends Component
{
    public function __construct(
        public Token $token,
        public string $size = 'normal' // @TODO: use enums
    ) {
    }

    public function render(): View
    {
        return view('components.blockchain-logo');
    }

    public function dimensions(): string
    {
        return match ($this->size) {
            'small' => 'w-11 h-11',
            default => 'w-20 h-20',
        };
    }

    public function rounded(): string
    {
        return match ($this->size) {
            'small' => 'rounded',
            default => 'rounded-xl',
        };
    }
}
