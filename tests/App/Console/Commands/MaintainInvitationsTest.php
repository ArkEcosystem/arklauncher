<?php

declare(strict_types=1);

use App\Console\Commands\MaintainInvitations;
use Carbon\Carbon;
use Domain\Collaborator\Models\Invitation;

it('removes expired invitations', function () {
    $invitation = Invitation::factory()->create(['created_at' => Carbon::now()->subWeek()]);

    $this->assertDatabaseHas('invitations', ['token_id' => $invitation->token_id, 'user_id' => $invitation->user_id]);
    $this->artisan(MaintainInvitations::class);
    $this->assertDatabaseMissing('invitations', ['token_id' => $invitation->token_id, 'user_id' => $invitation->user_id]);
});

it('keeps non expired invitations', function () {
    $invitation = Invitation::factory()->create();

    $this->assertDatabaseHas('invitations', ['token_id' => $invitation->token_id, 'user_id' => $invitation->user_id]);
    $this->artisan(MaintainInvitations::class);
    $this->assertDatabaseHas('invitations', ['token_id' => $invitation->token_id, 'user_id' => $invitation->user_id]);
});
