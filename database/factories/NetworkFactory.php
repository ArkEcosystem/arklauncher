<?php

declare(strict_types=1);

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Factories\Factory;

final class NetworkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Network::class;

    protected $token;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'token_id' => $this->token ?? Token::factory(),
            'name'     => 'mainnet',
        ];
    }

    public function ownedBy(Token $token)
    {
        $this->token = $token;

        return $this;
    }

    public function createForTest()
    {
        $network = $this->create(['token_id' => $this->token ?? Token::factory()]);

        return Network::findOrFail($network->id);
    }
}
