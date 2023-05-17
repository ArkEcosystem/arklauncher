<?php

declare(strict_types=1);

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use Carbon\Carbon;
use Domain\Coin\Models\Coin;
use Domain\Collaborator\Models\Invitation;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Enums\TokenAttributeEnum;
use Domain\Token\Events\TokenCreated;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Token::class;

    protected array $config = [
        'fees' => [
          'static' => [
            'vote'                 => 100000000,
            'transfer'             => 10000000,
            'multiSignature'       => 500000000,
            'secondSignature'      => 500000000,
            'delegateRegistration' => 2500000000,
            'ipfs'                 => 0,
            'multiPayment'         => 0,
            'delegateResignation'  => 2500000000,
          ],
          'dynamic' => [
            'enabled'    => false,
            'addonBytes' => [
              'ipfs'                 => 250,
              'vote'                 => 100,
              'transfer'             => 100,
              'multiPayment'         => 500,
              'multiSignature'       => 500,
              'secondSignature'      => 250,
              'delegateResignation'  => 400000,
              'delegateRegistration' => 400000,
            ],
            'minFeePool'      => 3000,
            'minFeeBroadcast' => 3000,
          ],
        ],
        'token'       => 'TST',
        'coreIp'      => '127.0.0.1',
        'symbol'      => 'TST',
        'apiPort'     => 4003,
        'forgers'     => 51,
        'p2pPort'     => 4000,
        'blocktime'   => 8,
        'chainName'   => 'testing',
        'explorerIp'  => '0.0.0.0',
        'devnetPeers' => [
          0 => '0.0.0.0',
        ],
        'webhookPort'  => 4004,
        'monitorPort'  => 4005,
        'databaseHost' => '1.1.1.1',
        'databaseName' => 'core_token',
        'databasePort' => '5432',
        'devnetPrefix' => 'D',
        'explorerPort' => 4200,
        'licenseEmail' => null,
        'mainnetPeers' => [
          0 => '0.0.0.0',
        ],
        'totalPremine'         => 2100000000000000,
        'mainnetPrefix'        => 'M',
        'testnetPrefix'        => 'T',
        'rewardPerBlock'       => 200000000,
        'rewardHeightStart'    => 1,
        'vendorFieldLength'    => 255,
        'transactionsPerBlock' => 150,
        'maxBlockPayload'      => 2097152,
        'wif'                  => 1,
    ];

    protected $user;

    protected $status;

    private int $networkCount = 0;

    private int $serverCount = 1;

    private int $invitationsCount = 1;

    private int $serverProviderCount = 0;

    private bool $defaultNetworks = false;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'            => User::factory(),
            'coin_id'            => Coin::factory(),
            'name'               => $this->faker->firstName(),
            'slug'               => $this->faker->firstName(),
            'config'             => $this->config,
            'keypair'            => [
                'privateKey' => encrypt($this->faker->word()),
                'publicKey'  => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAAAQQDDHr/jh2Jy4yALcK4JyWbVkPRaWmhck3IgCoeOO3z1e2dBowLh64QAM+Qb72pxekALga2oi4GvT+TlWNhzPH4V example',
            ],
            'extra_attributes' => ['repoName' => $this->faker->firstName()],
        ];
    }

    /**
     * New token ready to be configured.
     */
    public function newly(): Factory
    {
        $name = $this->faker->slug(3);

        return $this->state(fn () => [
            'name'         => $name,
            'slug'         => $name,
            'config'       => null,
            'keypair'      => null,
            'onboarded_at' => null,
        ])->afterCreating(function (Token $token) {
            TokenCreated::dispatch($token);
        });
    }

    /**
     * Onboarding configuration completed.
     */
    public function withOnboardingConfigurationCompleted(): Factory
    {
        $name = $this->faker->slug(3);

        return $this->state(fn () => [
            'name'         => $name,
            'slug'         => $name,
        ])->afterCreating(function (Token $token) {
            TokenCreated::dispatch($token);

            $token->setMetaAttribute('onboarding.configuration_completed_at', Carbon::now());
        });
    }

    /**
     * With Onboarding server provider select.
     */
    public function withOnboardingServerProvider(): Factory
    {
        return $this->withOnboardingConfigurationCompleted()->afterCreating(function (Token $token) {
            $token->setMetaAttribute('onboarding.server_providers_status', 'completed');

            ServerProvider::factory()
                ->withRegions()
                ->withImages()
                ->withPlans()
                ->create(['token_id' => $token->id]);
        });
    }

    /**
     * Onboarding server configuration completed.
     */
    public function withOnboardingServerConfiguration(): Factory
    {
        return $this->withOnboardingServerProvider()->afterCreating(function (Token $token) {
            $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
                'server_provider_id' => 1,
            ]);
        });
    }

    /**
     * Onboarding secure shell key completed.
     */
    public function withOnboardingSecureShellKey(): Factory
    {
        return $this->withOnboardingServerConfiguration()->afterCreating(function (Token $token) {
            $token->secureShellKeys()->sync(SecureShellKey::factory()->create()->id);
        });
    }

    public function ownedBy(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function withNetwork(int $count): self
    {
        $this->networkCount = $count;

        return $this;
    }

    public function withDefaultNetworks(): self
    {
        $this->defaultNetworks = true;

        return $this;
    }

    public function withServerProviders(int $count): self
    {
        $this->serverProviderCount = $count;

        return $this;
    }

    public function withServers(int $count): self
    {
        $this->serverCount = $count;

        return $this;
    }

    public function withInvitations(int $count): self
    {
        $this->invitationsCount = $count;

        return $this;
    }

    public function withStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function createForTest(?array $attributes = [])
    {
        $user = $this->user ?? User::withoutEvents(function () {
            return User::factory()->create();
        });

        $coin = Coin::withoutEvents(function () {
            return Coin::factory()->create();
        });

        $token = $this->create([
            'user_id' => $user->id,
            'coin_id' => $coin->id,
        ] + $attributes);

        // Share the token with the owner
        $token->shareWith($user, 'owner');

        if ($this->status) {
            $token->setStatus($this->status);
        }

        if ($this->serverProviderCount > 0) {
            for ($i = 0; $i < $this->serverProviderCount; $i++) {
                ServerProvider::factory()->ownedBy($token)->createForTest();
            }
        }

        // Create default servers
        Network::factory($this->networkCount)
            ->ownedBy($token)
            ->create()
            ->each(fn ($network) => Server::factory($this->serverCount)->create(['network_id' => $network->id]));

        // Default Networks
        if ($this->defaultNetworks) {
            $token->networks()->create(['name' => 'devnet']);
            $token->networks()->create(['name' => 'testnet']);
        }

        // Invitations
        if ($this->invitationsCount > 0) {
            Invitation::factory($this->invitationsCount)->create(['token_id' => $token->id]);
        }

        return Token::findOrFail($token->id);
    }
}
