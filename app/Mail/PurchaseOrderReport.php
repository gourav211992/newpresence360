<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use App\Exports\PurchaseOrderExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseOrderScheduler;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderReport extends Mailable
{
  use Queueable, SerializesModels;

  public $scheduler;
  public $startDate;
  public $endDate;

  /**
   * Create a new message instance.
   *
   * @param PurchaseOrderScheduler $scheduler
   * @param Carbon $startDate
   * @param Carbon $endDate
   */
  public function __construct(PurchaseOrderScheduler $scheduler, Carbon $startDate, Carbon $endDate)
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
        $excelData = Excel::raw(new PurchaseOrderExport(Carbon::parse($this->startDate), Carbon::parse($this->endDate)), \Maatwebsite\Excel\Excel::XLSX);

        // // Save the file locally
        // $fileName = 'purchase_orders.xlsx';
        // Storage::disk('local')->put($fileName, $excelData);
        
        // // Dump the file path for debugging
        // dd(Storage::path($fileName));

        // Save the file locally for debugging
        $filePath = storage_path('app/public/purchase-order-report/' . $fileName);
        file_put_contents($filePath, $excelData);

        // Log file creation
        Log::info('Excel file created successfully.', ['file_path' => $filePath]);

        // Check if the file exists
        if (!file_exists($filePath)) {
            throw new \Exception('File does not exist at path: ' . $filePath);
        }
        
        // Log email building
        Log::info('Email built successfully.');

        return $this->view('emails.purchase-order-report')
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
      return "{$type} Purchase Order Report - " . $this->endDate->format('Y-m-d');
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
      return "purchase_order_report_{$type}_{$date}.xlsx";
  }


}