<?php

declare(strict_types=1);

use Domain\Server\Enums\ServerTaskStatusEnum;

it('a server task status has enums', function () {
    expect(ServerTaskStatusEnum::PENDING)->toBe('pending');
    expect(ServerTaskStatusEnum::RUNNING)->toBe('running');
    expect(ServerTaskStatusEnum::FAILED)->toBe('failed');
    expect(ServerTaskStatusEnum::FINISHED)->toBe('finished');
    expect(ServerTaskStatusEnum::TIMEOUT)->toBe('timeout');
});
