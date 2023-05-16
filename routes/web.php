<?php

declare(strict_types=1);

use App\Server\Controllers\DeploymentStatusController;
use App\Token\Controllers\DeploymentConfigController;
use App\Token\Controllers\InstallationScriptController;
use App\Token\Controllers\TokenActivityController;
use App\Token\Controllers\TokenCollaboratorController;
use App\Token\Controllers\TokenController;
use App\Token\Controllers\TokenDetailsController;
use App\Token\Controllers\TokenInvitationController;
use App\Token\Controllers\TokenOnboardingController;
use App\Token\Controllers\TokenSecureShellKeyController;
use App\Token\Controllers\TokenServerActionController;
use App\Token\Controllers\TokenServerConfigurationController;
use App\Token\Controllers\TokenServerController;
use App\Token\Controllers\TokenServerProviderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. Thesepa
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/app/tokens')->name('home');

// Application...
Route::prefix('app')->middleware(['auth', 'verified'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::prefix('settings')->group(function () {
            Route::view('/', 'app.user.settings.profile')->name('user.profile');
            Route::view('security', 'app.user.settings.security')->name('user.security');
            Route::view('ssh-keys', 'app.user.settings.ssh-keys')->name('user.ssh-keys');
            Route::view('notifications', 'app.user.settings.notifications')->name('user.notifications');
            Route::view('teams', 'app.user.settings.teams')->name('user.teams');
        });
    });

    Route::prefix('tokens')->group(function () {
        Route::get('/', [TokenController::class, 'index'])->name('tokens');
        Route::get('new', [TokenOnboardingController::class, 'create'])->name('tokens.create');

        Route::prefix('{token}')->group(function () {
            Route::get('details', TokenDetailsController::class)->name('tokens.details');

            Route::get('edit', [TokenController::class, 'edit'])->name('tokens.edit');

            Route::get('welcome', [TokenOnboardingController::class, 'index'])->name('tokens.welcome');
            Route::get('welcome/complete', [TokenOnboardingController::class, 'update'])->name('tokens.welcome.complete');

            Route::get('/', [TokenController::class, 'show'])->name('tokens.show')->middleware('onboard');

            Route::get('collaborators', TokenCollaboratorController::class)->name('tokens.collaborators');
            Route::get('activity', TokenActivityController::class)->name('tokens.activity-log');
            Route::get('ssh-keys', TokenSecureShellKeyController::class)->name('tokens.ssh-keys');
            Route::get('/server-providers', [TokenServerProviderController::class, 'index'])->name('tokens.server-providers');

            Route::get('/servers', [TokenServerController::class, 'index'])->name('tokens.servers.index');

            Route::prefix('networks')->group(function () {
                Route::prefix('{network}')->group(function () {
                    Route::prefix('servers')->group(function () {
                        Route::get('create', [TokenServerController::class, 'create'])->name('tokens.servers.create');

                        Route::prefix('{serverId}')->group(function () {
                            Route::get('/', [TokenServerController::class, 'show'])->name('tokens.servers.show');

                            Route::prefix('actions')->group(function () {
                                Route::post('/', [TokenServerActionController::class, 'start'])->name('tokens.servers.start');
                                Route::delete('/', [TokenServerActionController::class, 'stop'])->name('tokens.servers.stop');
                                Route::put('/', [TokenServerActionController::class, 'reboot'])->name('tokens.servers.reboot');
                            });
                        });
                    });
                });
            });

            Route::prefix('server-configuration')->group(function () {
                Route::get('/', [TokenServerConfigurationController::class, 'index'])->name('tokens.server-configuration');
            });
        });
    });

    // Accept/Reject an Invitation
    Route::get('invitations/{invitation}/accept', [TokenInvitationController::class, 'update'])->name('invitations.accept');
    Route::get('invitations/{invitation}/decline', [TokenInvitationController::class, 'destroy'])->name('invitations.decline');
});

Route::view('terms-of-service', 'app.terms-of-service')->name('terms-of-service');
Route::view('privacy-policy', 'app.privacy-policy')->name('privacy-policy');
Route::view('cookie-policy', 'app.cookie-policy')->name('cookie-policy');

// GDPR
Route::personalDataExports('personal-data-exports');

/*
 * Temporary URLs for all things deployment.
 *
 * These have to be moved into controllers later on for route caching
 * but keeping them here for faster testing until that time comes.
 */
Route::post('temporary/scripts/{server}/deployment', DeploymentStatusController::class)
    ->middleware('signed')
    ->name('server.deployment.status');

/*
 * Temporary URLs for storing/fetching the deployment configuration for the token
 */
Route::get('temporary/scripts/{network}/config', [DeploymentConfigController::class, 'show'])
    ->middleware('signed')
    ->name('server.deployment.config.show');

Route::post('temporary/scripts/{network}/config', [DeploymentConfigController::class, 'store'])
    ->middleware('signed')
    ->name('server.deployment.config.store');

Route::get('temporary/installation-script/{network}', [InstallationScriptController::class, 'show'])
    ->middleware('signed')
    ->name('server.deployment.installation-script.show');
