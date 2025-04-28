<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\HomeLoan;
use Illuminate\Console\Command;
use App\Mail\LoanUserMessageMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserPaymentOverdue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-payment-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send email to user payment overdue reminder.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Fetch only home loans with existing recovery schedules
        $homeLoans = HomeLoan::has('recoveryScheduleLoan')
            ->with('recoveryScheduleLoan', 'recoveryLoan')
            ->where('status', 2)
            ->get();

        foreach ($homeLoans as $homeLoan) {
            $dueDateRecovery = $this->getPaymentDueDate($today, $homeLoan);

            if ($dueDateRecovery) {
                $this->sendReminder($homeLoan, $dueDateRecovery);
            }
        }
    }

    private function getPaymentDueDate(Carbon $currentDate, $homeLoan)
    {
        $yesterday = $currentDate->copy()->subDay();
        return $homeLoan->recoveryScheduleLoan()
            ->where('recovery_due_date', '=', $yesterday)
            ->where('status', '=', 0)
            ->orderBy('recovery_due_date', 'asc')
            ->first();
    }

    private function sendReminder($homeLoan, $dueDateRecovery)
    {
        $data = [
            'username' => (string) $homeLoan->name,
            'title' => "Payment Overdue",
            'message' => "Dear {$homeLoan->name}, your loan payment of {$dueDateRecovery->total} due on {$dueDateRecovery->recovery_due_date} is now overdue. Please make the payment at the earliest to avoid late fees and penalties. If you've already made the payment, kindly ignore this message.",

        ];
        if ($homeLoan->email !== null) {
            try {
                Mail::to(users: $homeLoan->email)->send(new LoanUserMessageMail($data));
                // Log success
                Log::info("Email sent successfully to {$homeLoan->email} for application {$homeLoan->appli_no}");
                $this->info("Sending reminder for home loan ID {$homeLoan->id}");
            } catch (\Exception $e) {
                // Log the full error
                $this->info("Failed to send email. Please try again later. Error: {$e->getMessage()}");
            }
        }
    }

}
