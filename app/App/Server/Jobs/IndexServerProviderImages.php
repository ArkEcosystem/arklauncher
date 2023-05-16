<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use App\Server\Notifications\IndexServerProviderImagesFailed;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

final class IndexServerProviderImages implements ShouldQueue
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
        $images = $this->serverProvider->client()->images()->items;

        DB::transaction(function () use ($images) {
            $serverProviderImages = $images->map(fn ($image) => ServerProviderImage::updateOrCreate(['uuid' => $image->id], [
                'uuid' => $image->id,
                'name' => $image->name,
            ]));

            $this->serverProvider->images()->sync($serverProviderImages->pluck('id')->unique());
        }, 5);
    }

    public function failed(): void
    {
        $this->serverProvider->token->user->notify(new IndexServerProviderImagesFailed($this->serverProvider));

        $serverProviderOwner = $this->serverProvider->user();

        if ($serverProviderOwner !== null && $serverProviderOwner->isNot($this->serverProvider->token->user) && $this->serverProvider->token->hasCollaborator($serverProviderOwner)) {
            $serverProviderOwner->notify(new IndexServerProviderImagesFailed($this->serverProvider));
        }
    }

    public function tags(): array
    {
        return ['images', 'serverProvider:'.$this->serverProvider->id];
    }
}
