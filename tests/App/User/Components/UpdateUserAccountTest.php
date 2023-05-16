<?php

declare(strict_types=1);

use App\User\Components\UpdateUserAccount;
use App\User\Mail\ConfirmEmailChange;
use Domain\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

it('opens a modal to verify password if mail is valid', function () {
    $this->actingAs($this->user());
    $email = 'updated@example.com';

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->assertSet('confirmEmailChangeModal', true);
});

it('can cancel the email change', function () {
    $this->actingAs($this->user());
    $email = 'updated@example.com';

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->assertSet('confirmEmailChangeModal', true)
        ->call('closeEmailConfirmationModal')
        ->assertSet('password', '')
        ->assertSet('confirmEmailChangeModal', false);
});

it('does not open a modal if the email address is not different', function () {
    $user = User::factory()->create([
        'email'    => 'initial@example.com',
        'password' => bcrypt('secret'),
    ]);

    $this->actingAs($user);

    Livewire::test(UpdateUserAccount::class)
        ->set('email', 'initial@example.com')
        ->call('update')
        ->assertEmitted('toastMessage');
});

it('validates the user password before sending a confirmation email', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email'    => 'initial@example.com',
        'password' => bcrypt('secret'),
    ]);

    $this->actingAs($user);
    $email = 'new@example.com';

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->set('password', 'no-secret')
        ->call('confirmEmailChange')
        ->assertHasErrors(['password']);

    Mail::assertNothingSent();
});

it('sends a confirmation email and stores the new email', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email'    => 'initial@example.com',
        'password' => bcrypt('secret'),
    ]);

    $this->actingAs($user);
    $email = 'new@example.com';

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->set('password', 'secret')
        ->call('confirmEmailChange')
        ->assertHasNoErrors()
        ->call('closeEmailConfirmationModal');

    Mail::assertQueued(ConfirmEmailChange::class, fn ($mail) => $mail->email === 'new@example.com');

    expect($user->fresh()->getMetaAttribute('email_to_update'))->toBe('new@example.com');
    expect($user->fresh()->getMetaAttribute('email_to_update_stored_at'))->not->toBeNull();
});

it('stores verification email as lowercase', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email'    => 'initial@example.com',
        'password' => bcrypt('secret'),
    ]);

    $this->actingAs($user);
    $email = 'NEW@EXAMPLE.COM';

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->set('password', 'secret')
        ->call('confirmEmailChange')
        ->assertHasNoErrors()
        ->call('closeEmailConfirmationModal');

    Mail::assertQueued(ConfirmEmailChange::class, fn ($mail) => $mail->email === 'new@example.com');

    expect($user->fresh()->getMetaAttribute('email_to_update'))->toBe('new@example.com');
    expect($user->fresh()->getMetaAttribute('email_to_update_stored_at'))->not->toBeNull();
});

it('can resend the confirmation email', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email'    => 'initial@example.com',
        'password' => bcrypt('secret'),
    ]);

    $user->setMetaAttribute('email_to_update', 'new@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());

    $this->actingAs($user);

    Livewire::test(UpdateUserAccount::class)
            ->call('resendConfirmationEmail')
            ->assertEmitted('toastMessage');

    Mail::assertQueued(ConfirmEmailChange::class, fn ($mail) => $mail->email === 'new@example.com');
});

it('does not send confirmation email if email has changed in the meantime', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email'    => 'initial@example.com',
        'password' => bcrypt('secret'),
    ]);

    $user->setMetaAttribute('email_to_update', 'new@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());

    $this->actingAs($user);

    $component = Livewire::test(UpdateUserAccount::class);

    $user->forgetMetaAttribute('email_to_update');
    $user->forgetMetaAttribute('email_to_update_stored_at');

    $component->call('resendConfirmationEmail')->assertRedirect(route('user.profile'));

    Mail::assertNothingSent();
});

it('can cancel the confirmation workflow', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email'    => 'initial@example.com',
        'password' => bcrypt('secret'),
    ]);

    $user->setMetaAttribute('email_to_update', 'new@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());

    $this->actingAs($user);

    Livewire::test(UpdateUserAccount::class)->call('cancelConfirmationEmail');

    expect($user->fresh()->getMetaAttribute('email_to_update'))->toBeNull();
    expect($user->fresh()->getMetaAttribute('email_to_update_stored_at'))->toBeNull();
});

