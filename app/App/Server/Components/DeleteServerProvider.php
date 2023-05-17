<?php

declare(strict_types=1);

namespace App\Server\Components;

use ARKEcosystem\Foundation\UserInterface\Components\Concerns\HandleToast;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderDeleted;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Components\Concerns\InteractsWithToken;

final class DeleteServerProvider extends Component
{
    use HasDefaultRender;
    use HasModal;
    use InteractsWithToken;
    use AuthorizesRequests;
    use HandleToast;

    public ?int $serverProviderId = null;

    public bool $deleteOnProvider = false;

    /** @var mixed */
    protected $listeners = ['deleteServerProvider' => 'askForConfirmation'];

    public function askForConfirmation(int $id): void
    {
        $this->serverProviderId = $id;

        $this->openModal();
    }

    public function toggleDeleteOnProvider(): void
    {
        $this->deleteOnProvider = ! $this->deleteOnProvider;
    }

    public function destroy() : void
    {
        $serverProvider = ServerProvider::findOrFail($this->serverProviderId);

        $this->authorize('delete', $serverProvider);

        if ($this->deleteOnProvider && $serverProvider->servers_count > 0) {
            $serverProvider->servers()->get()->each->delete();
        }

        $serverProvider->delete();

        ServerProviderDeleted::dispatch($serverProvider);

        $this->serverProviderId = null;

        $this->closeModal();

        $this->toast(trans('tokens.server-providers.removed_success'));
        $this->emit('refreshServerProviders');
    }

    public function cancel(): void
    {
        $this->serverProviderId = null;

        $this->closeModal();
    }
}
