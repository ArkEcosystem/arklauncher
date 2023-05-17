<?php

declare(strict_types=1);

/* @var $factory \Illuminate\Database\Eloquent\Factory */

namespace Database\Factories;

use Domain\Token\Models\Token;
use Domain\User\Models\DatabaseNotification;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DatabaseNotificationFactory extends Factory
{
    protected $model = DatabaseNotification::class;

    protected $token;

    public function definition()
    {
        return [
            'id'              => $this->faker->uuid(),
            'type'            => "App\TestNotification",
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->token ? $this->token->user->id : 1,
            'data'            => [
                'relatable_type' => $this->token ? $this->token->getMorphClass() : null,
                'relatable_id'   => $this->token ? $this->token->getKey() : null,
                'token'          => $this->token ? $this->token->id : 1,
                'content'        => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'type'           => 'success',
                'action'         => [
                    'title' => 'View',
                    'url'   => route('home'),
                ],
            ],
        ];
    }

    public function ownedBy(Token $token)
    {
        $this->token = $token;

        return $this;
    }
}
