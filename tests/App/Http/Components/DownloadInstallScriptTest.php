<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use App\Http\Components\DownloadInstallScript;
use Carbon\Carbon;
use Domain\Server\Models\Server;
use Domain\Token\Models\Token;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('can handle scenario with no available networks', function () {
    $token = Token::factory()->create();

    Livewire::actingAs($token->user)
            ->test(DownloadInstallScript::class, ['token' => $token])
            ->assertSee('disabled')
            ->assertDontSee(NetworkTypeEnum::MAINNET)
            ->assertDontSee(NetworkTypeEnum::DEVNET)
            ->assertDontSee(NetworkTypeEnum::TESTNET);
});

it('can determine available networks for a token', function () {
    $server = Server::factory()->genesis()->createForTest(['provisioned_at' => Carbon::now()]);

    Livewire::actingAs($server->token->user)
            ->test(DownloadInstallScript::class, ['token' => $server->token])
            ->assertSee($server->network->name);
});

it('displays a toast and reports an internal exception if file does not exist', function () {
    Storage::fake('token-config');

    $server   = Server::factory()->genesis()->createForTest(['provisioned_at' => Carbon::now()]);
    $filePath = $server->network->configurationPath();

    Storage::disk('token-config')->assertMissing($filePath);

    Livewire::actingAs($server->token->user)
            ->test(DownloadInstallScript::class, ['token' => $server->token])
            ->assertSee($server->network->name)
            ->call('download', $server->network->name)
            ->assertEmitted('toastMessage');
});

it('can download the selected network version installation script', function () {
    Storage::fake('token-config');

    $server   = Server::factory()->genesis()->createForTest(['provisioned_at' => Carbon::now()]);
    $filePath = $server->network->configurationPath();

    UploadedFile::fake()->create('config.zip', 100, 'application/zip')->storeAs('', $filePath, 'token-config');

    Storage::disk('token-config')->assertExists($filePath);

    Livewire::actingAs($server->token->user)
            ->test(DownloadInstallScript::class, ['token' => $server->token])
            ->assertSee($server->network->name)
            ->call('download', $server->network->name)
            ->assertFileDownloaded('config-'.Str::lower($server->token->name).'-'.$server->network->name.'.zip');
});
