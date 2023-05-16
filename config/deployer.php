<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | OpenSSH
    |--------------------------------------------------------------------------
    */

    'openssh' => [
        'length'  => env('OPENSSH_KEY_LENGTH', 4096),
        'name'    => env('OPENSSH_KEY_NAME', 'ark-deployer-temporary-key'),
    ],
    'branch' => env('DEPLOYER_BRANCH', 'master'),

    /*
    |--------------------------------------------------------------------------
    | Deployment Options
    |--------------------------------------------------------------------------
    */
    'deployment' => [
        'minimumServerRam' => env('DEPLOYMENT_GENESIS_MIN_RAM', 2048),
        'minimumCores'     => env('DEPLOYMENT_EXPLORER_MIN_CPU', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Server Providers
    |--------------------------------------------------------------------------
    */
    'server_providers' => [
        App\Enums\ServerProviderTypeEnum::DIGITALOCEAN => true,
        App\Enums\ServerProviderTypeEnum::HETZNER      => true,
        // App\Enums\ServerProviderTypeEnum::VULTR        => false,
        // App\Enums\ServerProviderTypeEnum::AWS          => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Wheter or not show the cookie consent pop up
    |--------------------------------------------------------------------------
    */
    'enable_cookieconsent' => env('ENABLE_COOKIECONSENT', true),
];
