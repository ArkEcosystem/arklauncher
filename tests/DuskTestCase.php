<?php

declare(strict_types=1);

namespace Tests;

use Domain\Coin\Models\Coin;
use Domain\User\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use Livewire\Macros\DuskBrowserMacros;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Carbon $testStartedAt;

    /**
     * Register the base URL with Dusk.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerMacros();

        $this->testStartedAt = now();

        $this->createInitialModels();

        $this->browse(fn (Browser $browser) => $browser::$waitSeconds = 25);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->script('localStorage.clear()');
        });
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     *
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions())->addArguments(collect([
            '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                '--headless',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     *
     * @return bool
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    private function createInitialModels(): void
    {
        Coin::factory()->create([
            'name'   => 'ARK',
            'symbol' => 'ARK',
        ]);
    }

    private function registerMacros(): void
    {
        Browser::mixin(new DuskBrowserMacros());

        Browser::macro('scrollToElement', function (string $selector, int $offset = 110) {
            $this->driver->executeScript("window.scrollTo(0, document.querySelector(\"$selector\").offsetTop-$offset);");

            return $this;
        });

        Browser::macro('scrollToBottom', function () {
            $this->driver->executeScript('window.scrollTo(0, document.body.scrollHeight);');

            return $this;
        });

        Browser::macro('waitForLocationWithQuery', function ($path, $seconds = null) {
            $message = $this->formatTimeOutMessage('Waited %s seconds for location', $path);

            return $this->waitUntil("(window.location.pathname + window.location.search) == '{$path}'", $seconds, $message);
        });

        Browser::macro('waitForRouteWithQuery', function ($route, $parameters = [], $seconds = null) {
            return $this->waitForLocationWithQuery(route($route, $parameters, false), $seconds);
        });

        Browser::macro('assertLivewireComponentHasAttribute', function ($selector, $attribute, $expected) {
            return $this->assertScript("window.Livewire.find(document.querySelector('{$selector}').attributes.getNamedItem('wire:id').value).get('{$attribute}')", $expected);
        });

        Browser::macro('emitLivewireEvent', function ($name, $value) {
            $this->script("livewire.emit('{$name}', '{$value}')");

            return $this;
        });

        Browser::macro('withUser', function () {
            $user = User::factory()->create([
                'seen_welcome_screens_at' => now(),
            ]);

            /* @phpstan-ignore-next-line */
            $this->loginAs($user);

            return $this;
        });
    }
}
