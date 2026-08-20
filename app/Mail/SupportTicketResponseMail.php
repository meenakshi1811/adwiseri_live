<?php

namespace App\Mail;

use App\Support\BrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected object $data,
        protected string $emailSubject,
        protected string $intro
    ) {
    }

    public function build()
    {
        $data = $this->data;
        $headerTitle = 'Support Ticket Update';

        $content = '<p style="margin:0 0 12px 0;">Hello' . (!empty($data->name) ? ' ' . e($data->name) : '') . ',</p>'
            . '<p style="margin:0 0 12px 0;">' . e($this->intro) . '</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Ticket ID:</strong> ' . e($data->ticket_id) . '</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Department:</strong> ' . e($data->department ?? $data->support ?? '-') . '</p>'
            . '<p style="margin:0 0 12px 0;"><strong>Issue:</strong><br>' . nl2br(e($data->issue)) . '</p>';

        if (!empty($data->response)) {
            $content .= '<p style="margin:0 0 12px 0;"><strong>Response:</strong><br>' . nl2br(e($data->response)) . '</p>';
        }

        if (!empty($data->ticket_url)) {
            $content .= '<p style="margin:0 0 12px 0;"><a href="' . e($data->ticket_url) . '" style="color:#695EEE;">View your ticket</a></p>';
        }

        $content .= '<p style="margin:0;">Thank you for using Adwiseri support.</p>';

        return BrandedMail::applyPlatformEnvelope(
            $this->subject($this->emailSubject)
                ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'))
        );
    }
}
