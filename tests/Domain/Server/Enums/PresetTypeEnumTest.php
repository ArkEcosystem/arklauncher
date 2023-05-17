<?php

declare(strict_types=1);

use Domain\Server\Enums\PresetTypeEnum;

it('a_preset_type_has_enums', function () {
    expect(PresetTypeEnum::GENESIS)->toBe('genesis');
    expect(PresetTypeEnum::SEED)->toBe('seed');
    expect(PresetTypeEnum::RELAY)->toBe('relay');
    expect(PresetTypeEnum::FORGER)->toBe('forger');
    expect(PresetTypeEnum::EXPLORER)->toBe('explorer');
});
