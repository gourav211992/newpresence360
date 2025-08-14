<?php

namespace App\Jobs;

use App\Services\Mailers\Mailer;
use App\Models\MailBox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable ,SerializesModels;

    protected $receiver;
    protected $title;
    protected $description;
    protected $cc;
    protected $bcc;
    protected $attachment;

    /**
     * Create a new job instance.
     */
    public function __construct($receiver,$sender, $sender_name ,$title, $description,$cc = null, $bcc = null, $attachment = null)
    {
        $this->sender = $sender;
        $this->sender_name = $sender_name;
        $this->receiver = $receiver->email;
        $this->title = $title;
        $this->description = $description;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->attachment = $attachment;
        Log::info('SendEmailJob', [
            'receiver' => $this->receiver,
            'sender' => $this->sender,
            'title' => $this->title,
            'description' => $this->description,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'attachment' => $this->attachment,
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            file_put_contents(storage_path('logs/handle-debug.txt'), 'HANDLE EXECUTED');
            Log::info('Sending email', [
                'receiver' => $this->receiver,
                'title' => $this->title,
                'description' => $this->description,
                'cc' => $this->cc,
                'bcc' => $this->bcc,
                'attachment' => $this->attachment,
            ]);
            // Create new Mailer and MailBox objects
            $mailer = new Mailer;
            $mailBox = new MailBox;
            // $mailBox->mail_from = $this->sender;
            // $mailBox->mail_from_name = $this->sender_name;
            $mailBox->mail_to = $this->receiver;
            $mailBox->mail_cc = is_array($this->cc) ? implode(',', $this->cc) : ($this->cc ?? null); ;
            $mailBox->mail_bcc = is_array($this->bcc) ? implode(',', $this->bcc) : ($this->bcc ?? null);
            $mailBox->attachment = is_array($this->attachment) ? json_encode($this->attachment) : ($this->attachment ?? []);
            $mailBox->layout = "emails.template";
            $mailBox->mail_body = json_encode([
                'title' => $this->title,
                'description' => $this->description,
            ]);
            $mailBox->subject = $this->title;
            Log::info('MailBox created', [
                'mail_to' => $mailBox->mail_to,
                'mail_cc' => $mailBox->mail_cc,
                'mail_bcc' => $mailBox->mail_bcc,
                'attachment' => $mailBox->attachment,
                'subject' => $mailBox->subject,
            ]);
            // Send email using Mailer service
            $mailer->emailTo($mailBox);

        } catch (\Exception $e) {
            // Log failure in mail status table
            Log::error('Email sending failed:', [
                'receiver' => $this->receiver,
                'title' => $this->title,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
