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
        Commands\CheckPeminjamanWarning::class,
    ];

    /**
     * Define the application's command schedule.
     */
   protected function schedule(Schedule $schedule): void
{
    // ✨ Cek expired alat - setiap hari jam 8 pagi
    $schedule->call(function () {
        app()->make(\App\Http\Controllers\AlatsController::class)->checkExpired();
    })->dailyAt('08:00')->withoutOverlapping();

    // ✨ Cek warning peminjaman - JALANKAN 2 KALI SEHARI
    // Pagi jam 8 untuk warning 15%
    $schedule->command('peminjaman:check-warning')
             ->dailyAt('08:00')
             ->withoutOverlapping();

    // Sore jam 4 untuk warning 1 hari sebelum jatuh tempo (lebih urgent)
    $schedule->command('peminjaman:check-warning')
             ->dailyAt('16:00')
             ->withoutOverlapping();

    // Atau jalankan setiap 6 jam untuk lebih responsif
    // $schedule->command('peminjaman:check-warning')
    //          ->everySixHours()
    //          ->withoutOverlapping();
}

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
