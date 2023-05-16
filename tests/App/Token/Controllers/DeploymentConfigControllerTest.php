<?php

declare(strict_types=1);

use Domain\Token\Models\Network;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

it('receives an encrypted file and store it with his original name on a folder with the network and token id', function () {
    Storage::fake('token-config');

    $network = Network::factory()->createForTest();

    $file = UploadedFile::fake()->create('config.zip', 15, 'application/zip');

    $route = URL::temporarySignedRoute('server.deployment.config.store', now()->addMinutes(1), $network);

    $response = $this->post($route, ['file' => $file]);

    $response->assertSuccessful();

    Storage::disk('token-config')->assertExists($network->configurationPath());
});

it('return the config file from the server', function () {
    Storage::fake('token-config');

    $network = Network::factory()->createForTest();

    $file = UploadedFile::fake()->create('config.zip', 15, 'application/zip');

    $filePath = $network->configurationPath();

    $file->storeAs('', $filePath, 'token-config');

    $route = URL::temporarySignedRoute('server.deployment.config.show', now()->addMinutes(1), $network);

    $response = $this->get($route);

    $response->assertSuccessful();

    $response->assertHeader('content-type', 'application/zip');
    $response->assertHeader('content-disposition', 'attachment; filename=config.zip');
});

it('return a 404 error is the config file is not stored yet', function () {
    Storage::fake('token-config');

    $network = Network::factory()->createForTest();

    $route = URL::temporarySignedRoute('server.deployment.config.show', now()->addMinutes(1), $network);

    $response = $this->get($route);

    $response->assertNotFound();
});