it('can not click link after cancelling the email change', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email'    => 'initial@example.com',
        'password' => bcrypt('secret'),
    ]);

    $user->setMetaAttribute('email_to_update', 'new@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());

    $this->actingAs($user);

    Livewire::test(UpdateUserAccount::class)
        ->call('cancelConfirmationEmail');

    expect($user->fresh()->getMetaAttribute('email_to_update'))->toBeNull();
    expect($user->fresh()->getMetaAttribute('email_to_update_stored_at'))->toBeNull();

    $expiresAt = now()->addHours(24);

    $url = URL::temporarySignedRoute('user.profile', $expiresAt, [
        'email' => 'new@example.com',
    ]);

    $parts = parse_url($url);
    parse_str($parts['query'], $params);
    $params['signature'] = 'Invald';

    $this->partialMock(Request::class)
        ->shouldReceive('has')
        ->with(['email', 'signature'])
        ->andReturn(true)
        ->shouldReceive('input')
        ->with('email')
        ->andReturn('new@example.com')
        ->shouldReceive('input')
        ->with('expires')
        ->andReturn($expiresAt->timestamp)
        ->shouldReceive('hasValidSignature')
        ->andReturn(true);

    Livewire::withQueryParams($params)
        ->test(UpdateUserAccount::class)
        ->assertSet('expiredLink', true);

    expect($user->fresh()->email)->toBe('initial@example.com');
});

it('cannot update the email if the current email is invalid', function () {
    $this->actingAs($this->user());
    $email = 'invalid-email';

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->assertNotEmitted('toastMessage')
        ->assertSee('The email must be a valid email address.');
});

it('cannot update the email if the current email is empty', function () {
    $this->actingAs($this->user());
    $email = '';

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->assertNotEmitted('toastMessage')
        ->assertSee('The email field is required.');
});

it('cannot update the email if the current email is greater than 255 characters', function () {
    $this->actingAs($this->user());
    $email = str_repeat('h', 255).'@example.com';

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->assertNotEmitted('toastMessage')
        ->assertSee('The email may not be greater than 255 characters.');
});

it('cannot update the email if it\'s already been taken', function () {
    $this->actingAs($this->user());
    $email = $this->user()->email;

    Livewire::test(UpdateUserAccount::class)
        ->set('email', $email)
        ->call('update')
        ->assertNotEmitted('toastMessage')
        ->assertSee(trans('validation.custom.email.unique'));
});

it('can update the name', function () {
    $this->actingAs($user = $this->user());

    Livewire::test(UpdateUserAccount::class)
        ->set('name', 'John Doe')
        ->call('update')
        ->assertEmitted('toastMessage');

    expect($user->fresh()->name)->toBe('John Doe');
});

it('cannot update the name name is empty', function () {
    $this->actingAs($this->user());

    Livewire::test(UpdateUserAccount::class)
        ->set('name', '')
        ->call('update')
        ->assertNotEmitted('toastMessage')
        ->assertHasErrors('name');
});

it('cannot update the name if the current name is greater than 255 characters', function () {
    $this->actingAs($this->user());
    $name = str_repeat('a', 255);

    Livewire::test(UpdateUserAccount::class)
        ->set('name', $name)
        ->call('update')
        ->assertNotEmitted('toastMessage')
        ->assertHasErrors('name');
});

