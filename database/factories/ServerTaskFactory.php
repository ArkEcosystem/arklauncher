<?php

declare(strict_types=1);

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use App\Server\Jobs\ProvisionUser;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerTask;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ServerTaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServerTask::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'server_id' => Server::factory(),
            'type'      => ProvisionUser::class,
            'name'      => $this->faker->firstName(),
            'user'      => $this->faker->username(),
            'exit_code' => 0,
            'script'    => $this->faker->word(),
            'output'    => $this->faker->paragraph(),
            'options'   => [],
        ];
    }
}
