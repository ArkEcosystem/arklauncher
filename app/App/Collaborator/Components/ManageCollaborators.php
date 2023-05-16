<?php

declare(strict_types=1);

namespace App\Collaborator\Components;

use Domain\User\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\InteractsWithToken;

final class ManageCollaborators extends Component
{
    use HasDefaultRender;
    use InteractsWithToken;

    /** @var mixed */
    protected $listeners = ['refreshCollaborators' => '$refresh'];

    public function getCollaboratorsProperty(): Collection
    {
        return $this->token->collaborators()->where('user_id', '!=', Auth::id())->get();
    }

    public function getUserProperty(): User
    {
        return $this->token->collaborators()->where('user_id', Auth::id())->firstOrFail();
    }
}
