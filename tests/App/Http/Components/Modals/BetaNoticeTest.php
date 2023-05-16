<?php

declare(strict_types=1);

use App\Http\Components\Modals\BetaNotice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Livewire\Livewire;

it('shows modal', function () {
    Livewire::test(BetaNotice::class)
        ->assertSee(trans('pages.beta.title'));

    $this->get('/login')
        ->assertSeeText(trans('pages.beta.title'));
});

it('can dismiss the modal when agreed', function () {
    expect(Cookie::has('beta_agreement_at'))->toBeFalse();

    Livewire::test(BetaNotice::class)
        ->assertSee(trans('pages.beta.title'))
        ->set('agree', true)
        ->call('close')
        ->assertDontSee(trans('pages.beta.title'));

    expect(Cookie::hasQueued('beta_agreement_at'))->toBeTrue();
});

it('can not dismiss the modal if not agreed', function () {
    expect(Cookie::has('beta_agreement_at'))->toBeFalse();

    Livewire::test(BetaNotice::class)
        ->assertSee(trans('pages.beta.title'))
        ->call('close')
        ->assertSee(trans('pages.beta.title'));

    expect(Cookie::hasQueued('beta_agreement_at'))->toBeFalse();
});

it('should not show modal if already agreed', function () {
    $this->withCookie('beta_agreement_at', Carbon::now()->toString())
        ->get('/login')
        ->assertDontSee(trans('pages.beta.title'));
});
