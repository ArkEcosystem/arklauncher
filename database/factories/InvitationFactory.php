<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Collaborator\Models\Collaborator;
use Domain\Collaborator\Models\Invitation;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class InvitationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $permissions = Collaborator::availablePermissions();

        return [
            'uuid'           => $this->faker->uuid(),
            'token_id'       => Token::factory(),
            'user_id'        => User::factory(),
            'email'          => $this->faker->email(),
            'role'           => 'collaborator',
            'permissions'    => $this->faker->randomElements(
                $permissions,
                $this->faker->numberBetween(1, count($permissions))
            ),
        ];
    }
}
