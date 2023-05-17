<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use App\Token\Requests\DeploymentConfigRequest;
use Domain\Token\Models\Network;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Support\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DeploymentConfigController extends Controller
{
    public function show(Network $network): Response|StreamedResponse
    {
        $path = $network->configurationPath();

        if (! Storage::disk('token-config')->exists($path)) {
            report(new RuntimeException('Trying to retrieve config.zip file for the token [ID: '.$network->token->id.'] but the file does not exist.'));

            return response('File not found!', 404);
        }

        return Storage::disk('token-config')->download($path, 'config.zip');
    }

    public function store(Network $network, DeploymentConfigRequest $request): Response
    {
        $request->file->storeAs('', $network->configurationPath(), 'token-config');

        return response()->noContent();
    }
}
