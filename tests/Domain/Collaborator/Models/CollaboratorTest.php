<?php

declare(strict_types=1);

use Domain\Collaborator\Models\Collaborator;

it('a collaborator has a set of available permissions', function () {
    expect(Collaborator::availablePermissions())->toBeArray();
});
