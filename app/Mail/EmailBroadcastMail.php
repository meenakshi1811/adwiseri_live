<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $emailSubject,
        protected string $content,
        protected string $senderEmail,
        protected string $senderName
    ) {
    }

    public function build()
    {
        $headerTitle = $this->emailSubject;
        $content = $this->content;

        return $this->subject($this->emailSubject)
            ->from($this->senderEmail, $this->senderName)
            ->replyTo($this->senderEmail, $this->senderName)
            ->view('web.dynamic_email_template', compact('content', 'headerTitle'));
    }
}
