<?php

declare(strict_types=1);

use App\Server\Jobs\RemovePasswordsFromServer;
use Domain\Server\Models\Server;
use Illuminate\Support\Facades\Http;

it('checks if passwords are properly removed from database once server is deployed', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server'), 200, []),
        ]);

    $server = Server::factory()->digitalocean()->create([]);

    expect($server->user_password)->not()->toBeNull();
    expect($server->sudo_password)->not()->toBeNull();
    expect($server->delegate_passphrase)->not()->toBeNull();
    expect($server->delegate_password)->not()->toBeNull();

    (new RemovePasswordsFromServer($server))->handle();

    $this->assertDatabaseHas('servers', [
            'user_password'       => null,
            'sudo_password'       => null,
            'delegate_passphrase' => null,
            'delegate_password'   => null,
        ]);
});
