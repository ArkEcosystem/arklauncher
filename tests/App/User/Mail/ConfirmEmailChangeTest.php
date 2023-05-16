<?php

declare(strict_types=1);

use App\User\Mail\ConfirmEmailChange;
use Illuminate\Support\Str;

it('generates the confirmation URL', function () {
    $mail = new ConfirmEmailChange('john@example.com', 'John Doe');

    expect(Str::is(
        route('user.profile', ['email' => 'john@example.com']).'&expires=*&signature=*',
        $mail->build()->viewData['url']
    ))->toBeTrue();
});
