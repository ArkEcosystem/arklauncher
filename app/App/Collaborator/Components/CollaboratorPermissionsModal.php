<?php

declare(strict_types=1);

namespace App\Collaborator\Components;

use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\Collaborator\Models\Collaborator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;

final class CollaboratorPermissionsModal extends Component
{
    use HasDefaultRender;
    use HasModal;

    public array $permissions = [];

    /** @var mixed */
    protected $listeners = ['showCollaboratorPermissions' => 'setPermissions'];

    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;

        $this->openModal();
    }

    public function getAvailablePermissionsProperty(): Collection
    {
        return collect(Collaborator::availablePermissions());
    }
}
