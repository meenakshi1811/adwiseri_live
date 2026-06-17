<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketClosureMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    public function __construct($maildata)
    {
        $this->data = $maildata;
    }

    public function build()
    {
        $data = $this->data;
        $content = '<p>Your support ticket has been closed.</p>'
            . '<p><strong>Ticket ID:</strong> ' . e($data->ticket_id) . '</p>'
            . '<p><strong>Department:</strong> ' . e($data->department) . '</p>'
            . '<p><strong>Issue:</strong> ' . nl2br(e($data->issue)) . '</p>';

        if (!empty($data->response)) {
            $content .= '<p><strong>Admin Response:</strong><br>' . nl2br(e($data->response)) . '</p>';
        }

        $content .= '<p>If you need more help, please raise a new support ticket from your Adwiseri account.</p>';

        return $this->subject('Support Ticket Closed (' . $data->ticket_id . ')')
            ->view('web.dynamic_email_template', compact('content'));
    }
}
