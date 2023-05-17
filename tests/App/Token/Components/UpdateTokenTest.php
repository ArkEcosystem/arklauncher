<?php

declare(strict_types=1);

use App\Token\Components\UpdateToken;
use Domain\SecureShell\Contracts\SecureShellKeyGenerator;
use Domain\Token\Models\Token;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

it('fails to save the token if no data is provided', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->call('update')
            ->assertHasErrors([
                'chainName'     => 'required',
                'token'         => 'required',
                'symbol'        => 'required',
                'mainnetPrefix' => 'required',
                'devnetPrefix'  => 'required',
                'testnetPrefix' => 'required',
            ]);
});

it('fails to save the token if token name is reserved', function ($tokenName) {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
        ->set('token', $tokenName)
        ->call('update')
        ->assertHasErrors(['token']);
})->with([
    'cd',
    'for',
    'sudo',
    'pwd',
    'awk',
    'abs',
    'and',
    'true',
    'false',
    'kill',
    'Cd',
    'For',
    'Sudo',
    'Pwd',
    'Awk',
    'Abs',
    'And',
    'True',
    'False',
    'Kill',
    'CD',
    'FOR',
    'SUDO',
    'PWD',
    'AWK',
    'ABS',
    'AND',
    'TRUE',
    'FALSE',
    'KILL',
]);

it('can save a new token', function () {
    $this->mock(SecureShellKeyGenerator::class, function ($mock) {
        $mock->shouldReceive('make')->andReturn([
            'publicKey'  => 'publicKey',
            'privateKey' => 'privateKey',
        ]);
    });

    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    $file = UploadedFile::fake()->createWithContent('config.txt', $this->fixture('config'));

    $this->assertDatabaseMissing('tokens', ['name' => 'test']);

    Livewire::test(UpdateToken::class, [$token])
            ->set('config', $file)
            ->call('update');

    $this->assertDatabaseHas('tokens', ['name' => 'test']);
    $this->assertDatabaseMissing('tokens', ['name' => 'test', 'keypair' => null]);
});

it('will not modify the config if the json is invalid', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('chainName', null)
            ->set('config', 'invalid')
            ->assertSet('chainName', null)
            ->assertHasErrors('config');
});

it('can cancel and get redirected', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->call('cancel')
            ->assertRedirect('/');
});

it('can go to the next step and back with valid config', function () {
    $token = $this->createToken($this->user());
    $file  = UploadedFile::fake()->createWithContent('config.txt', $this->fixture('config'));

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->set('config', $file)
            ->assertSet('step', 1)
            ->call('next')
            ->assertSet('step', 2)
            ->call('previous')
            ->assertSet('step', 1);
});

it('can go to a specific step', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('step', 1)
            ->call('setStep', 4)
            ->assertSet('step', 4);
});

it('cant go to the next step if validation fails', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('step', 1)
            ->call('next')
            ->assertSet('step', 1)
            ->assertHasErrors([
                'chainName'     => 'required',
                'token'         => 'required',
                'symbol'        => 'required',
                'mainnetPrefix' => 'required',
                'devnetPrefix'  => 'required',
                'testnetPrefix' => 'required',
            ]);
});

it('requires all ports to be different of each other', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->set('step', 2)
            ->set('p2pPort', 1001)
            ->set('webhookPort', 1001)
            ->call('next')
            ->assertSet('step', 2)
            ->assertHasErrors([
                'p2pPort',
                'webhookPort',
            ]);
});

it('can set default values', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('mainnetPrefix', null)
            ->assertSet('devnetPrefix', null)
            ->assertSet('testnetPrefix', null)
            ->set('inputs', ['mainnetPrefix', 'devnetPrefix', 'testnetPrefix'])
            ->call('setDefaults')
            ->assertSet('mainnetPrefix', trans('forms.create_token.input_mainnet_prefix_placeholder'))
            ->assertSet('devnetPrefix', trans('forms.create_token.input_devnet_prefix_placeholder'))
            ->assertSet('testnetPrefix', trans('forms.create_token.input_testnet_prefix_placeholder'));
});

it('can set default fee values', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('fees.static.transfer', null)
            ->assertSet('fees.dynamic.addonBytes.transfer', null)
            ->set('inputs', ['static.transfer'])
            ->call('setFeeDefaults')
            ->assertSet('fees.static.transfer', trans('forms.create_token.static.transfer_placeholder'))
            ->assertSet('fees.dynamic.addonBytes.transfer', null)
            ->set('inputs', ['dynamic.addonBytes.transfer'])
            ->call('setFeeDefaults')
            ->assertSet('fees.static.transfer', trans('forms.create_token.static.transfer_placeholder'))
            ->assertSet('fees.dynamic.addonBytes.transfer', trans('forms.create_token.dynamic.addon_bytes.transfer_placeholder'));
});

