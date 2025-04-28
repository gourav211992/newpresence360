<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\HomeLoan;
use Illuminate\Console\Command;
use App\Mail\LoanUserMessageMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UpCommingUserPaymentRiminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:upcomming-user-payment-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send email to user upcomming payment reminder.';

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
            $nextRecovery = $this->getNextPaymentDate($today, $homeLoan);
            $nextPayment = Carbon::parse($nextRecovery->recovery_due_date);

            if ($nextPayment) {
                $reminderDays = [30, 7, 3, 1];

                foreach ($reminderDays as $days) {
                    $reminderDate = $nextPayment->copy()->subDays($days);
                    if ($reminderDate->isSameDay($today)) {
                        $this->sendReminder($days, $nextPayment, $homeLoan, $nextRecovery);
                    }
                }
            }
        }
    }

    private function getNextPaymentDate(Carbon $currentDate, $homeLoan)
    {
        return $homeLoan->recoveryScheduleLoan()
            ->where('recovery_due_date', '>', $currentDate)
            ->orderBy('recovery_due_date', 'asc')
            ->first();
    }

    private function sendReminder($daysBefore, Carbon $paymentDate, $homeLoan, $nextRecovery)
    {
        $data = [
            'username' => (string) $homeLoan->name,
            'title' => "Upcoming Payment Reminder",
            'message' => "Dear {$homeLoan->name}, your next loan repayment of {$nextRecovery->total} is due on {$nextRecovery->recovery_due_date}. Please ensure that sufficient funds are available for the automatic debit, or make a payment through Card. Thank you.",

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
