<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\HomeLoan;
use Illuminate\Console\Command;
use App\Mail\LoanUserMessageMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserFinalPaymentRiminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-final-payment-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send email to user final payment reminder.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Fetch only home loans with existing recovery schedules
        $homeLoans = HomeLoan::has('recoveryScheduleLoan')
            ->with('recoveryScheduleLoan')
            ->where('status', 2)
            ->get();

        foreach ($homeLoans as $homeLoan) {
            $lastRemainingPayment = $this->getLastRemainingPayment($homeLoan);
            $nextPayment = Carbon::parse($lastRemainingPayment->recovery_due_date);

            if ($lastRemainingPayment) {
                $reminderDays = [30, 7, 3, 1];

                foreach ($reminderDays as $days) {
                    $reminderDate = $nextPayment->copy()->subDays($days);
                    if ($reminderDate->isSameDay($today)) {
                        $this->sendReminder($days, $nextPayment, $homeLoan, $lastRemainingPayment);
                    }
                }
            }
        }
    }

    private function getLastRemainingPayment($homeLoan)
    {
        $schedules = $homeLoan->recoveryScheduleLoan;

        // Check if all previous payments are made
        $allPreviousPaid = $schedules->slice(0, -1)->every(function ($schedule) {
            return $schedule->status == '1';
        });

        // Get the last schedule
        $lastSchedule = $schedules->last();

        // Return the last schedule only if all previous are paid and the last one is unpaid
        if ($allPreviousPaid && $lastSchedule && $lastSchedule->status != '1') {
            return $lastSchedule;
        }

        return null;
    }

    private function sendReminder($daysBefore, Carbon $paymentDate, $homeLoan, $lastRemainingPayment)
    {
        $data = [
            'username' => (string) $homeLoan->name,
            'title' => "Loan Settlement - Final Payment Reminder",
            'message' => "Dear {$homeLoan->name}, this is a reminder for the final payment of {$lastRemainingPayment->total} for your loan (Loan ID: {$homeLoan->appli_no}). The payment is due on {$lastRemainingPayment->recovery_due_date}. Once the payment is received, your loan will be fully settled. Thank you for your prompt attention.",

        ];
        if ($homeLoan->email !== null) {
            try {
                Mail::to(users: $homeLoan->email)->send(new LoanUserMessageMail($data));
                // Log success
                Log::info("Email sent successfully to {$homeLoan->email} for application {$homeLoan->appli_no}");
                $this->info("Sending reminder for home loan ID {$homeLoan->id}, {$daysBefore} days before payment due on {$paymentDate->toDateString()}");
            } catch (\Exception $e) {
                // Log the full error
                $this->info("Failed to send email. Please try again later. Error: {$e->getMessage()}");
            }
        }
    }

}
