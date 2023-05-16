<?php

declare(strict_types=1);

use App\Token\Components\ManageTokenSecureShellKeys;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Server\Models\Server;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Livewire\Livewire;

it('can list the user ssh keys', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSee($key->name)
            ->assertSet('selectedOptions', []);
});

it('can select a single key', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSee($key->name)
            ->assertSet('selectedOptions', [])
            ->call('selectOption', $key->id)
            ->assertSet('selectedOptions', [$key->id]);
});

it('can select multiple keys', function () {
    $user = $this->user();

    $firstKey  = SecureShellKey::factory()->ownedBy($user)->createForTest();
    $secondKey = SecureShellKey::factory()->ownedBy($user)->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSee($firstKey->name)
            ->assertSee($secondKey->name)
            ->assertSet('selectedOptions', [])
            ->call('selectOption', $firstKey->id)
            ->call('selectOption', $secondKey->id)
            ->assertSet('selectedOptions', [$firstKey->id, $secondKey->id]);
});

it('clicking on a selected key should remove it from the selected keys', function () {
    $user = $this->user();

    $firstKey  = SecureShellKey::factory()->ownedBy($user)->createForTest();
    $secondKey = SecureShellKey::factory()->ownedBy($user)->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSee($firstKey->name)
            ->assertSee($secondKey->name)
            ->assertSet('selectedOptions', [])
            ->call('selectOption', $firstKey->id)
            ->call('selectOption', $secondKey->id)
            ->assertSet('selectedOptions', [$firstKey->id, $secondKey->id])
            ->call('selectOption', $firstKey->id)
            ->call('selectOption', $secondKey->id)
            ->assertSet('selectedOptions', []);
});

it('should store the selected key', function () {
    $user = $this->user();

    $key  = SecureShellKey::factory()->ownedBy($user)->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    $this->assertDatabaseMissing('secure_shell_key_token', ['token_id' => $token->id, 'secure_shell_key_id' => $key->id]);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSee($key->name)
            ->assertSet('selectedOptions', [])
            ->call('selectOption', $key->id)
            ->assertSet('selectedOptions', [$key->id])
            ->call('store');

    $this->assertDatabaseHas('secure_shell_key_token', ['token_id' => $token->id, 'secure_shell_key_id' => $key->id]);
});

it('select option should push add a new option to the selected options', function () {
    $user = $this->user();

    $key  = SecureShellKey::factory()->ownedBy($user)->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    $realComponent = new ManageTokenSecureShellKeys('1');
    $realComponent->mount($token);

    expect($realComponent->selectedOptions)->toBeEmpty();

    $realComponent->selectOption($key->id);

    expect($realComponent->selectedOptions)->toHaveCount(1);
});

it('selecting the same option twice should remove it from the selected options', function () {
    $user = $this->user();

    $key  = SecureShellKey::factory()->ownedBy($user)->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    $realComponent = new ManageTokenSecureShellKeys('1');
    $realComponent->mount($token);

    expect($realComponent->selectedOptions)->toBeEmpty();

    $realComponent->selectOption($key->id);

    expect($realComponent->selectedOptions)->toHaveCount(1);

    $realComponent->selectOption($key->id);

    expect($realComponent->selectedOptions)->toBeEmpty();
});

it('select all should add all available options to the selected options', function () {
    $user = $this->user();

    SecureShellKey::factory()->ownedBy($user)->createForTest();
    SecureShellKey::factory()->ownedBy($user)->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    $realComponent = new ManageTokenSecureShellKeys('1');
    $realComponent->mount($token);

    expect($realComponent->selectedOptions)->toBeEmpty();

    $realComponent->selectAll();

    expect($realComponent->selectedOptions)->toHaveCount(2);
});

it('deselect all should remove everything from selected options', function () {
    $user = $this->user();

    SecureShellKey::factory()->ownedBy($user)->createForTest();
    SecureShellKey::factory()->ownedBy($user)->createForTest();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    $realComponent = new ManageTokenSecureShellKeys('1');
    $realComponent->mount($token);

    expect($realComponent->selectedOptions)->toBeEmpty();

    $realComponent->selectAll();

    expect($realComponent->selectedOptions)->toHaveCount(2);

    $realComponent->deselectAll();

    expect($realComponent->selectedOptions)->toBeEmpty();
});

it('selected options should return an array', function () {
    $user = $this->user();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    $realComponent = new ManageTokenSecureShellKeys('1');
    $realComponent->mount($token);

    expect($realComponent->selectedOptions)->toBeArray();
});

