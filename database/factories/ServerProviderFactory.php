<?php

declare(strict_types=1);

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Enums\ServerProviderTypeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class ServerProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServerProvider::class;

    protected $token;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'token_id'         => $this->token ?? Token::factory(),
            'type'             => ServerProviderTypeEnum::DIGITALOCEAN,
            'name'             => $this->faker->unique()->domainName(),
            'extra_attributes' => ['accessToken'  => encrypt(Str::random(42))],
            'provider_key_id'  => 512190,
        ];
    }

    public function digitalocean()
    {
        return $this->state([
            'type'            => ServerProviderTypeEnum::DIGITALOCEAN,
            'name'            => $this->faker->unique()->domainName(),
            'provider_key_id' => 512190,
        ]);
    }

    public function hetzner()
    {
        return $this->state([
            'type'            => ServerProviderTypeEnum::HETZNER,
            'name'            => $this->faker->unique()->domainName(),
            'provider_key_id' => 2323,
        ]);
    }

    public function vultr()
    {
        return $this->state([
            'type'            => ServerProviderTypeEnum::VULTR,
            'name'            => $this->faker->unique()->domainName(),
            'provider_key_id' => '541b4960f23bd',
        ]);
    }

    public function linode()
    {
        return $this->state([
            'type'            => ServerProviderTypeEnum::LINODE,
            'name'            => $this->faker->unique()->domainName(),
            'provider_key_id' => 1234,
        ]);
    }

    public function aws()
    {
        return $this->state([
            'type'             => ServerProviderTypeEnum::AWS,
            'name'             => $this->faker->unique()->domainName(),
            'extra_attributes' => [
                'accessToken'  => encrypt(Str::random(42)),
                'accessKey'    => encrypt(Str::random(42)),
            ],
        ]);
    }

    public function withRegions()
    {
        return $this->afterCreating(function (ServerProvider $serverProvider) {
            $serverProvider->regions()
                ->syncWithoutDetaching(ServerProviderRegion::factory()->count(3)->create());
        });
    }

    public function withImages()
    {
        return $this->afterCreating(function (ServerProvider $serverProvider) {
            $serverProvider->images()
                ->syncWithoutDetaching(ServerProviderImage::factory()->count(3)->create());
        });
    }

    public function withPlans()
    {
        return $this->afterCreating(function (ServerProvider $serverProvider) {
            $override = [
                'memory' => config('deployer.deployment.minimumServerRam') + 1,
            ];
            if ($serverProvider->regions()->exists()) {
                $override['regions'] = $serverProvider->regions()->pluck('uuid');
            }
            $serverProvider->plans()
                ->syncWithoutDetaching(
                    ServerProviderPlan::factory()->count(3)->create($override)
                );
        });
    }

    public function ownedBy(Token $token)
    {
        $this->token = $token;

        return $this;
    }

    public function createForTest(?array $attributes = [])
    {
        $serverProvider = $this->create([
            'token_id' => $this->token ?? Token::factory(),
        ] + $attributes);

        $serverProvider->token->shareWith($serverProvider->token->user, 'owner');

        return ServerProvider::findOrFail($serverProvider->id);
    }
}
