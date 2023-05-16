<?php

declare(strict_types=1);

namespace Tests\Browser;

use Domain\Token\Models\Token;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * @coversNothing
 */
final class OnboardingStepsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_press_the_add_new_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->withUser()
                ->visit(route('tokens'))
                ->press('#create-token')
                ->waitForRoute('tokens.create')
                ->assertRouteIs('tokens.create');
        });
    }

    /** @test */
    public function can_press_the_customize_blockchain_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->withUser()
                ->visit(route('tokens.create'))
                ->assertSee(__('tokens.onboarding.page_header'));

            // Recently created token
            $token = Token::latest()->first();

            $browser
                ->click('#onboard-step-configuration')
                ->waitForRoute('tokens.edit', $token)
                ->assertRouteIs('tokens.edit', $token);
        });
    }
}
