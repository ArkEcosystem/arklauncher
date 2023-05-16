<?php

declare(strict_types=1);

namespace Tests\Browser;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * @coversNothing
 */
final class RegisterTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_user_can_register(): void
    {
        $password = 'aA1$aA1$aA1$';

        $user = User::factory()->make();

        $this->browse(function (Browser $browser) use ($user, $password) {
            $browser
                ->visit(route('register'))
                ->waitForLocation('/register')
                ->type('name', $user->name)
                ->pause(500)->type('email', $user->email)
                ->pause(500)->type('password', $password)
                ->pause(500)->type('password_confirmation', $password)
                ->pause(500)->check('terms')
                ->press(__('actions.sign_up'))
                ->assertRouteIs('verification.notice')
                ->assertSee(__('auth.verify.page_header'));
        });
    }

    /** @test */
    public function user_without_verified_emails_is_redirected_to_verify_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->loginAs($user)
                ->visit(route('tokens.create'))
                ->waitForRoute('verification.notice')
                ->assertRouteIs('verification.notice')
                ->assertSee(__('auth.verify.page_header'));
        });
    }
}
