<?php

declare(strict_types=1);

namespace App\Token\Components;

use App\Enums\NetworkTypeEnum;
use ARKEcosystem\Foundation\Fortify\Components\Concerns\InteractsWithUser;
use ARKEcosystem\Foundation\UserInterface\Http\Livewire\Concerns\HasModal;
use Domain\Server\Enums\PresetTypeEnum;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProviderImage;
use Domain\Token\Enums\TokenAttributeEnum;
use Domain\Token\Events\ServerCreated;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Livewire\Component;
use Support\Components\Concerns\HasDefaultRender;
use Support\Helpers\Format;

final class DeployBlockchain extends Component
{
    use HasDefaultRender;
    use HasModal;
    use InteractsWithUser;

    public ?int $tokenId = null;

    public Token $token;

    public array $options;

    public string $selectedOption;

    /** @var mixed */
    protected $listeners = [
        'deployBlockchain' => 'askForConfirmation',
        'setNetworkOption' => 'selectOption',
    ];

    public function mount(): void
    {
        $this->options = [
            NetworkTypeEnum::MAINNET => trans('tokens.deploy_blockchain_modal.option_mainnet'),
            NetworkTypeEnum::DEVNET  => trans('tokens.deploy_blockchain_modal.option_devnet'),
            NetworkTypeEnum::TESTNET => trans('tokens.deploy_blockchain_modal.option_testnet'),
        ];

        $this->selectedOption = NetworkTypeEnum::MAINNET;
    }

    public function selectOption(string $option): void
    {
        $this->selectedOption = $option;
    }

    public function askForConfirmation(int $id): void
    {
        $this->openModal();

        $this->tokenId   = $id;
        $this->token     = Token::findOrFail($id);
    }

    public function deploy(): void
    {
        $serverConfig   = $this->token->getMetaAttribute(TokenAttributeEnum::SERVER_CONFIG);
        $serverProvider = $this->token->serverProviders()->findOrFail($serverConfig['server_provider_id']);
        $network        = $this->token->networks()->where('name', $this->selectedOption)->firstOrFail();

        /** @var ServerProviderImage $ServerProviderImage */
        $ServerProviderImage = $serverProvider->images()->where('uuid', $serverProvider->client()->getImageId())->first();

        $server = $network->servers()->create([
            'server_provider_id'        => $serverConfig['server_provider_id'],
            'name'                      => Format::withToken($serverConfig['name']),
            'server_provider_region_id' => $serverConfig['server_provider_region_id'],
            'server_provider_plan_id'   => $serverConfig['server_provider_plan_id'],
            'server_provider_image_id'  => $ServerProviderImage->id,
            'preset'                    => PresetTypeEnum::GENESIS,
        ]);

        /** @var User $user */
        $user = $this->user;

        $server->setMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN, false);
        $server->setMetaAttribute(ServerAttributeEnum::SERVER_CREATED_MODAL_SEEN, false);
        $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

        ServerCreated::dispatch($server);

        $this->token->forgetServerConfiguration();

        $this->redirectRoute('tokens.servers.show', [$this->token, $network, $server]);
    }

    public function cancel(): void
    {
        $this->tokenId = null;

        $this->modalClosed();
    }
}
