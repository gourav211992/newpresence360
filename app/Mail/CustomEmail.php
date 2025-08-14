<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $body;
    public $attachs;

    /**
     * Create a new message instance.
     *
     * @param string $body
     * @param array $attachments
     * @return void
     */
    public function __construct($body, array $attachs = [])
    {
        $this->body = $body;
        $this->attachs = $attachs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        // Only pass attachments to the view, not to the email itself
        $email = $this->view('legal.mailer')
                      ->with('body', $this->body)
                      ->with('attachs', $this->attachs);

        // Optionally set the subject
        $email->subject('Your Subject Here'); // Customize as needed

        return $email;
    }
}


