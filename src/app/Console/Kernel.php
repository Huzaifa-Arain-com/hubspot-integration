<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if ($this->app->isDownForMaintenance()) {
            return;
        }
        $schedule->command('hubspot:companies --post --all');
        $schedule->command('hubspot:companies --post --failed');

        $schedule->command('hubspot:contacts --post --all');
        $schedule->command('hubspot:contacts --post --failed');

        $schedule->command('hubspot:products --post --all');
        $schedule->command('hubspot:products --post --failed');

        $schedule->command('hubspot:deals --post --all');
        $schedule->command('hubspot:deals --post --failed');

        $schedule->command('hubspot:lineItems --post --all');
        $schedule->command('hubspot:lineItems --post --failed');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
