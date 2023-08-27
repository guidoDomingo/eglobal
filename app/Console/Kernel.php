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
        Commands\Inspire::class,
        \App\Console\Commands\checkAtmStatus::class,
        \App\Console\Commands\checkSaldosAtms::class,
        \App\Console\Commands\GenerarReporteContable::class,
        \App\Console\Commands\DepositsMigrations::class,
        \App\Console\Commands\GenerarAlquiler::class,
        \App\Console\Commands\TransactionsHistoryExports::class,
        \App\Console\Commands\CheckComisiones::class,
        \App\Console\Commands\GeolocatedTransactions::class,
        \App\Console\Commands\SendTransactionsClaro::class,
        \App\Console\Commands\DeleteImagesBoletas::class,
        \App\Console\Commands\CheckAlquilerError::class

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
       /* $schedule->command('inspire')
                 ->hourly();
       */
    }
}
