<?php

declare(strict_types=1);

namespace App\Http\Components;

use App\Enums\NetworkTypeEnum;
use ARKEcosystem\Foundation\UserInterface\Components\Concerns\HandleToast;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use RuntimeException;
use Support\Components\Concerns\HasDefaultRender;
use Symfony\Component\HttpFoundation\Response;

final class DownloadInstallScript extends Component
{
    use HasDefaultRender;
    use HandleToast;

    public Token $token;

    public function download(string $networkName) : ?Response
    {
        $network = $this->token->network($networkName);

        abort_if($network === null, 404);

        $path = $network->configurationPath();

        if (! Storage::disk('token-config')->exists($path)) {
            report(new RuntimeException('User tried to download an installation script for token [ID: '.$this->token->id.'] but the file does not exist.'));

            $this->toast(trans('pages.token.error_downloading_script'), 'error');

            return null;
        }

        return Storage::disk('token-config')->download(
            $path,
            $this->generateFilename($network)
        );
    }

    public function availableNetworks() : Collection
    {
        return collect(NetworkTypeEnum::all())->filter(function ($networkName) {
            $network = $this->token->network($networkName);

            return $network !== null && $network->hasProvisionedGenesis();
        })->values();
    }

    private function generateFilename(Network $network) : string
    {
        return sprintf('config-%s-%s.zip', Str::slug($this->token->name), $network->name);
    }
}
