<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Coin\Models\Coin;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CoinFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Coin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'   => 'ARK',
            'slug'   => 'ark',
            'symbol' => 'ARK',
        ];
    }
}
