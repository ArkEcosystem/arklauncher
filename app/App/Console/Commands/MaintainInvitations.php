<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Collaborator\Models\Invitation;
use Illuminate\Console\Command;

final class MaintainInvitations extends Command
{
    protected $signature = 'invitations:maintain';

    protected $description = 'Check existing invitations for any expired ones and remove those.';

    public function handle(): void
    {
        Invitation::chunkById(5000, function ($invitations): void {
            foreach ($invitations->filter->isExpired() as $invitation) {
                $invitation->delete();
            }
        });
    }
}
