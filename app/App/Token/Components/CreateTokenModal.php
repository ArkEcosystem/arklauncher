<?php

declare(strict_types=1);

namespace App\Token\Components;

use ARKEcosystem\Foundation\Fortify\Components\Concerns\InteractsWithUser;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Events\TokenDeleted;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;
use Support\Components\Concerns\HasDefaultRender;

/**
 * @property User $user
 */
final class CreateTokenModal extends Component
{
    use HasDefaultRender;
    use HasModal;
    use InteractsWithUser;

    public ?Token $token = null;

    /** @var mixed */
    protected $listeners = [
        'onCreateToken' => 'handle',
    ];

    public function handle(): RedirectResponse|Redirector|null
    {
        abort_if($this->user === null, 403);

        $this->token = $this->user->ownedTokens()->currentStatus(TokenStatusEnum::PENDING)->first();

        if ($this->token === null) {
            return $this->continue();
        }

        return null;
    }

    public function continue(): RedirectResponse|Redirector
    {
        return redirect()->route('tokens.create');
    }

    public function cancel(): void
    {
        $this->token = null;

        $this->closeModal();
    }

    public function deletePendingToken(): RedirectResponse|Redirector
    {
        /** @var Token $token */
        $token = $this->token;

        $token->forceDelete();

        TokenDeleted::dispatch($token);

        return $this->continue();
    }
}
