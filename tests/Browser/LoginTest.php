<?php

declare(strict_types=1);

namespace Tests\Browser;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * @coversNothing
 */
final class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_user_can_login_with_a_valid_email_and_password(): void
    {
        $password = 'aA1$aA1$aA1$';

        $user = User::factory()->create([
            'email'    => 'john@doe.com',
            'password' => Hash::make($password),
        ]);

        $this->browse(function (Browser $browser) use ($user, $password) {
            $browser
                ->visit(route('login'))
                ->waitForLocation('/login')
                ->type('email', $user->email)
                ->type('password', $password)
                ->press('Sign In')
                ->waitForLocation('/app/tokens')
                ->assertRouteIs('tokens');
        });
    }
}
