<?php

declare(strict_types=1);

namespace App\Server\Controllers;

use Domain\Server\Enums\ServerDeploymentStatus;
use Domain\Server\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ReflectionClass;
use Support\Http\Controllers\Controller;

final class DeploymentStatusController extends Controller
{
    public function __invoke(Server $server, Request $request): Response
    {
        if (! $request->filled('version')) {
            $status = $request->input('status');

            $statuses = $this->getAllDeploymentStatuses();
            if (in_array($status, $statuses, true)) {
                $server->setStatus($status);
            }

            if (Str::startsWith($status, 'failed')) {
                $this->handleFailedStatus($server, $status);
            }
        } else {
            $server->fill([
                'core_version' => $request->version,
            ])->save();
        }

        // If any changes to any server happen we want to flush the token cache
        // to ensure that checks like deployments of certain types run against
        // fresh data to avoid data corruption or outdated UI feedback for users.
        $server->token->flushCache();

        return response()->noContent();
    }

    public function getAllDeploymentStatuses() : array
    {
        $serverDeploymentStatus = new ReflectionClass(new ServerDeploymentStatus());

        return $serverDeploymentStatus->getConstants();
    }

    private function handleFailedStatus(Server $server, string $status): void
    {
        Log::warning('Missing case for failed deployment status: "'.$status.'"');

        $server->setStatus('failed');
    }
}
