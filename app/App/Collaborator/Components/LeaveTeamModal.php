<?php

declare(strict_types=1);

namespace App\Collaborator\Components;

use ARKEcosystem\Foundation\Fortify\Components\Concerns\InteractsWithUser;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use ARKEcosystem\Foundation\UserInterface\Support\Enums\FlashType;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;
use Support\Components\Concerns\HasDefaultRender;

final class LeaveTeamModal extends Component
{
    use HasDefaultRender;
    use HasModal;
    use InteractsWithUser;

    public const ALERT_STATUS = 'team';

    public ?int $tokenId = null;

    public ?Token $token;

    /** @var mixed */
    protected $listeners = ['showLeaveTeamModal' => 'showModal'];

    public function showModal(int $id): void
    {
        $this->tokenId = $id;
        $this->token   = Token::find($id);
    }

    public function close(): void
    {
        $this->tokenId = null;

        $this->closeModal();
    }

    public function leave(): Redirector|RedirectResponse
    {
        /** @var ?User */
        $user = $this->user;

        if ($user !== null && $this->token !== null && ! $user->ownsToken($this->token)) {
            $this->token->stopSharingWith($user);
        }

        $this->tokenId = null;

        $this->closeModal();

        alert('tokens.messages.left_team', FlashType::SUCCESS, ['team' => $this->token?->name]);

        return redirect()->route('user.teams')->with('status', self::ALERT_STATUS);
    }
}
