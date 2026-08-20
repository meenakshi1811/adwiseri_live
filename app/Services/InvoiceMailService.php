<?php

namespace App\Services;

use App\Mail\Invoicemail;
use App\Models\Internal_Invoices;
use App\Models\User;
use App\Support\BrandedMail;
use App\Support\InvoiceIssuerLogo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InvoiceMailService
{
    public function __construct(
        private InvoiceItemService $itemService,
        private InvoiceSnapshotService $snapshotService
    ) {
    }

    public function resolveSender(Internal_Invoices $invoice): ?User
    {
        if (!empty($invoice->user_id)) {
            $user = User::find($invoice->user_id);
            if ($user) {
                return $user;
            }
        }

        if (!empty($invoice->email)) {
            return User::where('email', $invoice->email)->first();
        }

        if (!empty($invoice->subscriber_id)) {
            return User::find($invoice->subscriber_id);
        }

        return null;
    }

    public function recipientEmail(Internal_Invoices $invoice, ?string $override = null): ?string
    {
        $email = trim((string) ($override ?? $invoice->to_email ?? ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function buildMailData(Internal_Invoices $invoice, ?User $sender = null): \stdClass
    {
        $invoice->loadMissing('items');
        $issuer = $sender ?: $this->resolveSender($invoice);
        $issuerLogo = InvoiceIssuerLogo::resolve($invoice, $issuer);
        $subscriberId = (int) ($invoice->subscriber_id ?: ($issuerLogo['owner_user_id'] ?? ($issuer?->id ?? 0)));
        $senderOrg = trim((string) ($issuer?->organization ?? $invoice->name ?? 'Adwiseri'));
        $senderEmail = trim((string) ($issuer?->email ?? $invoice->email ?? ''));
        $currency = trim((string) ($issuer?->currency ?? 'Rs.'));
        $qrFilename = $this->snapshotService->resolvedPaymentQrFilename($invoice);
        $qrPath = null;
        $qrUrl = null;

        if ($qrFilename !== '' && $subscriberId > 0) {
            $diskPath = public_path('web_assets/users/user' . $subscriberId . '/' . $qrFilename);
            if (file_exists($diskPath)) {
                $qrPath = $diskPath;
                $qrUrl = asset('web_assets/users/user' . $subscriberId . '/' . $qrFilename);
            }
        }

        $maildata = new \stdClass();
        $maildata->name = $invoice->to_name ?? '-';
        $maildata->email = $senderEmail;
        $maildata->from_email = BrandedMail::alertsFromAddress();
        $maildata->to_email = $invoice->to_email;
        $maildata->company_name = $senderOrg !== '' ? $senderOrg : 'Adwiseri';
        $maildata->subscriber_name = $maildata->company_name;
        $maildata->subscriber_email = $senderEmail;
        $maildata->display_from_email = $senderEmail;
        $maildata->subscriber_id = $subscriberId > 0 ? $subscriberId : null;
        $maildata->user_id = $invoice->user_id;
        $maildata->logo = $invoice->logo;
        $maildata->logo_path = $issuerLogo['relative_path'];
        $maildata->detail = $invoice->detail;
        $maildata->amount = $invoice->amount;
        $maildata->items = $this->itemService->itemsForMail($invoice);
        $maildata->discount = $invoice->discount;
        $maildata->tax = $invoice->tax;
        $maildata->tax_label = $invoice->tax_label;
        $maildata->export_service_tax_exempt = (bool) ($invoice->export_service_tax_exempt ?? false);
        $maildata->total = $invoice->total;
        $maildata->currency = $currency;
        $maildata->status = $invoice->status;
        $maildata->invoice_no = $invoice->invoice_no;
        $maildata->invoice_date = $invoice->created_at;
        $maildata->due_date = $invoice->due_date;
        $maildata->invoice_id = $invoice->id;
        $maildata->token = $invoice->token;
        $maildata->invoice_note = $this->snapshotService->resolvedInvoiceNote($invoice);
        $maildata->to_address = $invoice->to_address;
        $maildata->to_city = $invoice->to_city;
        $maildata->to_state = $invoice->to_state;
        $maildata->to_country = $invoice->to_country;
        $maildata->to_pincode = $invoice->to_pincode;
        $maildata->payment_link = $this->snapshotService->resolvedPaymentLink($invoice);
        $maildata->payment_qr_url = $qrUrl;
        $maildata->payment_qr_path = $qrPath;
        $maildata->message = 'You have new invoice from ' . $maildata->company_name
            . ' for ' . $currency . ' ' . number_format((float) $invoice->total, 2) . '.';
        $maildata->from_name = BrandedMail::sentOnBehalfOf($maildata->company_name);
        $maildata->reply_to_email = $senderEmail;
        $maildata->reply_to_name = $maildata->company_name;

        if (!empty($invoice->uploaded_invoice)) {
            $uploadedPath = public_path('web_assets/users/' . ltrim((string) $invoice->uploaded_invoice, '/'));
            if (is_readable($uploadedPath)) {
                $maildata->uploaded_invoice_path = $uploadedPath;
            }
        }

        return $maildata;
    }

    public function mailConfigSummary(): array
    {
        return [
            'mailer' => (string) config('mail.default'),
            'from' => BrandedMail::alertsFromAddress(),
            'bcc' => BrandedMail::alertsBccRecipients(),
        ];
    }

    public function send(Internal_Invoices $invoice, ?User $sender = null, ?string $toEmail = null): array
    {
        $recipient = $this->recipientEmail($invoice, $toEmail);
        if ($recipient === null) {
            return [
                'success' => false,
                'message' => 'No valid recipient email address is set on this invoice.',
                'recipient' => null,
            ];
        }

        $fromAddress = BrandedMail::alertsFromAddress();
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'MAIL_FROM_ADDRESS (or MAIL_ALERTS_FROM_ADDRESS) is missing or invalid in .env.',
                'recipient' => $recipient,
            ];
        }

        try {
            $maildata = $this->buildMailData($invoice, $sender);
            BrandedMail::sendWithAlertsArchive($recipient, fn () => new Invoicemail($maildata));

            if (method_exists(Mail::class, 'failures')) {
                $failures = Mail::failures();
                if (!empty($failures)) {
                    Log::warning('Invoice email SMTP failures', [
                        'invoice_id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'failures' => $failures,
                    ]);

                    return [
                        'success' => false,
                        'message' => 'SMTP rejected recipient(s): ' . implode(', ', $failures),
                        'recipient' => $recipient,
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Invoice email sent to ' . $recipient . ' (archive copy to ' . implode(', ', BrandedMail::alertsBccRecipients()) . ').',
                'recipient' => $recipient,
            ];
        } catch (Throwable $e) {
            Log::error('Invoice email failed', [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'recipient' => $recipient,
                'mailer' => config('mail.default'),
                'from' => $fromAddress,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Email failed: ' . $e->getMessage(),
                'recipient' => $recipient,
            ];
        }
    }
}
