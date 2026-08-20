<?php

namespace App\Mail;

use App\Support\BrandedMail;
use App\Support\InvoiceMailAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Invoicemail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var object */
    public $data;

    public function __construct($maildata)
    {
        $this->data = $maildata;
    }

    public function build()
    {
        $data = $this->data;
        $content = BrandedMail::renderBody('emails.bodies.invoice', compact('data'));
        $headerTitle = 'Invoice ' . ($data->invoice_no ?? '');

        $subscriberLabel = BrandedMail::stripSentOnBehalfPrefix(
            $data->from_name ?? ($data->company_name ?? 'Subscriber')
        );

        $mail = $this->subject('New Invoice ' . ($data->invoice_no ?? ''))
            ->from(BrandedMail::alertsFromAddress(), BrandedMail::alertsFromName($subscriberLabel))
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle'));

        $subscriberReplyEmail = trim((string) ($data->subscriber_email ?? $data->reply_to_email ?? ''));
        if ($subscriberReplyEmail !== '') {
            BrandedMail::applySubscriberReplyTo(
                $mail,
                $subscriberReplyEmail,
                $data->reply_to_name ?? ($data->subscriber_name ?? null)
            );
        } else {
            BrandedMail::applyDefaultReplyTo($mail);
        }

        $mail = InvoiceMailAttachment::attachInvoicePdf($mail, $data);

        return $mail;
    }
}
