<?php

declare(strict_types=1);

namespace App\Providers;

use ARKEcosystem\Foundation\DataBags\DataBag;
use Domain\SecureShell\Contracts\SecureShellKeyGenerator;
use Domain\SecureShell\Contracts\ShellProcessRunner as ShellProcessRunnerContract;
use Domain\SecureShell\Services\SecureShellKey;
use Domain\SecureShell\Services\ShellProcessRunner;
use Domain\User\Models\User;
use Domain\User\Observers\UserObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(DuskServiceProvider::class);
        }
    }

    public function boot(): void
    {
        $this->app->bind(SecureShellKeyGenerator::class, fn () => new SecureShellKey());

        $this->app->bind(ShellProcessRunnerContract::class, fn () => new ShellProcessRunner());

        User::observe(UserObserver::class);

        $this->registerDataBags();

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage())
                    ->subject($subject = trans('auth.verify.page_header'))
                    ->view('mails.email-verification', [
                        'url'     => $url,
                        'subject' => $subject,
                    ]);
        });

        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new MailMessage())
                    ->subject($subject = trans('auth.passwords.email.page_header'))
                    ->view('mails.reset-password', [
                        'url'     => route('password.reset', ['token' => $token, 'email' => $notifiable->email]),
                        'subject' => $subject,
                    ]);
        });
    }

    private function registerDataBags(): void
    {
        DataBag::register('fortify-content', [
            'register' => [
                'pageTitle'   => trans('actions.sign_up'),
                'title'       => trans('actions.sign_up'),
                'description' => trans('pages.auth.subtitle'),
            ],
            'login' => [
                'pageTitle'   => trans('actions.login'),
                'title'       => trans('actions.login'),
                'description' => trans('pages.auth.subtitle'),
            ],
            'password' => [
                'reset' => [
                    'pageTitle'   => trans('actions.reset_password'),
                    'title'       => trans('auth.passwords.reset.title'),
                    'description' => trans('auth.passwords.reset.description'),
                ],
                'request' => [
                    'pageTitle'   => trans('actions.reset_password'),
                    'title'       => trans('auth.passwords.reset.title'),
                    'description' => trans('auth.passwords.reset.description'),
                ],
            ],
            'verification' => [
                'notice' => [
                    'pageTitle' => trans('auth.verify.page_header'),
                ],
                'verify' => [
                    'pageTitle' => trans('auth.verify.page_header'),
                ],
                'send' => [
                    'pageTitle' => trans('auth.token.page_header'),
                ],
            ],
            'two-factor' => [
                'login' => [
                    'pageTitle' => trans('auth.token.page_header'),
                ],
            ],
        ]);
    }
}
