<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\SecureShell\Scripts\ManualScript;
use Domain\Token\Models\Network;
use Support\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InstallationScriptController extends Controller
{
    public function show(Network $network) : StreamedResponse
    {
        $script = new ManualScript($network->token, $network->name);

        return response()->streamDownload(function () use ($script) {
            echo $script->script();
        }, 'install.sh');
    }
}
