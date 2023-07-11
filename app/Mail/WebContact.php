<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\{
    Content,
    Envelope,
};

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Data\Open\WebContactData;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WebContact extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [20, 60];

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * Create a new Web Contact instance.
     *
     * @param \App\Data\Open\WebContactData $data
     */
    public function __construct(private WebContactData $data)
    {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->data->subject . ' - ' . __('mails.web_contact.subject'));
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contacts.web',
            with: ['data' => $this->data],
        );
    }
}
