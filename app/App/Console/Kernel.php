<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CheckRemoteServerExistence;
use App\Console\Commands\IndexServerProviderImages;
use App\Console\Commands\IndexServerProviderPlans;
use App\Console\Commands\IndexServerProviderRegions;
use App\Console\Commands\MaintainInvitations;
use App\Console\Commands\MaintainServerProviderKeys;
use App\Console\Commands\PingRemoteServers;
use App\Console\Commands\PurgeServersWithFailedDeployments;
use App\Console\Commands\SyncServerRemoteAddresses;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command(SyncServerRemoteAddresses::class)
            ->everyMinute()
            ->withoutOverlapping();

        $schedule
            ->command(CheckRemoteServerExistence::class)
            ->hourly()
            ->withoutOverlapping();

        $schedule
            ->command(PingRemoteServers::class)
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        $schedule
            ->command(MaintainServerProviderKeys::class)
            ->hourly()
            ->withoutOverlapping();

        $schedule
            ->command(MaintainInvitations::class)
            ->everyTenMinutes()
            ->withoutOverlapping();

        $schedule
            ->command(IndexServerProviderPlans::class)
            ->daily()
            ->withoutOverlapping();

        $schedule
            ->command(IndexServerProviderRegions::class)
            ->twiceDaily()
            ->withoutOverlapping();

        $schedule
            ->command(IndexServerProviderImages::class)
            ->daily()
            ->withoutOverlapping();

        $schedule
            ->command(PurgeServersWithFailedDeployments::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        $schedule
            ->command('telescope:prune')
            ->environments(['local'])
            ->twiceDaily();

        $schedule
            ->command('horizon:snapshot')
            ->everyFiveMinutes();

        $schedule->command('personal-data-export:clean')->daily();

        // TODO: Do we want to use onFailure onSuccess hooks ?

        /*
        $schedule
            ->command('backup:clean')
            ->daily()
            ->at('01:00')
            ->withoutOverlapping();

        $schedule
            ->command('backup:run')
            ->daily()
            ->at('01:10')
            ->withoutOverlapping();
        */

        // TODO: Command to upload the backup
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