it('will not see modal if no values filled in', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('mainnetPrefix', null)
            ->assertSet('requiresConfirmation', false)
            ->call('handleDefaults', ['mainnetPrefix'])
            ->assertSet('requiresConfirmation', false)
            ->assertDontSee(trans('actions.fill_empty'));

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('fees.static.transfer', null)
            ->call('handleFeeDefaults', ['static.transfer'])
            ->assertSet('requiresConfirmation', false)
            ->assertDontSee(trans('actions.fill_empty'));
});

it('can see a modal if values were already filled in', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('mainnetPrefix', null)
            ->set('mainnetPrefix', trans('forms.create_token.input_mainnet_prefix_placeholder'))
            ->assertSet('requiresConfirmation', false)
            ->call('handleDefaults', ['mainnetPrefix'])
            ->assertEmitted('askForConfirmation');
});

it('can overwrite existing values', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('mainnetPrefix', null)
            ->assertSet('devnetPrefix', null)
            ->set('mainnetPrefix', 'abc')
            ->assertSet('mainnetPrefix', 'abc')
            ->assertSet('requiresConfirmation', false)
            ->call('handleDefaults', ['mainnetPrefix', 'devnetPrefix'])
            ->assertEmitted('askForConfirmation')
            ->call('setDefaults', true)
            ->assertSet('mainnetPrefix', trans('forms.create_token.input_mainnet_prefix_placeholder'))
            ->assertSet('devnetPrefix', trans('forms.create_token.input_devnet_prefix_placeholder'));

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('fees.static.transfer', null)
            ->assertSet('fees.static.vote', null)
            ->set('fees.static.transfer', 123)
            ->assertSet('fees.static.transfer', 123)
            ->assertSet('requiresConfirmation', false)
            ->call('handleFeeDefaults', ['static.transfer', 'static.vote'])
            ->assertEmitted('askForConfirmation', true)
            ->call('setFeeDefaults', true)
            ->assertSet('fees.static.transfer', trans('forms.create_token.static.transfer_placeholder'))
            ->assertSet('fees.static.vote', trans('forms.create_token.static.vote_placeholder'));
});

it('can fill defaults without overwriting existing values', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('mainnetPrefix', null)
            ->assertSet('devnetPrefix', null)
            ->set('mainnetPrefix', 'abc')
            ->assertSet('mainnetPrefix', 'abc')
            ->assertSet('requiresConfirmation', false)
            ->call('handleDefaults', ['mainnetPrefix', 'devnetPrefix'])
            ->assertEmitted('askForConfirmation')
            ->call('setDefaults')
            ->assertSet('mainnetPrefix', 'abc')
            ->assertSet('devnetPrefix', trans('forms.create_token.input_devnet_prefix_placeholder'));

    Livewire::test(UpdateToken::class, [$token])
            ->assertSet('fees.static.transfer', null)
            ->assertSet('fees.static.vote', null)
            ->set('fees.static.transfer', 123)
            ->assertSet('fees.static.transfer', 123)
            ->assertSet('requiresConfirmation', false)
            ->call('handleFeeDefaults', ['static.transfer', 'static.vote'])
            ->assertEmitted('askForConfirmation', true)
            ->call('setFeeDefaults')
            ->assertSet('fees.static.transfer', 123)
            ->assertSet('fees.static.vote', trans('forms.create_token.static.vote_placeholder'));
});

it('will load existing token', function () {
    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(UpdateToken::class, [$token])
            ->assertNotSet('tokenObject', null);
});

it('can update a token', function () {
    $token = $this->token();

    $this->assertDatabaseHas('tokens', ['name' => $token->name]);

    Livewire::actingAs($token->user)
            ->test(UpdateToken::class, [$token])
            ->set('chainName', 'testing-token-name')
            ->call('update')
            ->assertHasNoErrors();

    $this->assertDatabaseHas('tokens', ['name' => 'testing-token-name']);
});

it('does not allow duplicate token name', function () {
    $firstToken          = Token::factory()->createForTest();
    $token               = Token::factory()->withServers(0)->createForTest();
    $firstToken->user_id = $token->user_id;
    $firstToken->save();

    $this->assertDatabaseHas('tokens', ['name' => $firstToken->name, 'user_id' => $token->user_id]);

    Livewire::actingAs($token->fresh()->user)
            ->test(UpdateToken::class, [$token->fresh()])
            ->set('chainName', $firstToken->name)
            ->call('update')
            ->assertHasErrors(['chainName' => 'unique']);
});

