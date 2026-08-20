<?php

namespace App\Mail;

use App\Support\BrandedMail;
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
        $headerTitle = 'Support Ticket Closed';

        $content = '<p style="margin:0 0 12px 0;">Hello' . (!empty($data->name) ? ' ' . e($data->name) : '') . ',</p>'
            . '<p style="margin:0 0 12px 0;">Your support ticket has been closed.</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Ticket ID:</strong> ' . e($data->ticket_id) . '</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Department:</strong> ' . e($data->department ?? $data->support ?? '-') . '</p>'
            . '<p style="margin:0 0 12px 0;"><strong>Issue:</strong><br>' . nl2br(e($data->issue)) . '</p>';

        if (!empty($data->response)) {
            $content .= '<p style="margin:0 0 12px 0;"><strong>Admin Response:</strong><br>' . nl2br(e($data->response)) . '</p>';
        }

        if (!empty($data->ticket_url)) {
            $content .= '<p style="margin:0 0 12px 0;"><a href="' . e($data->ticket_url) . '" style="color:#695EEE;">View your ticket</a></p>';
        }

        $content .= '<p style="margin:0;">If you need more help, please raise a new support ticket from your Adwiseri account.</p>';

        return BrandedMail::applyPlatformEnvelope(
            $this->subject('Support Ticket Closed (' . $data->ticket_id . ')')
                ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }
}
