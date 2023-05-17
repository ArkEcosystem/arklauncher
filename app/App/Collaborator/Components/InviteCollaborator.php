<?php

declare(strict_types=1);

namespace App\Collaborator\Components;

use ARKEcosystem\Foundation\UserInterface\Components\Concerns\HandleToast;
use Domain\Collaborator\Actions\InviteCollaboratorAction;
use Domain\Collaborator\Models\Collaborator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\InteractsWithPermissions;
use Support\Components\Concerns\InteractsWithToken;

final class InviteCollaborator extends Component
{
    use HasDefaultRender;
    use AuthorizesRequests;
    use InteractsWithToken;
    use InteractsWithPermissions;
    use HandleToast;

    public ?string $email = null;

    public function invite(): void
    {
        $this->authorize('createCollaborator', $this->token);

        $data = $this->validate([
            'email'         => ['required', 'email', 'max:255'],
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['required', 'string', Rule::in($this->getAvailablePermissionsProperty()->toArray())],
        ]);

        resolve(InviteCollaboratorAction::class)($this->token, strtolower($data['email']), $this->permissions);

        $this->email       = null;
        $this->permissions = [];

        $this->emit('refreshCollaborators');

        $this->toast(trans('tokens.invitation_sent'));
    }

    public function getAvailablePermissionsProperty(): Collection
    {
        return collect(Collaborator::availablePermissions());
    }
}
