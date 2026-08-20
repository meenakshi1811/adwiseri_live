<?php

namespace App\Mail;

use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailboxAutoReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $mailbox;
    protected string $fromName;
    protected string $bodyText;
    protected string $mailSubject;

    public function __construct(string $mailbox, string $fromName, string $subject, string $bodyText)
    {
        $this->mailbox = strtolower(trim($mailbox));
        $this->fromName = $fromName;
        $this->mailSubject = $subject;
        $this->bodyText = $bodyText;
    }

    public function build()
    {
        $content = nl2br(e($this->bodyText));
        $headerTitle = 'Thank you for contacting Adwiseri';

        return $this
            ->from($this->mailbox, $this->fromName)
            ->replyTo($this->mailbox, $this->fromName)
            ->subject($this->mailSubject)
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'));
    }
}
