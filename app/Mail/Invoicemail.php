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
        $issuer = !empty($data->issuer_user_id) ? \App\Models\User::find($data->issuer_user_id) : null;
        $fromSubscriber = $issuer && ($issuer->user_type ?? '') !== 'admin';
        // A subscriber's invoice to their client is signed with their business name; Adwiseri's own keep "Adwiseri".
        $data->signature_name = $fromSubscriber ? BrandedMail::businessName($issuer) : null;
        $content = BrandedMail::renderBody('emails.bodies.invoice', compact('data'));
        $headerTitle = 'Invoice ' . ($data->invoice_no ?? '');

        $subscriberLabel = BrandedMail::stripSentOnBehalfPrefix(
            $data->from_name ?? ($data->company_name ?? 'Subscriber')
        );

        $mail = $this->subject('New Invoice ' . ($data->invoice_no ?? ''))
            ->from(BrandedMail::alertsFromAddress(), BrandedMail::alertsFromName($subscriberLabel))
            ->view(BrandedMail::LAYOUT, compact('content', 'headerTitle') + ($fromSubscriber ? BrandedMail::subscriberLayoutData($issuer) : []));

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