it('updates the email if received within a signed url and clears the meta', function () {
    $user = User::factory()->create(['email' => 'example@example.com']);

    $this->actingAs($user);

    $expiresAt = now()->addHours(24);

    $url = URL::temporarySignedRoute(
        'user.profile',
        $expiresAt,
        ['email' => 'new@example.com'],
    );

    $user->setMetaAttribute('email_to_update', 'new@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());

    $parts = parse_url($url);
    parse_str($parts['query'], $params);

    $this->partialMock(Request::class)
        ->shouldReceive('has')
        ->with(['email', 'signature'])
        ->andReturn(true)
        ->shouldReceive('input')
        ->with('email')
        ->andReturn('new@example.com')
        ->shouldReceive('input')
        ->with('expires')
        ->andReturn($expiresAt->timestamp)
        ->shouldReceive('hasValidSignature')
        ->andReturn(true);

    Livewire::withQueryParams($params)
        ->test(UpdateUserAccount::class)
        ->call('closeEmailUpdatedFeedbackModal')
        ->assertSet('signature', null);

    expect($user->fresh()->email)->toBe('new@example.com');
    expect($user->fresh()->getMetaAttribute('email_to_update'))->toBeNull();
    expect($user->fresh()->getMetaAttribute('email_to_update_stored_at'))->toBeNull();
});

it('doesnt update the email if mail is different to the one in meta attributes', function () {
    $updatedAt = now()->subWeek()->startOfDay();
    $user      = User::factory()->create([
        'email'      => 'example@example.com',
        'updated_at' => $updatedAt,
    ]);

    $this->actingAs($user);

    $expiresAt = now()->addHours(24);

    $url = URL::temporarySignedRoute(
        'user.profile',
        $expiresAt,
        ['email' => 'wrong@example.com'],
    );

    $parts = parse_url($url);
    parse_str($parts['query'], $params);

    $user->setMetaAttribute('email_to_update', 'example3@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());
    $user->updated_at = $updatedAt;
    $user->save();

    $this->partialMock(Request::class)
        ->shouldReceive('has')
        ->with(['email', 'signature'])
        ->andReturn(true)
        ->shouldReceive('input')
        ->with('email')
        ->andReturn('wrong@example.com')
        ->shouldReceive('input')
        ->with('expires')
        ->andReturn($expiresAt->timestamp)
        ->shouldReceive('hasValidSignature')
        ->andReturn(true);

    Livewire::withQueryParams($params)
        ->test(UpdateUserAccount::class)
        ->assertSet('expiredLink', true);

    expect($user->fresh()->updated_at)->toEqual($updatedAt);
});

it('should update email if request mail is different case to meta', function () {
    $updatedAt = now()->subWeek()->startOfDay();
    $user      = User::factory()->create([
        'email'      => 'example@example.com',
        'updated_at' => $updatedAt,
    ]);

    $user->setMetaAttribute('email_to_update', 'EXAMPLE2@EXAMPLE.COM');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());
    $user->updated_at = $updatedAt;
    $user->save();

    $this->actingAs($user->fresh());

    $expiresAt = now()->addHours(24);

    $url = URL::temporarySignedRoute(
        'user.profile',
        $expiresAt,
        ['email' => 'example2@example.com'],
    );

    $parts = parse_url($url);
    parse_str($parts['query'], $params);

    $this->partialMock(Request::class)
        ->shouldReceive('has')
        ->with(['email', 'signature'])
        ->andReturn(true)
        ->shouldReceive('input')
        ->with('email')
        ->andReturn('EXAMPLE2@EXAMPLE.COM')
        ->shouldReceive('input')
        ->with('expires')
        ->andReturn($expiresAt->timestamp)
        ->shouldReceive('hasValidSignature')
        ->andReturn(true);

    Livewire::withQueryParams($params)
        ->test(UpdateUserAccount::class)
        ->assertSet('expiredLink', false)
        ->assertSet('invalidLink', false)
        ->call('closeEmailUpdatedFeedbackModal')
        ->assertSet('signature', null);

    expect($user->fresh()->email)->toBe('example2@example.com');
});

it('doesnt update the email if the signature is invalid', function () {
    $user = User::factory()->create(['email' => 'example@example.com']);

    $user->setMetaAttribute('email_to_update', 'new@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());

    $this->actingAs($user);

    $expiresAt = now()->addHours(24);

    $url = URL::temporarySignedRoute(
        'user.profile',
        $expiresAt,
        ['email' => 'new@example.com'],
    );

    $parts = parse_url($url);
    parse_str($parts['query'], $params);
    $params['signature'] = 'Invald';

    $this->partialMock(Request::class)
        ->shouldReceive('has')
        ->with(['email', 'signature'])
        ->andReturn(true)
        ->shouldReceive('input')
        ->with('email')
        ->andReturn('new@example.com')
        ->shouldReceive('input')
        ->with('expires')
        ->andReturn($expiresAt->timestamp)
        ->shouldReceive('hasValidSignature')
        ->andReturn(false);

    Livewire::withQueryParams($params)
        ->test(UpdateUserAccount::class)
        ->assertSet('signature', null)
        ->assertSet('invalidLink', true);

    expect($user->fresh()->email)->toBe('example@example.com');
});

it('doesnt update the email if the signature is expired', function () {
    $user = User::factory()->create(['email' => 'example@example.com']);

    $user->setMetaAttribute('email_to_update', 'new@example.com');
    $user->setMetaAttribute('email_to_update_stored_at', Carbon::now()->toString());

    $this->actingAs($user);

    $expiresAt = now()->subMinute(1);

    $url = URL::temporarySignedRoute(
        'user.profile',
        $expiresAt,
        ['email' => 'new@example.com'],
    );

    $parts = parse_url($url);
    parse_str($parts['query'], $params);
    $params['signature'] = 'Invald';

    $this->partialMock(Request::class)
        ->shouldReceive('has')
        ->with(['email', 'signature'])
        ->andReturn(true)
        ->shouldReceive('input')
        ->with('email')
        ->andReturn('new@example.com')
        ->shouldReceive('hasValidSignature')
        ->andReturn(false)
        ->shouldReceive('input')
        ->with('expires')
        ->andReturn($expiresAt->timestamp);

    Livewire::withQueryParams($params)
        ->test(UpdateUserAccount::class)
        ->assertSet('signature', null);

    expect($user->fresh()->email)->toBe('example@example.com');
});
