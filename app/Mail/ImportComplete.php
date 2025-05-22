<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportComplete extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     *
     * @param array $mailData
     * @return void
     */
    public function __construct(array $mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            Log::info('Building ImportComplete email', [
                'modelName' => $this->mailData['modelName'],
                'successful_items' => count($this->mailData['successful_items']),
                'failed_items' => count($this->mailData['failed_items']),
                'export_successful_url' => $this->mailData['export_successful_url'],
                'export_failed_url' => $this->mailData['export_failed_url'],
            ]);

            return $this->subject($this->mailData['modelName'] . ' Import Completion')
            ->view('emails.import_complete')
            ->with($this->mailData);
        } catch (\Exception $e) {
            Log::error('Error building ImportComplete email: ' . $e->getMessage(), [
                'exception' => $e,
                'mailData' => $this->mailData,
            ]);

            throw $e;
        }
    }
}