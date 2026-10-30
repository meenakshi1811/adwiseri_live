<?php

namespace App\Services;

use App\Mail\PaymentReminderMail;
use App\Models\Associate;
use App\Models\AssociateInvoice;
use App\Models\Clients;
use App\Models\Internal_Invoices;
use App\Models\Invoice_settings;
use App\Models\PaymentARs;
use App\Models\PaymentReminderSetting;
use App\Models\User;
use App\Models\UserRoles;
use App\Support\BrandedMail;
use Carbon\Carbon;

/**
 * Builds and sends payment reminder emails: the scheduled reminders (payments:send-reminders) and the
 * manual "Send reminder" buttons on Payments (AR) and Associates > Payments.
 */
class PaymentReminderService
{
    public function __construct(
        private ReminderScheduleService $scheduleService
    ) {
    }

    /**
     * Manual reminders email the subscriber's clients/associates, so staff need write access to the module
     * (as for Email Broadcast) and support staff can't send them.
     */
    public function userCanSend(User $user, string $module): bool
    {
        if ((int) ($user->is_support ?? 0) === 1) {
            return false;
        }

        if (in_array($user->user_type, ['Subscriber', 'admin'], true)) {
            return true;
        }

        $roles = UserRoles::where('user_id', $user->id)->where('module', $module)->first();

        return $roles && ($roles->write_only == 1 || $roles->read_write_only == 1);
    }

    /**
     * Placeholder values for PaymentReminderMail. $details: invoice_no, recipient_name, outstanding_amount,
     * service_description, due_date, and invoice_client_name when the email goes to someone other than the
     * client (an associate), so the client is named on its own line.
     */
    public function payload(User $subscriber, array $details, string $recipientEmail, string $paymentLink): array
    {
        $recipientName = (string) ($details['recipient_name'] ?? '-');
        $outstandingAmount = number_format((float) ($details['outstanding_amount'] ?? 0), 2, '.', '');
        $serviceDescription = (string) (($details['service_description'] ?? '') ?: '-');
        $dueDate = !empty($details['due_date']) ? Carbon::parse($details['due_date'])->format('d-m-Y') : '-';
        $invoiceNo = (string) ($details['invoice_no'] ?? '');

        return [
            'subscriber_name' => (string) $subscriber->name,
            'client_first_name' => $this->scheduleService->firstName($recipientName),
            'client_name' => $recipientName,
            'name' => $recipientName,
            'currency_symbol' => $this->currencySymbol((string) $subscriber->currency),
            'amount' => $outstandingAmount,
            'outstanding_amount' => $outstandingAmount,
            'invoice_no' => $invoiceNo,
            'invoice_id' => $invoiceNo,
            'service_description' => $serviceDescription,
            'application_service' => $serviceDescription,
            'invoice_client_name' => trim((string) ($details['invoice_client_name'] ?? '')),
            'payment_due_date' => $dueDate,
            'due_date' => $dueDate,
            'payment_link' => $paymentLink,
            'recipient_email' => $recipientEmail,
        ];
    }

    public function send(User $subscriber, string $recipientEmail, array $payload, bool $bccSubscriber): void
    {
        BrandedMail::sendWithAlertsArchive(
            $recipientEmail,
            fn () => new PaymentReminderMail($subscriber, $payload),
            function ($mail) use ($bccSubscriber, $subscriber) {
                if ($bccSubscriber && !empty($subscriber->email)) {
                    $mail->bcc($subscriber->email);
                }
            }
        );
    }

    public function settingsPaymentLink(User $subscriber): string
    {
        $invoiceSetting = Invoice_settings::where('user_id', $subscriber->id)->first();

        return trim((string) ($invoiceSetting?->payment_link ?? ''));
    }

    public function currencySymbol(string $currency): string
    {
        if (preg_match('/\((.*?)\)/', $currency, $match)) {
            return $match[1] ?? '';
        }

        return $currency;
    }

