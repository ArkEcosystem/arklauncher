<?php

declare(strict_types=1);

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Models\Network;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class ServerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Server::class;

    protected $network;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'network_id'                => Network::factory(),
            'server_provider_id'        => ServerProvider::factory(),
            'name'                      => Str::slug($this->faker->name()),
            'provider_server_id'        => mt_rand(1000000, 9999999),
            'server_provider_plan_id'   => ServerProviderPlan::factory(),
            'server_provider_region_id' => ServerProviderRegion::factory(),
            'server_provider_image_id'  => ServerProviderImage::factory(),
            'ip_address'                => $this->faker->ipv4(),
            'user_password'             => $this->faker->password(),
            'sudo_password'             => $this->faker->password(),
            'delegate_passphrase'       => $this->faker->password(),
            'delegate_password'         => $this->faker->password(),
            'preset'                    => 'relay',
        ];
    }

    public function hetzner()
    {
        return $this->state([
            'server_provider_id' => ServerProvider::factory()->hetzner(),
        ]);
    }

    public function digitalocean()
    {
        return $this->state([
            'server_provider_id' => ServerProvider::factory()->digitalocean(),
        ]);
    }

    public function vultr()
    {
        return $this->state([
            'server_provider_id' => ServerProvider::factory()->vultr(),
        ]);
    }

    public function linode()
    {
        return $this->state([
            'server_provider_id' => ServerProvider::factory()->linode(),
        ]);
    }

    public function aws()
    {
        return $this->state([
            'server_provider_id' => ServerProvider::factory()->aws(),
        ]);
    }

    public function genesis()
    {
        return $this->state(['preset' => 'genesis']);
    }

    public function seed()
    {
        return $this->state(['preset' => 'seed']);
    }

    public function relay()
    {
        return $this->state(['preset' => 'relay']);
    }

    public function forger()
    {
        return $this->state(['preset' => 'forger']);
    }

    public function explorer()
    {
        return $this->state(['preset' => 'explorer']);
    }

    public function ownedBy(Network $network)
    {
        $this->network = $network;

        return $this;
    }

    public function createForTest(?array $attributes = [])
    {
        $server = $this->create([
            'network_id' => $this->network ?? Network::factory(),
        ] + $attributes);

        $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $server->token->user->id);

        $server->token->shareWith($server->token->user, 'owner');

        return Server::findOrFail($server->id);
    }
}
