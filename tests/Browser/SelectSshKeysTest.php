<?php

declare(strict_types=1);

namespace Tests\Browser;

use Domain\SecureShell\Models\SecureShellKey;
use Domain\Token\Models\Token;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * @coversNothing
 */
final class SelectSshKeysTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_add_a_new_ssh_key(): void
    {
        $this->browse(function (Browser $browser) {
            $token    = Token::factory()->withOnboardingServerConfiguration()->createForTest();
            $shellKey = SecureShellKey::factory()->make();

            $browser
                ->loginAs($token->user)
                ->visit(route('tokens.ssh-keys', $token))
                ->waitForRoute('tokens.ssh-keys', $token)
                ->type('name', 'Macbook')
                ->type('public_key', $shellKey->public_key)
                ->click('@save-and-continue')
                ->waitFor('label[for="selected_option-1"]')
                ->assertSeeIn('[for="selected_option-1"]', 'Macbook')
                ->assertPresent('input#selected_option-1');
        });
    }

    /** @test */
    public function can_add_another_ssh_key(): void
    {
        $this->browse(function (Browser $browser) {
            $token = Token::factory()->withOnboardingServerConfiguration()->createForTest();
            SecureShellKey::factory()->ownedBy($token->user)->createForTest(['public_key' => 'wharever']);
            $shellKey = SecureShellKey::factory()->make();

            $browser
                ->loginAs($token->user)
                ->visit(route('tokens.ssh-keys', $token))
                ->waitForRoute('tokens.ssh-keys', $token)
                ->press(__('tokens.secure-shell-keys.add_new_ssh_key'))
                ->waitFor('input#name')
                ->type('name', 'Macbook')
                ->type('public_key', $shellKey->public_key)
                ->press(__('actions.create'))
                ->waitUntilMissing('input#name')
                ->assertSeeIn('[for="selected_option-2"]', 'Macbook')
                ->assertPresent('input#selected_option-2');
        });
    }

    /** @test */
    public function can_select_the_ssh_keys(): void
    {
        $this->browse(function (Browser $browser) {
            $token = Token::factory()->withOnboardingServerConfiguration()->createForTest();

            SecureShellKey::factory()->ownedBy($token->user)->createForTest(['public_key' => 'wharever']);
            SecureShellKey::factory()->ownedBy($token->user)->createForTest(['public_key' => 'wharever2']);

            $browser
                ->loginAs($token->user)
                ->visit(route('tokens.ssh-keys', $token))
                ->waitForRoute('tokens.ssh-keys', $token)
                ->check('input#selected_option-2')
                ->check('input#selected_option-2')
                ->assertEnabled('@save-and-continue');
        });
    }
}
