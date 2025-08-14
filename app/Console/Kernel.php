<?php

namespace App\Console;

use App\Models\NumberPattern;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Reset Patterns Daily 
        $schedule->call(function () {
            NumberPattern::where('series_numbering','Auto')->where('reset_pattern','Daily')->each(function (NumberPattern $pattern) {  
                $pattern->current_no = $pattern->starting_no ?? 1;   
                $pattern->save();  
            });
        })->daily();

        // Reset Patterns Monthly
        $schedule->call(function () {
            NumberPattern::where('series_numbering','Auto')->where('reset_pattern','Monthly')->each(function (NumberPattern $pattern) {  
                $pattern->current_no = $pattern->starting_no ?? 1;   
                $pattern->save();  
            });
        })->monthly();

        // Reset Patterns Quarterly
        $schedule->call(function () {
            NumberPattern::where('series_numbering','Auto')->where('reset_pattern','Quarterly')->each(function (NumberPattern $pattern) {  
                $pattern->current_no = $pattern->starting_no ?? 1;   
                $pattern->save();  
            });
        })->quarterly();

        // Reset Patterns Yearly
        $schedule->call(function () {
            NumberPattern::where('series_numbering','Auto')->where('reset_pattern','Yearly')->each(function (NumberPattern $pattern) {  
                $pattern->current_no = $pattern->starting_no ?? 1;   
                $pattern->save();  
            });
        })->yearly();

        // Purchase order reports
        $schedule->command('purchase-orders:process-schedules')->daily();
        $schedule->command('app:upcomming-user-payment-reminder')->daily();
        $schedule->command('app:upcomming-user-payment-reminder')->daily();
        $schedule->command('app:generate-cr-dr-report')->everyMinute();
        $schedule->command('app:generate-cashflow-report')->everyMinute();
        
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
