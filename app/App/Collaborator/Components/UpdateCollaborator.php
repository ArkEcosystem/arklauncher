<?php

declare(strict_types=1);

namespace App\Collaborator\Components;

use Domain\Collaborator\Models\Collaborator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\HasFlashMessages;
use Support\Components\Concerns\InteractsWithPermissions;
use Support\Components\Concerns\InteractsWithToken;

final class UpdateCollaborator extends Component
{
    use HasDefaultRender;
    use HasFlashMessages;
    use AuthorizesRequests;
    use InteractsWithToken;
    use InteractsWithPermissions;

    public ?int $collaboratorId = null;

    /** @var mixed */
    protected $listeners = ['updateCollaborator' => 'edit'];

    public function edit(int $id): void
    {
        $collaborator = $this->token->collaborators()->findOrFail($id);

        $this->collaboratorId = $id;
        $this->permissions    = $collaborator->pivot->permissions ?? [];
    }

    public function update(): void
    {
        $this->authorize('createCollaborator', $this->token);

        $this->validate([
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['required', 'string', Rule::in($this->getAvailablePermissionsProperty()->toArray())],
        ]);

        $this->token->collaborators()->updateExistingPivot(
            $this->collaboratorId,
            ['permissions' => $this->permissions]
        );

        $this->collaboratorId = null;
        $this->permissions    = [];

        $this->emit('refreshCollaborators');

        $this->toast(trans('tokens.collaborators.permissions_updated_success'), 'success');
    }

    public function getAvailablePermissionsProperty(): Collection
    {
        return collect(Collaborator::availablePermissions());
    }

    public function close(): void
    {
        $this->collaboratorId = null;
        $this->permissions    = [];
    }
}
