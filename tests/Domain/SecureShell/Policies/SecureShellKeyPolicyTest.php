<?php

declare(strict_types=1);

use Domain\SecureShell\Models\SecureShellKey;
use Domain\SecureShell\Policies\SecureShellKeyPolicy;

it('can determine if the user passes view', function () {
    $key = SecureShellKey::factory()->createForTest();

    expect((new SecureShellKeyPolicy())->view($key->user, $key))->toBeTrue();
    expect((new SecureShellKeyPolicy())->view($this->user(), $key))->toBeFalse();
});
