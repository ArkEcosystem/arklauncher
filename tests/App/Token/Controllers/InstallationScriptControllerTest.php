<?php

declare(strict_types=1);

use Domain\SecureShell\Scripts\ManualScript;
use Domain\Server\Models\Server;
use Domain\Token\Models\Network;
use Illuminate\Support\Facades\URL;

it('can generate installation script for the network', function () {
    $network = Network::factory()->createForTest();
    $server  = Server::factory()->genesis()->create([
        'provisioned_at' => now(),
        'ip_address'     => '127.0.0.1',
        'network_id'     => $network->id,
    ]);

    $route = URL::temporarySignedRoute('server.deployment.installation-script.show', now()->addMinutes(1), $network);

    $response = $this->get($route);

    $response->assertDownload('install.sh');

    expect($response->streamedContent())->toBe(
        (new ManualScript($network->token, $network->name))->script()
    );
});
