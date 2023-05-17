<?php

declare(strict_types=1);

namespace Domain\Token\Enums;

final class TokenOnboardingStatusEnum
{
    public const CONFIGURATION = 'configuration';

    public const SERVER_PROVIDERS = 'server_providers';

    public const SERVER_CONFIGURATION = 'server_config';

    public const SECURE_SHELL_KEYS = 'secure_shell_keys';

    public const COLLABORATORS = 'collaborators';

    public const SERVERS = 'servers';

    public static function all(): array
    {
        return [
            static::CONFIGURATION,
            static::SERVER_PROVIDERS,
            static::SERVER_CONFIGURATION,
            static::SECURE_SHELL_KEYS,
            static::COLLABORATORS,
            static::SERVERS,
        ];
    }
}
