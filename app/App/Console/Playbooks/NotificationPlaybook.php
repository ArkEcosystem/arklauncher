<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use App\Collaborator\Notifications\CollaboratorAcceptedInvite;
use App\Collaborator\Notifications\CollaboratorDeclinedInvite;
use App\Server\Notifications\IndexServerProviderImagesFailed;
use App\Server\Notifications\IndexServerProviderPlansFailed;
use App\Server\Notifications\IndexServerProviderRegionsFailed;
use App\Server\Notifications\ServerDeleted;
use App\Server\Notifications\ServerDeployed;
use App\Server\Notifications\ServerFailedDeployment;
use App\Server\Notifications\ServerFailedToCreateOnProvider;
use App\Server\Notifications\ServerProviderAuthenticationFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyAdditionFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyLimitReached;
use App\Server\Notifications\ServerProviderSecureShellKeyRemovalFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyUniqueness;
use App\Server\Notifications\ServerProviderServerRemovalFailed;
use App\Server\Notifications\ServerProvisioned;
use App\Server\Notifications\ServerUnreachable;
use App\Token\Notifications\TokenDeleted;
use Domain\Server\Enums\PresetTypeEnum;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Support\Services\Haiku;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class NotificationPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        // Prep
        $user         = User::factory()->create(['email' => 'notifications@ark.io']);
        $collaborator = User::factory()->create();
        $token        = Token::factory()->withDefaultNetworks()->create([
            'user_id' => $user->id,
            'name'    => 'MyNotificationChain',
        ]);
        $network        = Network::factory()->ownedBy($token)->create();

        $serverProvider = ServerProvider::factory()->digitalocean()->create([
            'token_id' => $token->id,
        ]);

        $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

        $serverProvider->plans()->syncWithoutDetaching(ServerProviderPlan::factory()->count(3)->create());
        $serverProvider->regions()->syncWithoutDetaching(ServerProviderRegion::factory()->count(3)->create());
        $serverProvider->images()->syncWithoutDetaching(ServerProviderImage::factory()->count(3)->create());

        $server = $token->servers()->create([
            'network_id'                => $network->id,
            'server_provider_id'        => $token->serverProviders()->first()->id,
            'server_provider_plan_id'   => $token->serverProviders()->first()->plans()->first()->id,
            'server_provider_region_id' => $token->serverProviders()->first()->regions()->first()->id,
            'server_provider_image_id'  => $token->serverProviders()->first()->images()->first()->id,
            'name'                      => Haiku::name(),
            'preset'                    => PresetTypeEnum::GENESIS,
        ]);
        $server->setStatus('provisioning');
        $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

        // Token
        $user->notify(new TokenDeleted($token));

        // Server
        $user->notify(new ServerDeployed($server));
        $user->notify(new ServerDeleted($server));
        $user->notify(new ServerFailedDeployment($server));
        $user->notify(new ServerFailedToCreateOnProvider($serverProvider, $server->name));
        $user->notify(new ServerProvisioned($server));
        $user->notify(new ServerUnreachable($server));

        // Server Provider
        $user->notify(new IndexServerProviderImagesFailed($serverProvider));
        $user->notify(new IndexServerProviderPlansFailed($serverProvider));
        $user->notify(new IndexServerProviderRegionsFailed($serverProvider));

        $user->notify(new ServerProviderAuthenticationFailed($serverProvider));
        $user->notify(new ServerProviderSecureShellKeyAdditionFailed($serverProvider));
        $user->notify(new ServerProviderSecureShellKeyLimitReached($serverProvider));
        $user->notify(new ServerProviderSecureShellKeyRemovalFailed($serverProvider));
        $user->notify(new ServerProviderSecureShellKeyUniqueness($serverProvider));
        $user->notify(new ServerProviderServerRemovalFailed($serverProvider));

        // Collaborator
        $user->notify(new CollaboratorAcceptedInvite($token, $collaborator));
        $user->notify(new CollaboratorDeclinedInvite($token, $collaborator));
    }
}
