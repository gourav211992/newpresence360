<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Console\Command;
use App\Models\CashflowScheduler;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Services\Mailers\Mailer;
use App\Models\MailBox;
use App\Models\AuthUser;
use App\Http\Controllers\CashflowReportController;
use App\Models\Organization;


class GenerateCashflowReport extends Command
{
    protected $signature = 'app:generate-cashflow-report';
    protected $description = 'Generate and send cashflow report based on schedule';

    public function handle()
    {
        date_default_timezone_set('Asia/Kolkata');
        $schedules = CashflowScheduler::all();

        foreach ($schedules as $schedule) {
            $this->processSchedule($schedule);
        }

        $this->info('Cashflow schedules processed successfully.');
    }

    private function processSchedule($schedule)
    {
        $now = Carbon::now();
        $lastRun = $schedule->last_run ? Carbon::parse($schedule->last_run) : null;
        $nextRun = $this->getNextRunDate($schedule, $lastRun);

        if ($now >= $nextRun) {
            $startDate = $this->getReportStartDate($schedule, $lastRun);
            $endDate = $nextRun;

            // Generate and send the report
            $this->sendReport($schedule, $startDate, $endDate);
            
            $schedule->update(['last_run' => $endDate]);
        }
    }

    private function getNextRunDate($schedule, $lastRun)
    {
        if (!$lastRun) {
            //return $this->getReportEndDate($schedule, Carbon::parse($schedule->date));
            return Carbon::parse($schedule->date);
        }

        switch ($schedule->type) {
            case 'daily': return $lastRun->copy()->addDay();
            case 'weekly': return $lastRun->copy()->addWeek();
            case 'monthly': return $lastRun->copy()->addMonth();
            default: return $lastRun;
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
            case 'daily': return $lastRun->copy()->addDay();
            case 'weekly': return $lastRun->copy()->addWeek();
            case 'monthly': return $lastRun->copy()->addMonth();
            default: return $lastRun;
        }
    }

    public function sendReport($schedule, $startDate, $endDate)
    {
        $organization = $schedule->organization_id;
        $created_by = $schedule->created_by;
        $type = strtolower($schedule->type);
        $date = Carbon::now()->format('d-m-Y_His'); // Example: 20250404_153045
        $fileName = "Cashflow_Statment_{$date}_{$type}.pdf";
        $orgName = Organization::find($organization)?->name;
        $dateo = date('d-m-Y');
        $subject = "Cashflow Statment from {$orgName} | {$dateo}";

        
        $user = AuthUser::find($schedule->toable_id);

        if ($user) {
            try {
                // Ensure the directory exists
                if (!Storage::disk('public')->exists('cashflow')) {
                    Storage::disk('public')->makeDirectory('cashflow');
                }

                $filePath = 'cashflow/' . $fileName;
                $endDate = now();
                $startDate = now()->subDay();

                if ($type === 'weekly') {
                    $startDate = now()->subWeek();
                } elseif ($type === 'monthly') {
                    $startDate = now()->subMonth();
                }

                // Format the dates (optional)
                $startDate = $startDate->format('Y-m-d');
                $endDate = $endDate->format('Y-m-d');
                $pdf = CashflowReportController::print($startDate, $endDate,$organization,$created_by)->stream($fileName);
                Storage::disk('public')->put($filePath, $pdf);
                $fileUrl = Storage::url($filePath);

                // Log file creation
                Log::info('Excel file created successfully.', ['file_path' => $filePath]);

                // Check if file exists before sending email
                if (!Storage::disk('public')->exists($filePath)) {
                    throw new \Exception('File does not exist at path: ' . $filePath);
                }

                // Log email building
                Log::info('Building email for sending report.');

                $cc_list = json_decode($schedule->cc);
                $cc = [];
                if(is_array($cc_list)) {
                    foreach ($cc_list as $cc_l) {
                        $cc[] = AuthUser::find((int)$cc_l)?->email;
                    }
                }
               if(!empty($cc) && $user->email) {
                  
                // Prepare the MailBox object
                $mailBox = new MailBox();
                $mailBox->mail_to = $user->email;
                $mailBox->mail_cc = implode(',', $cc); 
                $mailBox->layout = 'emails.cashflow_report'; // Ensure you have this layout
                $mailBox->subject = $subject;
                $mailBox->mail_body = json_encode([
                    'remarks' => $schedule->remarks,
                    'custName' => $user->name,
                    'orgName' => $orgName,
                ]);

                // Attach the file with MIME type
                $mailBox->attachment = env('APP_URL','/'). $fileUrl;
                //$mailBox->attachmentMimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

                // Use the custom Mailer class to send the email
                $mailer = new Mailer();
                $mailer->emailTo($mailBox); // Send email using MailBox object

                // Log successful sending
                Log::info('Email sent successfully.');

                return 'sent';
            } else {
                Log::error('Email not sent. User email or CC list is empty.', [
                    'user_email' => $user->email,
                    'cc_list' => $cc,
                ]);
                return 'failed';    
            }
            } catch (\Exception $e) {
                Log::error('Error in generating or sending the report.', [
                    'error' => $e->getMessage(),
                    'stack' => $e->getTraceAsString(),
                ]);
                return 'failed';
            }
        } else {
            return 'user_not_found';
        }
    }
}
