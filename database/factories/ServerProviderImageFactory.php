<?php

declare(strict_types=1);

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Enums\ServerProviderTypeEnum;
use Domain\Server\Models\ServerProviderImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class ServerProviderImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServerProviderImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->name(),
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
}