it('should show a key as already checked if present in the selected options', function () {
    $user = $this->user();

    $token = Token::factory()->ownedBy($user)->createForTest();

    $this->actingAs($user);

    $realComponent = new ManageTokenSecureShellKeys('1');
    $realComponent->mount($token);

    $key = SecureShellKey::factory()->ownedBy($user)->createForTest();

    expect($realComponent->isRegistered($key->id))->toBeFalse();

    array_push($realComponent->selectedOptions, $key->id);

    expect($realComponent->isRegistered($key->id))->toBeTrue();
});

it('fails to store the ssh key if no data is provided', function () {
    $token = Token::factory()->createForTest();

    $this->actingAs($token->user);

    Livewire::actingAs($token->user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->set('name', null)
            ->set('public_key', null)
            ->call('storeKey')
            ->assertHasErrors([
                'name'        => 'required',
                'public_key'  => 'required',
            ]);
});

it('fails to store the ssh key if the public key is invalid', function () {
    $token = Token::factory()->createForTest();

    $this->actingAs($token->user);

    Livewire::actingAs($token->user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->set('name', $this->faker->name)
            ->set('public_key', 'invalid')
            ->call('storeKey')
            ->assertHasErrors(['public_key']);
});

it('can store a new ssh key', function () {
    $token = Token::factory()->createForTest();

    $this->actingAs($token->user);

    $name = $this->faker->name;

    $this->assertDatabaseMissing('secure_shell_keys', compact('name'));

    Livewire::actingAs($token->user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->set('name', $name)
            ->set('public_key', 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSUGPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XAt3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/EnmZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbxNrRFi9wrf+M7Q== schacon@mylaptop.local')
            ->call('storeKey');

    $this->assertDatabaseHas('secure_shell_keys', compact('name'));
});

it('cannot store a new ssh key if duplicate name for authenticated user', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $token = Token::factory()->createForTest();

    $this->actingAs($user);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->set('name', $key->name)
            ->set('public_key', 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSUGPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XAt3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/EnmZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbxNrRFi9wrf+M7Q== schacon@mylaptop.local')
            ->call('storeKey')
            ->assertHasErrors(['name' => 'unique']);
});

it('cannot store a new ssh key if duplicate public key for authenticated user', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $token = Token::factory()->createForTest();

    $this->actingAs($user);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->set('name', $this->faker->name)
            ->set('public_key', $key->public_key)
            ->call('storeKey')
            ->assertHasErrors(['public_key' => 'unique']);
});

it('can store a new ssh key if duplicate name for different user', function () {
    $firstUser  = User::factory()->create();
    $secondUser = User::factory()->create();

    $existingKey = SecureShellKey::factory()->ownedBy($firstUser)->createForTest();

    $token = Token::factory()->createForTest();

    $this->actingAs($secondUser);

    $this->assertDatabaseMissing('secure_shell_keys', ['user_id' => $secondUser->id, 'name' => $existingKey->name]);

    Livewire::actingAs($secondUser)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->set('name', $existingKey->name)
            ->set('public_key', 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSUGPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XAt3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/EnmZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbxNrRFi9wrf+M7Q== schacon@mylaptop.local')
            ->call('storeKey');

    $this->assertDatabaseHas('secure_shell_keys', ['user_id' => $secondUser->id, 'name' => $existingKey->name]);
});

it('can store a new ssh key if duplicate public key for different user', function () {
    $firstUser  = User::factory()->create();
    $secondUser = User::factory()->create();

    $existingKey = SecureShellKey::factory()->ownedBy($firstUser)->createForTest();

    $token = Token::factory()->createForTest();

    $this->actingAs($secondUser);

    $this->assertDatabaseMissing('secure_shell_keys', ['user_id' => $secondUser->id, 'public_key' => $existingKey->public_key]);

    Livewire::actingAs($secondUser)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->set('name', $this->faker->name)
            ->set('public_key', $existingKey->public_key)
            ->call('storeKey');

    $this->assertDatabaseHas('secure_shell_keys', ['user_id' => $secondUser->id, 'public_key' => $existingKey->public_key]);
});

it('can handle the modal', function () {
    $token = Token::factory()->createForTest();

    SecureShellKey::factory()->ownedBy($token->user)->createForTest();

    Livewire::actingAs($token->user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSet('name', null)
            ->assertSet('public_key', null)
            ->assertSet('modalShown', false)
            ->call('toggleModal')
            ->assertSet('modalShown', true)
            ->assertSee(trans('pages.user-settings.create_ssh_title'))
            ->call('toggleModal')
            ->assertDontSee(trans('pages.user-settings.create_ssh_title'));
});

it('cannot see the modal if no user keys', function () {
    $user  = $this->user();
    $token = Token::factory()->createForTest();

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSet('name', null)
            ->assertSet('public_key', null)
            ->assertSet('modalShown', false)
            ->call('toggleModal')
            ->assertSet('modalShown', true)
            ->assertDontSee(trans('pages.user-settings.create_ssh_title'));
});

it('cannot see the keys management list if no user keys', function () {
    $user  = $this->user();
    $token = Token::factory()->createForTest();

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSet('name', null)
            ->assertSet('public_key', null)
            ->assertDontSee(trans('actions.select_all'));
});

it('should not open a modal after creating the first key', function () {
    $user  = $this->user();
    $token = Token::factory()->createForTest();

    $name = $this->faker->name;

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $token])
            ->assertSet('name', null)
            ->assertSet('public_key', null)
            ->assertDontSee(trans('actions.select_all'))
            ->set('name', $name)
            ->assertSet('name', $name)
            ->set('public_key', 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSUGPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XAt3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/EnmZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbxNrRFi9wrf+M7Q== schacon@mylaptop.local')
            ->assertSet('public_key', 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSUGPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XAt3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/EnmZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbxNrRFi9wrf+M7Q== schacon@mylaptop.local')
            ->set('modalShown', true)
            ->call('storeKey')
            ->assertSet('modalShown', false);
});

it('cannot add keys on the page if the token has completed onboarding', function () {
    $server = Server::factory()->createForTest();

    SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();

    Livewire::actingAs($server->token->user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
            ->assertSet('name', null)
            ->assertSet('public_key', null)
            ->assertSet('modalShown', false)
            ->assertSee('settings page');
});

it('handles changes in both selected and tokenkeys', function () {
    $server = Server::factory()->createForTest();

    $key1 = SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();
    $key2 = SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();

    $server->token->secureShellKeys()->sync([$key1->id]);

    // Test that adding keys is allowed
    Livewire::actingAs($server->token->user)
        ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
        ->set('selectedOptions', [$key1->id])
        ->assertSee('disabled')
        ->set('selectedOptions', [$key1->id, $key2->id])
        ->assertDontSee('disabled');

    $server->token->secureShellKeys()->sync([$key1->id, $key2->id]);

    // Test that removing keys is alowed
    Livewire::actingAs($server->token->user)
        ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
        ->set('selectedOptions', [$key1->id, $key2->id])
        ->assertSee('disabled')
        ->set('selectedOptions', [$key1->id])
        ->assertDontSee('disabled');
});

it('does not allow setting 0 keys', function () {
    $server = Server::factory()->createForTest();

    SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();

    Livewire::actingAs($server->token->user)
        ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
        ->set('selectedOptions', [1])
        ->assertDontSee('disabled')
        ->set('selectedOptions', [])
        ->assertSee('disabled');
});

it('prevents users without permission to select which ssh keys can access the blockchain', function () {
    $server = Server::factory()->createForTest();

    SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();

    $user = User::factory()->create();

    $server->token->shareWith($user, 'collaborator', [
        'server-provider:create',
    ]);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
            ->call('selectOption', 0)
            ->assertForbidden();
});

it('allows users with sufficient permission to select which SSH keys can access the blockchain', function () {
    $server = Server::factory()->createForTest();

    SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();

    $user = User::factory()->create();

    $server->token->shareWith($user, 'collaborator', [
        'ssh-key:manage',
    ]);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
            ->call('selectOption', 0)
            ->assertOk();
});

it('prevents users without permission to select and deselect all SSH keys', function () {
    $server = Server::factory()->createForTest();

    SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();

    $user = User::factory()->create();

    $server->token->shareWith($user, 'collaborator', [
        'server-provider:create',
    ]);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
            ->call('selectAll')
            ->assertForbidden()
            ->call('deselectAll')
            ->assertForbidden();
});

it('allows users with sufficient permission to select and deselect all SSH keys', function () {
    $server = Server::factory()->createForTest();

    SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();

    $user = User::factory()->create();

    $server->token->shareWith($user, 'collaborator', [
        'ssh-key:manage',
    ]);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
            ->call('selectAll')
            ->assertOk()
            ->call('deselectAll')
            ->assertOk();
});

it('prevents users without permission to save changes', function () {
    $server = Server::factory()->createForTest();

    SecureShellKey::factory()->ownedBy($server->token->user)->createForTest();

    $user = User::factory()->create();

    $server->token->shareWith($user, 'collaborator', [
        'server-provider:create',
    ]);

    Livewire::actingAs($user)
            ->test(ManageTokenSecureShellKeys::class, ['token' => $server->token])
            ->call('store')
            ->assertForbidden();
});
