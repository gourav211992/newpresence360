<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class LoanUserMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $data)
    { 
        $this->data = $data;
        // $this->username = $data['username'];
        // $this->title = $data['title'];
        // $this->message = $data['message'];
        // $this->attachFiles = $data['attachFiles'] ?? [];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data['title'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.loan_user_message',
            with: [
                'data' => $this->data,
                //'attachmentInfo' => $this->prepareAttachmentInfo(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // $attachments = [];

        // foreach ($this->attachFiles as $docType => $files) {
        //     foreach ($files as $file) {
        //         if (file_exists($file) && is_readable($file)) {
        //             try {
        //                 $attachments[] = Attachment::fromPath($file)
        //                     ->as(basename($file))
        //                     ->withMime($this->getMimeType($file));
        //             } catch (\Exception $e) {
        //                 Log::warning("Failed to attach file: $file. Error: " . $e->getMessage());
        //             }
        //         } else {
        //             Log::warning("File does not exist or is not readable: $file");
        //         }
        //     }
        // }

        return [];
    }

    /**
     * Prepare attachment info for display in email body
     */
    // private function prepareAttachmentInfo(): array
    // {
    //     $info = [];
    //     foreach ($this->attachFiles as $docType => $files) {
    //         foreach ($files as $file) {
    //             if (file_exists($file) && is_readable($file)) {
    //                 $info[] = [
    //                     'name' => ucwords(str_replace('_', ' ', $docType)) . ' - ' . basename($file),
    //                     'path' => $file,
    //                 ];
    //             }
    //         }
    //     }
    //     return $info;
    // }

    /**
     * Get MIME type of the file
     */
    // private function getMimeType($file): string
    // {
    //     try {
    //         $finfo = finfo_open(FILEINFO_MIME_TYPE);
    //         $mimeType = finfo_file($finfo, $file);
    //         finfo_close($finfo);
    //         return $mimeType ?: 'application/octet-stream';
    //     } catch (\Exception $e) {
    //         Log::warning("Failed to determine MIME type for file: $file. Error: " . $e->getMessage());
    //         return 'application/octet-stream';
    //     }
    // }
}
