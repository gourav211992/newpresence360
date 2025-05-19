<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use App\Exports\CrDrReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CrDrReportScheduler;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CrDrReport extends Mailable
{
  use Queueable, SerializesModels;

  public $scheduler;
  public $startDate;
  public $endDate;

  /**
   * Create a new message instance.
   *
   * @param CrDrReportScheduler $scheduler
   * @param Carbon $startDate
   * @param Carbon $endDate
   */
  public function __construct(CrDrReportScheduler $scheduler, Carbon $startDate, Carbon $endDate)
  {
      $this->scheduler = $scheduler;
      $this->startDate = $startDate;
      $this->endDate = $endDate;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
      $fileName = $this->getFileName();
      try {
        // Create the export object with the date range
        $excelData = Excel::raw(new CrDrReportExport($this->scheduler->report_type,$this->scheduler->ledger_id,$this->scheduler->ledger_group_id), \Maatwebsite\Excel\Excel::XLSX);
        $filePath = storage_path('app/public/crdr-report/' . $fileName);
        file_put_contents($filePath, $excelData);

        // Log file creation
        Log::info('Excel file created successfully.', ['file_path' => $filePath]);

        // Check if the file exists
        if (!file_exists($filePath)) {
            throw new \Exception('File does not exist at path: ' . $filePath);
        }
        
        // Log email building
        Log::info('Email built successfully.');

        return $this->view('emails.crdr_report')
                    ->subject($this->getSubject())
                    ->attach($filePath, [
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->with([
                        'scheduler' => $this->scheduler,
                        'startDate' => $this->startDate->format('Y-m-d'),
                        'endDate' => $this->endDate->format('Y-m-d'),
                    ]);
    } catch (\Exception $e) {
        // Log errors
        Log::error('Failed to build email.', ['exception' => $e->getMessage()]);
        throw $e; // Re-throw the exception after logging it
    }
  }

  /**
   * Get the email subject based on the report type.
   *
   * @return string
   */
  private function getSubject()
  {
      $type = ucfirst($this->scheduler->type);
      return "{$type} CrDr Report - " . $this->endDate->format('Y-m-d');
  }

  /**
   * Get the file name for the Excel report.
   *
   * @return string
   */
  private function getFileName()
  {
      $type = strtolower($this->scheduler->type);
      $date = $this->endDate->format('Y-m-d');
      return "crdr_report_{$type}_{$date}.xlsx";
  }


}