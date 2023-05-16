<?php

declare(strict_types=1);

namespace Support\Components;

use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;

final class YoutubeModal extends Component
{
    use HasDefaultRender;

    public bool $showModal = false;

    public string $url;

    /** @var mixed */
    protected $listeners = [
        'showYoutubeModal' => 'showModal',
    ];

    public function mount(string $url): void
    {
        $this->url = $url;
    }

    public function showModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }
}
