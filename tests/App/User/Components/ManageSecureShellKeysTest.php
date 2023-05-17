<?php

declare(strict_types=1);

use App\User\Components\ManageSecureShellKeys;
use Domain\SecureShell\Models\SecureShellKey;
use Livewire\Livewire;

it('can list the ssh keys', function () {
    $key = SecureShellKey::factory()->ownedBy($user = $this->user())->createForTest();

    $this->actingAs($user);

    Livewire::test(ManageSecureShellKeys::class)->assertSee($key->name);
});
