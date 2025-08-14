<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Mail\LandReportMail;
use App\Models\LandScheduler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class LandSchedulerReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:land-scheduler-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send land reports based on schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schedules = LandScheduler::all();

        foreach ($schedules as $schedule) {
            $mail = $this->processSchedule($schedule);
        }

        $this->info("Land schedules processed successfully.{$mail}");
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
            //dd('test', $mail);
            $schedule->update(['last_run' => $endDate]);

            return $mail;
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
                    Mail::to($user->email)->send(new LandReportMail($schedule, $startDate, $endDate));
                    return 'sent mail';
                } catch (\Exception $e) {
                    return 'failed' . $e->getMessage();
                }
            }
        }

        if($schedule->toable_type == 'App\Models\Employee') {
            $user = Employee::find($schedule->toable_id);
            if ($user) {
                try {
                    Mail::to($user->email)->send(new LandReportMail($schedule, $startDate, $endDate));
                    return 'sent mail';
                } catch (\Exception $e) {
                    return 'failed' . $e->getMessage();
                }
            }
        }
        return 'user_not_found';
    }

}
