<?php

declare(strict_types=1);

return [
    // Used to make _local_ deployments work with signed routes, it otherwise fails to verify the signature
    'proxies' => env('APP_ENV', 'production') === 'local' ? '*' : [],
];
