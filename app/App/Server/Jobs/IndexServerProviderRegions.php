<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use App\Server\Notifications\IndexServerProviderRegionsFailed;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderRegion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

final class IndexServerProviderRegions implements ShouldQueue
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
        $regions = $this->serverProvider->client()->regions()->items;

        DB::transaction(function () use ($regions) {
            $serverProviderRegions = $regions->map(fn ($region) => ServerProviderRegion::updateOrCreate(['uuid' => $region->id], [
                'uuid' => $region->id,
                'name' => $region->name,
            ]));

            $this->serverProvider->regions()->sync($serverProviderRegions->pluck('id')->unique());
        }, 5);
    }

    public function failed(): void
    {
        $this->serverProvider->token->user->notify(new IndexServerProviderRegionsFailed($this->serverProvider));

        $serverProviderOwner = $this->serverProvider->user();

        if ($serverProviderOwner !== null && $serverProviderOwner->isNot($this->serverProvider->token->user) && $this->serverProvider->token->hasCollaborator($serverProviderOwner)) {
            $serverProviderOwner->notify(new IndexServerProviderRegionsFailed($this->serverProvider));
        }
    }

    public function tags(): array
    {
        return ['regions', 'serverProvider:'.$this->serverProvider->id];
    }
}
