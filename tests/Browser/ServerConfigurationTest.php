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
final class ServerConfigurationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_fill_the_server_configuration(): void
    {
        $this->browse(function (Browser $browser) {
            $token = Token::factory()->withOnboardingServerProvider()->createForTest();

            $serverProvider = $token->serverProviders()->first();

            $region = $serverProvider->regions()->first();
            $plan   = $serverProvider->plans()->first();

            $browser
                ->loginAs($token->user)
                ->visit(route('tokens.server-configuration', $token))
                ->waitForRoute('tokens.server-configuration', $token)
                ->type('serverName', 'MyServer')
                ->assertSelectHasOption('region', $region->id)
                ->select('region', $region->id)
                ->waitForLivewire()
                ->pause(500)
                ->assertSelectHasOption('plan', $plan->id)
                ->select('plan', $plan->id)
                ->click('#server-digitalocean')
                ->assertInputValue('provider', 'digitalocean')
                ->assertEnabled('@save-and-continue');
        });
    }
}
