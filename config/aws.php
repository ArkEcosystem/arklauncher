<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration options set in this file will be passed directly to the
    | `Aws\Sdk` object, from which all client objects are created. This file
    | is published to the application config directory for modification by the
    | user. The full set of possible options are documented at:
    | http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
    |
    */

    'region'   => env('AWS_REGION', 'us-east-1'),
    'version'  => 'latest',
    'imageIds' => [
        'ubuntu-18-04-lts' => 'ami-04b9e92b5572fa0d1',
    ],
];