it('should remove the addonBytes if dynamic fees are disabled', function () {
    $token = Token::factory()->createForTest();

    expect($token->config['fees']['dynamic'])->toHaveKey('addonBytes');

    $this->actingAs($token->user);

    $realComponent = new UpdateToken('1');
    $realComponent->mount($token);

    $realComponent->step = 3;
    $realComponent->store($token->config);

    expect($token->config['fees']['dynamic'])->not()->toHaveKey('addonBytes');
});

it('should be able to cancel the changes and go back to the review page', function () {
    $token = Token::factory()->createForTest();

    $this->actingAs($token->user);

    Livewire::actingAs($token->fresh()->user)
        ->test(UpdateToken::class, [$token->fresh()])
        ->set('step', 3)
        ->call('store', $token->config, true)
        ->call('next')
        ->assertSee(trans('actions.edit_general'))
        ->set('step', 1)
        ->assertSet('step', 1)
        ->set('chainName', 'fooTesting')
        ->assertSet('chainName', 'fooTesting')
        ->assertSet('hasReachedReviewStage', true)
        ->assertSee(trans('actions.save'))
        ->call('cancelChanges')
        ->assertSet('step', 4)
        ->assertSet('chainName', 'testing')
        ->assertSee(trans('actions.cancel'))
        ->assertSee(trans('actions.save_continue'));
});

it('should be able to make changes at review step and have them saved', function () {
    $token = Token::factory()->createForTest();

    $this->actingAs($token->user);

    Livewire::actingAs($token->fresh()->user)
        ->test(UpdateToken::class, [$token->fresh()])
        ->set('step', 3)
        ->call('store', $token->config, true)
        ->call('next')
        ->assertSee(trans('actions.edit_general'))
        ->set('step', 1)
        ->assertSet('step', 1)
        ->assertSet('hasReachedReviewStage', true)
        ->assertSee(trans('actions.save'))
        ->assertSet('chainName', 'testing')
        ->set('chainName', 'fooTesting')
        ->assertSet('chainName', 'fooTesting')
        ->call('returnToReview')
        ->assertSet('chainName', 'fooTesting')
        ->set('step', 1)
        ->assertSet('chainName', 'fooTesting')
        ->call('returnToReview')
        ->assertSet('step', 4)
        ->assertSee(trans('actions.cancel'))
        ->assertSee(trans('actions.save_continue'));
});

it('should not be able to go back on the review page if not validated', function () {
    $token = Token::factory()->createForTest();

    $this->actingAs($token->user);

    Livewire::actingAs($token->fresh()->user)
        ->test(UpdateToken::class, [$token->fresh()])
        ->set('step', 3)
        ->call('store', $token->config, true)
        ->call('next')
        ->assertSee(trans('actions.edit_general'))
        ->set('step', 1)
        ->assertSet('step', 1)
        ->assertSet('hasReachedReviewStage', true)
        ->assertSee(trans('actions.save'))
        ->set('mainnetPrefix', 'foo')
        ->call('returnToReview')
        ->assertSet('step', 1)
        ->assertSee('The mainnet prefix may not be greater than 1 characters.')
        ->assertSee(trans('actions.save'));
});

it('should clear the error bag when cancelling the changes', function () {
    $token = Token::factory()->createForTest();

    $this->actingAs($token->user);

    Livewire::actingAs($token->fresh()->user)
        ->test(UpdateToken::class, [$token->fresh()])
        ->set('step', 3)
        ->call('store', $token->config, true)
        ->call('next')
        ->assertSee(trans('actions.edit_general'))
        ->set('step', 1)
        ->assertSet('step', 1)
        ->assertSet('hasReachedReviewStage', true)
        ->assertSee(trans('actions.save'))
        ->set('mainnetPrefix', 'foo')
        ->assertSet('step', 1)
        ->call('returnToReview')
        ->assertSee('The mainnet prefix may not be greater than 1 characters.')
        ->assertSee(trans('actions.save'))
        ->call('cancelChanges')
        ->assertHasNoErrors();
});

it('accepts_the_token_parameter_as_token_object_and_will_not_conflict_with_the_token_attribute', function () {
    $token = $this->createToken($this->user());

    $this->actingAs($this->user());

    Livewire::test(UpdateToken::class, ['tokenObject' => $token])
            ->set('token', 'abc')
            ->assertSet('token', 'abc');
});
