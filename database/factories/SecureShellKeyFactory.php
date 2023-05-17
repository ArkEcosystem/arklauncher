<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\SecureShell\Models\SecureShellKey;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SecureShellKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SecureShellKey::class;

    protected $user;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'       => $this->faker->firstName(),
            'user_id'    => $this->user ?? User::factory(),
            'public_key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCYAi5XvpgT1tBUFVwD4fc+XigWVKUkBx0OhbGbxfm918TmarQy4LDKtX3Xqjm6ownmVE97E1Gvc5sIln6hwk+mtkXqQMrdD8tNqtYIr2mwW5yJDON0ml8mhn1Kb5myRkdFX+B411c3OJrsIv7t1OUAsAx+mrX4wanHENOnhHRhuiXP08OkXXpwQYPItmNWwyokcZ9etI6eP9SMlyrdneZTCZWsf++Gv4zh+XTdchiaYfiquAmtFwYnC3VinUwMuZPWhvYHL7Loo9sYTPpoW1YR/wyIhM33nd3yOnIPVODp8mn/VjjYRLPjYkhzOejOlP2W3MVkSY2SX156X5Rm7XDsggq0gWEHZ01lYhvWzO3gfUNjUH+3PDKNJcl+pOEjSHCsIKSFfTxV6cMkUhKFx2b0Gw8Q2msToZSFnzczyLXPA6r+9DMS0X78buBh+JXOevXaV2nWGrOvEmSyQBMLVb9EeRrmFnOwoxubIc7BDQpKJT3Pd62s+pWkSJrc/gegHCQzxZ8ZZZfwACTezm7u333MiuAf/VEUMMTUCyGjNmbPGtqIhDbQvRokxsEz0jNYuCy8OpsOZDLHn20lEw7nIXVe17YAV4ByvDXKqKNnX9fd6AsgSQlyF7VpGWN2dGZ9CpNVkHpy29skTWzKwLGGD+SmIspBec5lxZz+HaFWZDra1Q== robot@ark.io',
            // 'public_key' => ShellSecureShellKey::make()['publicKey'],
        ];
    }

    public function ownedBy(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function createForTest($overrides = [])
    {
        $secureShellKey = $this->create(
            array_merge($overrides, ['user_id' => $this->user ?? User::factory()])
        );

        return SecureShellKey::findOrFail($secureShellKey->id);
    }
}
