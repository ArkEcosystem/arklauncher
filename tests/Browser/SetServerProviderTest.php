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
final class SetServerProviderTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_select_a_sever_provider(): void
    {
        $this->browse(function (Browser $browser) {
            $token = Token::factory()->createForTest();

            $browser
                ->loginAs($token->user)
                ->visit(route('tokens.server-providers', $token))
                ->waitForRoute('tokens.server-providers', $token)
                ->type('name', 'MyServer')
                ->type('access_token', 'ABC')
                ->click('#server-digitalocean')
                ->assertInputValue('type', 'digitalocean')
                ->click('#server-hetzner')
                ->waitForLivewire()
                ->pause(500)
                ->assertInputValue('type', 'hetzner');
        });
    }
}
