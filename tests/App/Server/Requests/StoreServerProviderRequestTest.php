<?php

declare(strict_types=1);

use App\Enums\ServerProviderTypeEnum;
use App\Server\Requests\StoreServerProviderRequest;
use Faker\Factory;
use Illuminate\Support\Facades\Route;

beforeEach(fn () => Route::post('/', fn () => resolve(StoreServerProviderRequest::class)));

it('request is authorized if the user is authenticated', function () {
    $this
        ->actingAs($this->user())
        ->postJson('/', [
            'type'         => $this->faker->word,
            'name'         => $this->faker->word,
            'access_token' => $this->faker->uuid,
        ])
        ->assertStatus(200);
});

it('request is forbidden if the user is a guest', function () {
    $this
        ->postJson('/', [
            'type'         => $this->faker->word,
            'name'         => $this->faker->word,
            'access_token' => $this->faker->uuid,
        ])
        ->assertForbidden();
});

it('requests should pass', function ($data) {
    $this
        ->actingAs($this->user())
        ->postJson('/', $data)
        ->assertOk()
        ->assertJsonMissingValidationErrors();
})->with(passScenarios());

it('requests should fail', function ($data, $errors) {
    $this
        ->actingAs($this->user())
        ->postJson('/', $data)
        ->assertStatus(422)
        ->assertJsonValidationErrors($errors);
})->with(failScenarios());

function passScenarios(): array
{
    $faker = Factory::create();

    return [
        'request_should_pass_when_all_required_data_is_provided_and_valid' => [
            'data' => [
                'type'         => $faker->word,
                'name'         => $faker->word,
                'access_token' => $faker->uuid,
            ],
        ],
        'request_should_pass_when_all_required_data_is_provided_and_valid_for_AWS' => [
            'data' => [
                'type'         => ServerProviderTypeEnum::AWS,
                'name'         => $faker->word,
                'access_token' => $faker->uuid,
                'access_key'   => $faker->uuid,
            ],
        ],
    ];
}

function failScenarios(): array
{
    $faker = Factory::create();

    return [
        'request_should_fail_when_no_type_is_provided' => [
            'data'   => [
                'name'         => $faker->word,
                'access_token' => $faker->uuid,
            ],
            'errors' => [
                'type' => 'The type field is required.',
            ],
        ],
        'request_should_fail_when_the_type_is_too_long' => [
            'data' => [
                'type'         => str_repeat('x', 256),
                'name'         => $faker->word,
                'access_token' => $faker->uuid,
            ],
            'errors' => [
                'type' => 'The type may not be greater than 255 characters.',
            ],
        ],
        'request_should_fail_when_no_name_is_provided' => [
            'data'   => [
                'type'         => $faker->word,
                'access_token' => $faker->uuid,
            ],
            'errors' => [
                'name' => 'The name field is required.',
            ],
        ],
        'request_should_fail_when_the_name_is_too_long' => [
            'data' => [
                'type'         => $faker->word,
                'name'         => str_repeat('x', 256),
                'access_token' => $faker->uuid,
            ],
            'errors' => [
                'name' => 'The name may not be greater than 255 characters.',
            ],
        ],
        'request_should_fail_when_no_access_token_is_provided' => [
            'data'   => [
                'type' => $faker->word,
                'name' => $faker->word,
            ],
            'errors' => [
                'access_token' => 'The access token field is required.',
            ],
        ],
        'request_should_fail_when_the_access_token_is_too_long' => [
            'data' => [
                'type'         => $faker->word,
                'name'         => $faker->word,
                'access_token' => str_repeat('x', 256),
            ],
            'errors' => [
                'access_token' => 'The access token may not be greater than 255 characters.',
            ],
        ],
        'request_should_fail_when_no_access_key_is_provided_if_the_type_is_AWS' => [
            'data'   => [
                'type'         => ServerProviderTypeEnum::AWS,
                'name'         => $faker->word,
                'access_token' => $faker->uuid,
            ],
            'errors' => [
                'access_key' => 'The access key field is required when type is aws.',
            ],
        ],
    ];
};
