<?php

declare(strict_types=1);

namespace App\Console\Playbooks;

use App\Listeners\ListenForActivityLogs;
use App\Server\Notifications\ServerDeployed;
use Domain\Collaborator\Models\Invitation;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Server\Enums\PresetTypeEnum;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Events\ServerCreated;
use Domain\Token\Events\ServerProviderCreated;
use Domain\Token\Events\TokenCreated;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Str;
use Support\Services\Haiku;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DevelopmentPlaybook extends Playbook
{
    public function before(): array
    {
        return [
            UserPlaybook::once(),
            CoinPlaybook::once(),
        ];
    }

    public function after(): array
    {
        return [
            NotificationPlaybook::once(),
        ];
    }

    public function run(InputInterface $input, OutputInterface $output): void
    {
        User::get()->each(function ($user): void {
            Invitation::factory()->count(10)->create(['user_id' => $user->id]);

            $key = SecureShellKey::factory()->create(['user_id' => $user->id]);

            Token::factory()->count(5)->create(['user_id' => $user->id])->each(function ($token) use ($key, $user) : void {
                TokenCreated::dispatch($token);

                $token->slug = Str::slug($token->name);

                $token->setMetaAttribute('repoName', $token->slug);
                $token->setMetaAttribute('onboarding.configuration_status', 'completed');
                $token->setMetaAttribute('onboarding.server_providers_status', 'completed');
                $token->setMetaAttribute('onboarding.server_config_status', 'completed');
                $token->setMetaAttribute('onboarding.secure_shell_keys_status', 'completed');
                $token->setMetaAttribute('onboarding.collaborators_status', 'completed');
                $token->setMetaAttribute('onboarding.servers_status', 'completed');

                $token->setStatus(TokenStatusEnum::FINISHED);

                $token->secureShellKeys()->sync($key->id);

                $serverProvider = ServerProvider::factory()->digitalocean()->create([
                    'token_id' => $token->id,
                ]);

                $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

                ServerProviderCreated::dispatch($serverProvider);

                $serverProvider->plans()->syncWithoutDetaching(ServerProviderPlan::factory()->count(3)->create());
                $serverProvider->regions()->syncWithoutDetaching(ServerProviderRegion::factory()->count(3)->create());
                $serverProvider->images()->syncWithoutDetaching(ServerProviderImage::factory()->count(3)->create());

                $token
                    ->networks()
                    ->each(function ($network) use ($token, $user) : void {
                        $server = $token->servers()->create([
                            'network_id'                => $network->id,
                            'server_provider_id'        => $token->serverProviders()->first()->id,
                            'server_provider_plan_id'   => $token->serverProviders()->first()->plans()->first()->id,
                            'server_provider_region_id' => $token->serverProviders()->first()->regions()->first()->id,
                            'server_provider_image_id'  => $token->serverProviders()->first()->images()->first()->id,
                            'name'                      => Haiku::name(),
                            'preset'                    => PresetTypeEnum::GENESIS,
                        ]);

                        $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

                        $this->fakeServerCreatedEvent($server);

                        $user->notify(new ServerDeployed($server));
                    });
            });
        });
    }

    /**
     * We should usually call a `ServerCreated::dispatch($server);` event, so
     * the following listeners are called. However, since we are using a fake
     * server, the `CreateServerOnProvider` listener had problems connecting to
     * the server provider for provisioning so I am manually changing the server
     * status and running the listeners.
     *
     * @param Server $server
     *
     * @return void
     */
    private function fakeServerCreatedEvent($server): void
    {
        $event = new ServerCreated($server);

        (new ListenForActivityLogs())->handle($event);

        // Instead of (new CreateServerOnProvider())->handle($event);
        $server->update([
            'provider_server_id'             => rand(5, 15),
            'ip_address'                     => '127.0.0.1',
            'provisioning_job_dispatched_at' => now(),
        ]);
        $server->setStatus('provisioning');
        $server->markAsOnline();
    }
}
