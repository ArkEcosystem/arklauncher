<?php

declare(strict_types=1);

use App\Token\Components\LogoUpload;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

it('can upload a logo', function () {
    $token = $this->token();

    expect($token->logo)->toBeEmpty();

    $logo = UploadedFile::fake()->image('logo.jpeg', 300, 300);

    Livewire::actingAs($token->user)
            ->test(LogoUpload::class, ['token' => $token])
            ->set('imageSingle', $logo)
            ->assertSet('logo', $token->logo)
            ->assertSet('imageDeleted', false);

    expect($token->logo)->toBeString();
});

it('can upload a logo from disk', function () {
    $token = $this->token();

    expect($token->logo)->toBeEmpty();

    file_put_contents(storage_path('app/livewire-tmp/avatar.jpg'), file_get_contents('tests/fixtures/avatar.jpg'));

    Livewire::actingAs($token->user)
            ->test(LogoUpload::class, ['token' => $token])
            ->set('imageSingle', 'avatar.jpg')
            ->assertSet('logo', $token->logo)
            ->assertSet('imageDeleted', false);

    expect($token->logo)->toBeString();
});

it('can delete a logo', function () {
    $token = $this->token();

    $logo = UploadedFile::fake()->image('logo.jpeg', 300, 300);

    $instance = Livewire::actingAs($token->user)
            ->test(LogoUpload::class, ['token' => $token])
            ->set('imageSingle', $logo)
            ->assertSet('logo', $token->logo);

    expect($token->logo)->toBeString();

    $instance->call('deleteImageSingle')
            ->assertSet('imageSingle', null);

    expect($token->fresh()->getFirstMedia('logo'))->toBeNull();
});

it('cannot upload a logo with disallowed extension', function () {
    $token = $this->token();

    expect($token->logo)->toBeEmpty();

    $logo = UploadedFile::fake()->create('logo.gif', 1000, 'image/gif');

    Livewire::actingAs($token->user)
            ->test(LogoUpload::class, ['token' => $token])
            ->set('imageSingle', $logo)
            ->assertHasErrors('imageSingle');
});

it('cannot upload a logo that is too large', function () {
    $token = $this->token();

    expect($token->logo)->toBeEmpty();

    $logo = UploadedFile::fake()->image('logo.jpeg')->size(5000);

    Livewire::actingAs($token->user)
            ->test(LogoUpload::class, ['token' => $token])
            ->set('imageSingle', $logo)
            ->assertHasErrors('imageSingle');
});

it('cannot upload a logo that is too small', function () {
    $token = $this->token();

    expect($token->logo)->toBeEmpty();

    $logo = UploadedFile::fake()->image('logo.jpeg', 1, 1);

    Livewire::actingAs($token->user)
            ->test(LogoUpload::class, ['token' => $token])
            ->set('imageSingle', $logo)
            ->assertHasErrors('imageSingle');
});

it('cannot upload a logo that is too big', function () {
    $token = $this->token();

    expect($token->logo)->toBeEmpty();

    $logo = UploadedFile::fake()->image('logo.jpeg', 10000, 10000);

    Livewire::actingAs($token->user)
            ->test(LogoUpload::class, ['token' => $token])
            ->set('imageSingle', $logo)
            ->assertHasErrors('imageSingle');
});

it('does not allow uploading if you do not have the right permission', function () {
    $token       = $this->token();
    $anotherUser = $this->user();

    expect($token->logo)->toBeEmpty();

    $logo = UploadedFile::fake()->image('logo.jpeg');

    Livewire::actingAs($anotherUser)
                ->test(LogoUpload::class, ['token' => $token])
                ->set('imageSingle', $logo)
                ->assertForbidden();
});
