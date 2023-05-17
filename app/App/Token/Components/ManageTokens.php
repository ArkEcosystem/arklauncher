<?php

declare(strict_types=1);

namespace App\Token\Components;

use Domain\Token\Models\Token;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;
use Support\Components\Concerns\InteractsWithUser;

final class ManageTokens extends Component
{
    use AuthorizesRequests;
    use InteractsWithUser;

    public Collection $tokens;

    public ?Token $selectedToken = null;

    public ?int $index = null;

    public ?string $network = null;

    public ?string $token = null;

    /** @var mixed */
    protected $listeners = [
        'setToken' => 'selectToken',
        'setIndex' => 'setIndex',
    ];

    /** @var mixed */
    protected $queryString = [
        'index'   => ['except' => ''],
        'network' => ['except' => ''],
        'token'   => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->tokens = $this->user->tokens()->orderBy('name')->get();

        if ($this->tokens->count() > 0) {
            $this->selectedToken = $this->tokens->where('name', $this->token)->first() ?? $this->tokens->first();
        } else {
            $this->selectedToken = null;
        }
    }

    public function render(): View
    {
        return view('livewire.manage-tokens', [
            'tokens'        => $this->tokens,
            'selectedToken' => $this->selectedToken,
        ]);
    }

    public function selectToken(int $id): void
    {
        $token = $this->user->tokens()->findOrFail($id);

        $this->token = $token->name;

        $this->selectedToken = $token;
    }

    public function setIndex(int $id): void
    {
        $this->index = $id;
    }

    public function editToken(int $id): void
    {
        $this->redirectRoute('tokens.show', $this->tokens->firstWhere('id', $id));
    }
}