    /**
     * Current balance of a Payments (AR) invoice, worked out from the database at call time: the Invoice (AR)
     * total less everything paid against it, or for a stand-alone payment record its amount less paid.
     */
    public function arInvoiceBalance(int $subscriberId, string $invoiceNo): ?array
    {
        $payments = PaymentARs::where('subscriber_id', $subscriberId)
            ->where('type', 'ar')
            ->where('invoice_no', $invoiceNo)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $invoice = Internal_Invoices::where('subscriber_id', $subscriberId)
            ->where('type', 'ar')
            ->where('invoice_no', $invoiceNo)
            ->first();

        if (!$invoice && $payments->isEmpty()) {
            return null;
        }

        $total = $invoice ? (float) $invoice->total : (float) $payments->max('amount');
        $paid = (float) $payments->sum('paid_amount');
        $outstanding = round(max(0, $total - $paid), 2);

        $latest = $payments->first();
        $client = $latest && $latest->client_id
            ? Clients::where('subscriber_id', $subscriberId)->find($latest->client_id)
            : null;

        $service = $invoice ? trim((string) $invoice->detail) : '';
        if ($service === '' && $latest) {
            $service = trim((string) $latest->service_description);
        }

        return [
            'invoice_no' => $invoiceNo,
            'total' => round($total, 2),
            'paid' => round($paid, 2),
            'outstanding' => $outstanding,
            'cancelled' => $invoice && $invoice->status === 'Cancelled',
            'cleared' => $outstanding < 0.01 || ($invoice && $invoice->status === 'Paid'),
            'recipient_name' => trim((string) ($client->name ?? '')) ?: trim((string) ($invoice->to_name ?? '')),
            'recipient_email' => trim((string) ($client->email ?? '')) ?: trim((string) ($invoice->to_email ?? '')),
            'service_description' => $service,
            'due_date' => $invoice->due_date ?? null,
            'payment_link' => trim((string) ($invoice->payment_link ?? '')),
        ];
    }

    /**
     * Emails the client the latest outstanding amount of a Payments (AR) invoice.
     *
     * @return array{success:bool,status:int,message:string,recipient:?string,outstanding:?string}
     */
    public function sendArReminder(User $subscriber, string $invoiceNo): array
    {
        $balance = $this->arInvoiceBalance((int) $subscriber->id, $invoiceNo);

        if (!$balance) {
            return $this->result(false, 404, 'Invoice not found.');
        }

        if ($balance['cancelled']) {
            return $this->result(false, 422, 'This invoice has been cancelled, so no reminder was sent.');
        }

        if ($balance['cleared']) {
            return $this->result(false, 422, 'This invoice is already cleared, so no reminder was sent.');
        }

        return $this->deliverReminder(
            $subscriber,
            $balance['recipient_email'],
            [
                'invoice_no' => $invoiceNo,
                'recipient_name' => $balance['recipient_name'],
                'outstanding_amount' => $balance['outstanding'],
                'service_description' => $balance['service_description'],
                'due_date' => $balance['due_date'],
            ],
            $balance['payment_link'] ?: $this->settingsPaymentLink($subscriber),
            (bool) PaymentReminderSetting::forUserPayments((int) $subscriber->id)?->bccSubscriberForClients(),
            "The client's email address is missing or invalid. Please update it in the Clients module."
        );
    }

    /**
     * Emails the associate the latest outstanding amount of an associate invoice.
     *
     * @return array{success:bool,status:int,message:string,recipient:?string,outstanding:?string}
     */
    public function sendAssociateReminder(User $subscriber, AssociateInvoice $invoice, ?Associate $associate): array
    {
        if ($invoice->status === 'Cancelled') {
            return $this->result(false, 422, 'This invoice has been cancelled, so no reminder was sent.');
        }

        $outstanding = round((float) $invoice->outstanding, 2);
        if ($outstanding < 0.01 || $invoice->status === 'Paid') {
            return $this->result(false, 422, 'This invoice is already cleared, so no reminder was sent.');
        }

        return $this->deliverReminder(
            $subscriber,
            trim((string) ($associate->email ?? '')),
            [
                'invoice_no' => $invoice->invoice_no ?: $invoice->id,
                'recipient_name' => trim((string) ($associate->name ?? '')),
                'outstanding_amount' => $outstanding,
                'service_description' => $invoice->application_name ?: ($invoice->services ?: $invoice->service_provided),
                'invoice_client_name' => $invoice->client_name,
                'due_date' => $invoice->due_date,
            ],
            $this->settingsPaymentLink($subscriber),
            (bool) PaymentReminderSetting::forUserPayments((int) $subscriber->id)?->bccSubscriberForAssociates(),
            "The associate's email address is missing or invalid. Please update it in the Associates module."
        );
    }

    private function deliverReminder(
        User $subscriber,
        string $recipientEmail,
        array $details,
        string $paymentLink,
        bool $bccSubscriber,
        string $invalidEmailMessage
    ): array {
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->result(false, 422, $invalidEmailMessage);
        }

        $payload = $this->payload($subscriber, $details, $recipientEmail, $paymentLink);

        try {
            $this->send($subscriber, $recipientEmail, $payload, $bccSubscriber);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->result(false, 500, 'Unable to send the reminder email. Please try again.');
        }

        $amount = trim($payload['currency_symbol'] . ' ' . $payload['outstanding_amount']);

        return $this->result(
            true,
            200,
            'Payment reminder for ' . $amount . ' outstanding sent to ' . $recipientEmail . '.',
            $recipientEmail,
            $amount
        );
    }

    private function result(bool $success, int $status, string $message, ?string $recipient = null, ?string $outstanding = null): array
    {
        return [
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'recipient' => $recipient,
            'outstanding' => $outstanding,
        ];
    }
}
