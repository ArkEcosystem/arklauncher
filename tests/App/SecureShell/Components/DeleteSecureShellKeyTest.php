<?php

declare(strict_types=1);

use App\SecureShell\Components\DeleteSecureShellKey;
use Domain\SecureShell\Models\SecureShellKey;
use Livewire\Livewire;

it('can ask for confirmation and set the key id', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $this->actingAs($user);

    Livewire::test(DeleteSecureShellKey::class)
            ->assertSet('keyId', null)
            ->call('askForConfirmation', $key->id)
            ->assertSet('keyId', $key->id);
});

it('can cancel the confirmation', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $this->actingAs($user);

    $this->assertDatabaseHas('secure_shell_keys', ['id' => $key->id]);

    Livewire::test(DeleteSecureShellKey::class)
            ->assertSet('keyId', null)
            ->call('askForConfirmation', $key->id)
            ->assertSet('keyId', $key->id)
            ->call('cancel')
            ->assertSet('keyId', null);

    $this->assertDatabaseHas('secure_shell_keys', ['id' => $key->id]);
});

it('can destroy the key', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $this->actingAs($user);

    $this->assertDatabaseHas('secure_shell_keys', ['id' => $key->id]);

    Livewire::test(DeleteSecureShellKey::class)
            ->assertSet('keyId', null)
            ->call('askForConfirmation', $key->id)
            ->assertSet('keyId', $key->id)
            ->call('destroy')
            ->assertSet('keyId', null);

    $this->assertDatabaseMissing('secure_shell_keys', ['id' => $key->id]);
});
