<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use App\Server\Notifications\IndexServerProviderPlansFailed;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

final class IndexServerProviderPlans implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected ServerProvider $serverProvider)
    {
    }

    public function handle(): void
    {
        $plans = $this->serverProvider->client()->plans()->items;

        DB::transaction(function () use ($plans) {
            $serverProviderPlans = $plans->map(fn ($plan) => ServerProviderPlan::updateOrCreate(['uuid' => $plan->id], [
                'uuid'    => $plan->id,
                'disk'    => $plan->disk,
                'memory'  => $plan->memory,
                'cores'   => $plan->cores,
                'regions' => $plan->regions,
            ]));

            $this->serverProvider->plans()->sync($serverProviderPlans->pluck('id')->unique());
        }, 5);
    }

    public function failed(): void
    {
        $this->serverProvider->token->user->notify(new IndexServerProviderPlansFailed($this->serverProvider));

        $serverProviderOwner = $this->serverProvider->user();

        if ($serverProviderOwner !== null && $serverProviderOwner->isNot($this->serverProvider->token->user) && $this->serverProvider->token->hasCollaborator($serverProviderOwner)) {
            $serverProviderOwner->notify(new IndexServerProviderPlansFailed($this->serverProvider));
        }
    }

    public function tags(): array
    {
        return ['plans', 'serverProvider:'.$this->serverProvider->id];
    }
}
