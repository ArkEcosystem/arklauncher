<?php

declare(strict_types=1);

use App\SecureShell\Components\CreateSecureShellKey;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\SecureShell\Rules\SecureShellKey as Rule;
use Domain\User\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('fails to store the ssh key if no data is provided', function () {
    $this->actingAs($this->user());

    Livewire::test(CreateSecureShellKey::class)
            ->set('name', null)
            ->set('public_key', null)
            ->call('store')
            ->assertHasErrors([
                'name'        => 'required',
                'public_key'  => 'required',
            ]);
});

it('fails to store the ssh key if the public key is invalid', function () {
    $this->actingAs($this->user());

    Livewire::test(CreateSecureShellKey::class)
            ->set('name', $this->faker->name)
            ->set('public_key', 'invalid')
            ->call('store')
            ->assertHasErrors(['public_key' => Str::snake(Rule::class)]);
});

it('can store a new ssh key', function () {
    $this->actingAs($this->user());

    Livewire::test(CreateSecureShellKey::class)
            ->set('name', $name = $this->faker->name)
            ->set('public_key', 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSUGPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XAt3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/EnmZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbxNrRFi9wrf+M7Q== schacon@mylaptop.local')
            ->call('store');

    $this->assertDatabaseHas('secure_shell_keys', compact('name'));
});

it('cannot store a new ssh key if duplicate name for authenticated user', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $this->actingAs($user);

    Livewire::test(CreateSecureShellKey::class)
            ->set('name', $key->name)
            ->set('public_key', 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSUGPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XAt3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/EnmZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbxNrRFi9wrf+M7Q== schacon@mylaptop.local')
            ->call('store')
            ->assertHasErrors(['name' => 'unique']);
});

it('cannot store a new ssh key if duplicate public key for authenticated user', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $this->actingAs($user);

    Livewire::test(CreateSecureShellKey::class)
            ->set('name', $name = $this->faker->name)
            ->set('public_key', $key->public_key)
            ->call('store')
            ->assertHasErrors(['public_key' => 'unique']);
});

it('can store a new ssh key if duplicate name for different user', function () {
    $firstUser  = User::factory()->create();
    $secondUser = User::factory()->create();

    $existingKey = SecureShellKey::factory()->ownedBy($firstUser)->createForTest();

    $this->actingAs($secondUser);

    Livewire::test(CreateSecureShellKey::class)
            ->set('name', $existingKey->name)
            ->set('public_key', 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSUGPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XAt3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/EnmZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbxNrRFi9wrf+M7Q== schacon@mylaptop.local')
            ->call('store');

    $this->assertDatabaseHas('secure_shell_keys', ['user_id' => $secondUser->id, 'name' => $existingKey->name]);
});

it('can store a new ssh key if duplicate public key for different user', function () {
    $firstUser  = User::factory()->create();
    $secondUser = User::factory()->create();

    $existingKey = SecureShellKey::factory()->ownedBy($firstUser)->createForTest();

    $this->actingAs($secondUser);

    Livewire::test(CreateSecureShellKey::class)
            ->set('name', $this->faker->name)
            ->set('public_key', $existingKey->public_key)
            ->call('store');

    $this->assertDatabaseHas('secure_shell_keys', ['user_id' => $secondUser->id, 'public_key' => $existingKey->public_key]);
});
