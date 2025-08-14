<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\PurchaseOrder;
use Illuminate\Console\Command;
use App\Mail\PurchaseOrderReport;
use Illuminate\Support\Facades\Mail;
use App\Models\PurchaseOrderScheduler;

class GeneratePurchaseOrderReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-purchase-order-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send purchase order reports based on schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schedules = PurchaseOrderScheduler::all();

        foreach ($schedules as $schedule) {
            $this->processSchedule($schedule);
        }

        $this->info('Purchase order schedules processed successfully.');
    }

    private function processSchedule($schedule)
    {
        $now = Carbon::now();
        $lastRun = $schedule->last_run ? Carbon::parse($schedule->last_run) : null;
        $nextRun = $this->getNextRunDate($schedule, $lastRun);

        if ($now >= $nextRun) {
            $startDate = $this->getReportStartDate($schedule, $lastRun);
            $endDate = $nextRun;

            $mail = $this->sendReport($schedule, $startDate, $endDate);
            //  dd('test', $mail);
            $schedule->update(['last_run' => $endDate]);
        }
    }

    private function getNextRunDate($schedule, $lastRun)
    {
        if (!$lastRun) {
            return $this->getReportEndDate($schedule, Carbon::parse($schedule->date));
        }

        switch ($schedule->type) {
            case 'daily':
                return $lastRun->copy()->addDay();
            case 'weekly':
                return $lastRun->copy()->addWeek();
            case 'monthly':
                return $lastRun->copy()->addMonth();
            default:
                return $lastRun;
        }
    }

    private function getReportStartDate($schedule, $lastRun)
    {
        if (!$lastRun) {
            return Carbon::parse($schedule->date);
        }

        return $lastRun;
    }

    private function getReportEndDate($schedule, $lastRun)
    {
        switch ($schedule->type) {
            case 'daily':
                return $lastRun->copy()->addDay();
            case 'weekly':
                return $lastRun->copy()->addWeek();
            case 'monthly':
                return $lastRun->copy()->addMonth();
            default:
                return $lastRun;
        }
    }

    private function sendReport($schedule, $startDate, $endDate)
    {
        if($schedule->toable_type == 'App\Models\User') {
            $user = User::find($schedule->toable_id);
            if ($user) {
                try {
                    Mail::to($user->email)->send(new PurchaseOrderReport($schedule, $startDate, $endDate));
                    return 'sent';
                } catch (\Exception $e) {
                    return 'failed';
                }
            }
        }

        if($schedule->toable_type == 'App\Models\Employee') {
            $user = Employee::find($schedule->toable_id);
            if ($user) {
                try {
                    Mail::to($user->email)->send(new PurchaseOrderReport($schedule, $startDate, $endDate));
                    return 'sent';
                } catch (\Exception $e) {
                    return 'failed';
                }
            }
        }
        return 'user_not_found';
    }

}
