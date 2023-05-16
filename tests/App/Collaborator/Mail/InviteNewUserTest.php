<?php

declare(strict_types=1);

use App\Collaborator\Mail\InviteNewUser;
use Domain\Collaborator\Models\Invitation;
use Illuminate\Support\Facades\Mail;

it('sends the mail to the invited user', function () {
    $user       = $this->user();
    $invitation = Invitation::factory()->create();

    Mail::to($user)->send(new InviteNewUser($invitation));

    Mail::assertQueued(InviteNewUser::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

it('builds the mail with the correct subject', function () {
    $invitation = Invitation::factory()->create();

    $mail = new InviteNewUser($invitation);

    expect($mail->build()->subject)->toBe(trans('mails.subjects.new_invitation'));
});
