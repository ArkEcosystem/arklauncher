<?php

declare(strict_types=1);

namespace Support\Components\Concerns;

trait HasFlashMessages
{
    /** @codeCoverageIgnore this method is not yet used */
    public function flash(string $message, string $type = 'success'): void
    {
        $this->emit('flashMessage', [$message, $type]);
    }

    public function toast(string $message, string $type = 'success'): void
    {
        $this->emit('toastMessage', [$message, $type]);
    }
}
