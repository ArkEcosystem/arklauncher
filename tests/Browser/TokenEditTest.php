<?php

declare(strict_types=1);

namespace Tests\Browser;

use Closure;
use Domain\Token\Models\Token;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\Crop;
use Tests\DuskTestCase;

/**
 * @coversNothing
 */
final class TokenEditTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_fill_the_first_set_of_fields_of_the_form(): void
    {
        $this->fillStep1GeneralConfiguration(function (Browser $browser) {
            $browser
                ->pause(150)
                ->assertSeeIn('h1', __('pages.token.network_title'));
        });
    }

    /** @test */
    public function can_fill_the_network_configuration_related_fields_of_the_form(): void
    {
        $this->fillStep2NetworkConfiguration(function (Browser $browser) {
            $browser
                ->pause(150)
                ->assertSeeIn('h1', __('pages.token.fees.title'));
        });
    }

    /** @test */
    public function can_fill_the_fees_fields_of_the_form(): void
    {
        $this->fillStep3Fees(function (Browser $browser) {
            $browser
                ->pause(150)
                ->assertSeeIn('h1', __('pages.token.review.title'));
        });
    }

    /** @test */
    public function can_save_token_and_continue(): void
    {
        $this->fillStep3Fees(function (Browser $browser, Token $token) {
            $browser
                ->pause(250)
                ->scrollToBottom()
                ->screenshot('001')
                ->pause(250)
                ->press('@save-and-continue')
                ->screenshot('002')
                ->waitForRoute('tokens.welcome', $token)
                ->assertRouteIs('tokens.welcome', $token)
                ->assertPresent('#onboard-step-configuration.line-through')
                ->assertPresent('#onboard-step-server_providers:not(.line-through)');
        });
    }

    /** @test */
    public function can_fill_the_general_fields_of_the_form_with_the_use_defaults_link(): void
    {
        $this->browse(function (Browser $browser) {
            $token = Token::factory()->newly()->create();

            $browser
                ->loginAs($token->user)
                ->visit(route('tokens.edit', $token))
                ->waitForRoute('tokens.edit', $token)
                ->assertRouteIs('tokens.edit', $token)
                ->scrollTo('#general-information')->pause(150)
                ->press('#default-values-general-information')->pause(150)
                ->assertInputValue('chainName', 'MyChain')
                ->assertInputValue('token', 'MYN')
                ->assertInputValue('symbol', 'Ѧ')
                ->scrollTo('#address-prefixes')
                ->press('#default-values-address-prefixes')->pause(150)
                ->assertInputValue('mainnetPrefix', 'P')
                ->assertInputValue('devnetPrefix', 'D')
                ->assertInputValue('testnetPrefix', 'T')
                ->pause(150)->press(__('actions.continue'))
                ->pause(150)->assertSeeIn('h1', __('pages.token.network_title'));
        });
    }

    /** @test */
    public function can_fill_the_network_configuration_with_the_use_default_link(): void
    {
        $this->fillStep1GeneralConfiguration(function (Browser $browser) {
            $browser
                ->pause(150)
                ->scrollToElement('#network')->pause(50)
                ->press('#default-values-network')->pause(150)
                ->assertInputValue('forgers', '51')
                ->assertInputValue('blocktime', '8')
                ->assertInputValue('transactionsPerBlock', '150')
                ->assertInputValue('maxBlockPayload', '2097152')
                ->assertInputValue('totalPremine', '12500000000000000')
                ->assertInputValue('rewardHeightStart', '75600')
                ->assertInputValue('rewardPerBlock', '200000000')
                ->assertInputValue('vendorFieldLength', '255')
                ->assertInputValue('wif', '1')
                ->scrollToElement('#ports')->pause(50)
                ->press('#default-values-ports')->pause(150)
                ->assertInputValue('p2pPort', '4000')
                ->assertInputValue('apiPort', '4003')
                ->assertInputValue('webhookPort', '4004')
                ->assertInputValue('monitorPort', '4005')
                ->assertInputValue('explorerPort', '4200')
                ->scrollToElement('#database')->pause(50)
                ->press('#default-values-database')->pause(150)
                ->assertInputValue('databaseHost', '127.0.0.1')
                ->assertInputValue('databasePort', '5432')
                ->assertInputValue('databaseName', 'core_blockchain')
                ->waitForLivewire()->press(__('actions.continue'))
                ->assertSeeIn('h1', __('pages.token.fees.title'));
        });
    }

    /** @test */
    public function can_fill_the_fees_configuration_with_the_use_default_link(): void
    {
        $this->fillStep2NetworkConfiguration(function (Browser $browser, Token $token) {
            foreach ([
                'transaction-pool',
                'transfer',
                'second-signature',
                'delegate-registration',
                'vote',
                'multisignature',
                'ipfs',
                'multipayments',
                'delegate-resignation',
            ] as $section) {
                $browser
                    ->scrollToElement('#'.$section)->pause(50)
                    ->press('#default-values-'.$section)->pause(50);
            }

            $browser
                ->assertInputValue('fees.dynamic.minFeePool', '3000')
                ->assertInputValue('fees.dynamic.minFeeBroadcast', '3000')
                ->assertInputValue('fees.static.transfer', '10000000')
                ->assertInputValue('fees.dynamic.addonBytes.transfer', '100')
                ->assertInputValue('fees.static.secondSignature', '500000000')
                ->assertInputValue('fees.dynamic.addonBytes.secondSignature', '250')
                ->assertInputValue('fees.static.delegateRegistration', '2500000000')
                ->assertInputValue('fees.dynamic.addonBytes.delegateRegistration', '400000')
                ->assertInputValue('fees.static.vote', '100000000')
                ->assertInputValue('fees.dynamic.addonBytes.vote', '100')
                ->assertInputValue('fees.static.multiSignature', '500000000')
                ->assertInputValue('fees.dynamic.addonBytes.multiSignature', '500')
                ->assertInputValue('fees.static.ipfs', '500000000')
                ->assertInputValue('fees.dynamic.addonBytes.ipfs', '250')
                ->assertInputValue('fees.static.multiPayment', '10000000')
                ->assertInputValue('fees.dynamic.addonBytes.multiPayment', '500')
                ->assertInputValue('fees.static.delegateResignation', '250000000')
                ->assertInputValue('fees.dynamic.addonBytes.delegateResignation', '400000')
                ->press(__('actions.continue'))
                ->pause(150)
                ->assertSeeIn('h1', __('pages.token.review.title'));
        });
    }

    private function fillStep1GeneralConfiguration(Closure $closure): void
    {
        Storage::fake();

        $this->browse(function (Browser $browser) use ($closure) {
            $token = Token::factory()->newly()->create();

            $browser
                ->loginAs($token->user)
                ->visit(route('tokens.edit', $token))
                ->waitForRoute('tokens.edit', $token)
                ->assertRouteIs('tokens.edit', $token)
                ->waitForLivewire()->type('chainName', 'MyChain')
                ->waitForLivewire()->type('token', 'ABC')
                ->waitForLivewire()->type('symbol', '@')
                ->waitForLivewire()->type('mainnetPrefix', 'M')
                ->waitForLivewire()->type('devnetPrefix', 'S')
                ->waitForLivewire()->type('testnetPrefix', 'Z')
                ->attach('#image-single-upload-logo', base_path('tests/fixtures/avatar.jpg'))
                ->pause(150)
                ->within(new Crop('crop-modal-logo'), fn ($crop) => $crop->pressCropSaveButton())
                ->pause(150)
                ->press(__('actions.continue'));

            $closure($browser, $token);
        });
    }

    private function fillStep2NetworkConfiguration(Closure $closure): void
    {
        $this->fillStep1GeneralConfiguration(function (Browser $browser, Token $token) use ($closure) {
            $browser
                ->waitFor('#forgers')
                ->type('forgers', '51')
                ->waitForLivewire()->type('blocktime', '8')
                ->waitForLivewire()->type('transactionsPerBlock', '50')
                ->waitForLivewire()->type('maxBlockPayload', '2097152')
                ->waitForLivewire()->type('totalPremine', '12500000000000000')
                ->waitForLivewire()->type('rewardHeightStart', '75600')
                ->waitForLivewire()->type('rewardPerBlock', '200000000')
                ->waitForLivewire()->type('vendorFieldLength', '255')
                ->waitForLivewire()->type('wif', '1')
                ->waitForLivewire()->type('p2pPort', '4000')
                ->waitForLivewire()->type('apiPort', '4003')
                ->waitForLivewire()->type('webhookPort', '4004')
                ->waitForLivewire()->type('monitorPort', '4005')
                ->waitForLivewire()->type('explorerPort', '4200')
                ->waitForLivewire()->type('databaseHost', '127.0.0.1')
                ->waitForLivewire()->type('databasePort', '5432')
                ->waitForLivewire()->type('databaseName', 'core_blockchain')
                ->waitForLivewire()->press(__('actions.continue'));

            $closure($browser, $token);
        });
    }

    private function fillStep3Fees(Closure $closure): void
    {
        $this->fillStep2NetworkConfiguration(function (Browser $browser, Token $token) use ($closure) {
            $browser
                ->type('fees.dynamic.minFeePool', '3000')
                ->waitForLivewire()->type('fees.dynamic.minFeeBroadcast', '3000')
                ->waitForLivewire()->type('fees.static.transfer', '10000000')
                ->waitForLivewire()->type('fees.dynamic.addonBytes.transfer', '100')
                ->waitForLivewire()->type('fees.static.secondSignature', '500000000')
                ->waitForLivewire()->type('fees.dynamic.addonBytes.secondSignature', '250')
                ->waitForLivewire()->type('fees.static.delegateRegistration', '2500000000')
                ->waitForLivewire()->type('fees.dynamic.addonBytes.delegateRegistration', '400000')
                ->waitForLivewire()->type('fees.static.vote', '100000000')
                ->waitForLivewire()->type('fees.dynamic.addonBytes.vote', '100')
                ->waitForLivewire()->type('fees.static.multiSignature', '500000000')
                ->waitForLivewire()->type('fees.dynamic.addonBytes.multiSignature', '500')
                ->waitForLivewire()->type('fees.static.ipfs', '500000000')
                ->waitForLivewire()->type('fees.dynamic.addonBytes.ipfs', '250')
                ->waitForLivewire()->type('fees.static.multiPayment', '10000000')
                ->waitForLivewire()->type('fees.dynamic.addonBytes.multiPayment', '500')
                ->waitForLivewire()->type('fees.static.delegateResignation', '250000000')
                ->waitForLivewire()->type('fees.dynamic.addonBytes.delegateResignation', '400000')
                ->press(__('actions.continue'));

            $closure($browser, $token);
        });
    }
}
