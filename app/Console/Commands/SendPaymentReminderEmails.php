<?php

namespace App\Console\Commands;

use App\Mail\ApplicationReminderMail;
use App\Mail\DocumentReminderMail;
use App\Models\ApplicationReminder;
use App\Models\AssociateInvoice;
use App\Models\Internal_Invoices;
use App\Models\PaymentARs;
use App\Models\PaymentReminderSetting;
use App\Models\User;
use App\Services\DocumentReminderService;
use App\Services\PaymentReminderService;
use App\Services\ReminderScheduleService;
use App\Support\BrandedMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SendPaymentReminderEmails extends Command
{
    protected $signature = 'payments:send-reminders';

    protected $description = 'Send payment, document, and application reminder emails based on subscriber reminder settings.';

    public function __construct(
        private ReminderScheduleService $scheduleService,
        private DocumentReminderService $documentReminderService,
        private PaymentReminderService $paymentReminderService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->sendPaymentReminders();
        $this->sendDocumentReminders();
        $this->sendApplicationReminders();

        return Command::SUCCESS;
    }

    private function sendPaymentReminders(): void
    {
        $settings = PaymentReminderSetting::query()->payments()->get();

        foreach ($settings as $setting) {
            $subscriber = User::find($setting->user_id);
            if (!$subscriber) {
                continue;
            }

            if (!$this->scheduleService->shouldRunAtScheduledTime($subscriber)) {
                continue;
            }

            if (!$this->scheduleService->shouldRunForFrequency($setting->last_sent_at, (string) $setting->email_frequency, $subscriber)) {
                continue;
            }

            $sentCount = 0;

            if ($setting->remindsClients()) {
                $sentCount += $this->sendPaymentReminderRows(
                    $setting,
                    $subscriber,
                    $this->outstandingRowsForSubscriber($subscriber->id, $setting->client_group),
                    fn ($row) => trim((string) ($row->client_email ?? '')),
                    $setting->bccSubscriberForClients()
                );
            }

            if ($setting->remindsAssociates()) {
                $sentCount += $this->sendPaymentReminderRows(
                    $setting,
                    $subscriber,
                    $this->outstandingAssociateRowsForSubscriber($subscriber->id, $setting->client_group),
                    fn ($row) => trim((string) ($row->associate_email ?? '')),
                    $setting->bccSubscriberForAssociates()
                );
            }

            $setting->last_sent_at = now();
            $setting->save();

            $this->info('Processed payment reminders for subscriber_id ' . $subscriber->id . ' (' . $sentCount . ' sent).');
        }
    }

    /**
     * @param  iterable<int, object>  $rows
     */
    private function sendPaymentReminderRows(
        PaymentReminderSetting $setting,
        User $subscriber,
        iterable $rows,
        callable $resolveRecipientEmail,
        bool $bccSubscriber
    ): int {
        $paymentLink = $this->paymentReminderService->settingsPaymentLink($subscriber);
        $sentCount = 0;

        foreach ($rows as $row) {
            $recipientEmail = $resolveRecipientEmail($row);

            if ($recipientEmail === '' || (float) $row->outstanding_amount <= 0) {
                continue;
            }

            $dueDateObject = $row->due_date ? Carbon::parse($row->due_date) : null;
            if ($dueDateObject && $dueDateObject->isFuture()) {
                continue;
            }

            $payload = $this->paymentReminderService->payload($subscriber, [
                'invoice_no' => $row->invoice_no,
                'recipient_name' => $row->recipient_name ?? '-',
                'outstanding_amount' => $row->outstanding_amount,
                'service_description' => $row->service_description,
                'invoice_client_name' => $row->invoice_client_name ?? '',
                'due_date' => $dueDateObject,
            ], $recipientEmail, $paymentLink);

            $sent = $this->deliver(
                $recipientEmail,
                fn () => $this->paymentReminderService->send($subscriber, $recipientEmail, $payload, $bccSubscriber),
                'payment reminder for invoice ' . $row->invoice_no
            );
            if ($sent) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    private function sendDocumentReminders(): void
    {
        $settings = PaymentReminderSetting::query()->documents()->get();

        foreach ($settings as $setting) {
            $subscriber = User::find($setting->user_id);
            if (!$subscriber) {
                continue;
            }

            if (!$this->scheduleService->shouldRunAtScheduledTime($subscriber)) {
                continue;
            }

            if (!$this->scheduleService->shouldRunForFrequency($setting->last_sent_at, (string) $setting->email_frequency, $subscriber)) {
                continue;
            }

            $sentCount = 0;

            foreach ($this->documentReminderService->applicationsWithMissingDocuments($subscriber) as $row) {
                /** @var \App\Models\Applications $application */
                $application = $row['application'];
                $recipientEmail = trim((string) $row['client_email']);
                if ($recipientEmail === '') {
                    continue;
                }

                $payload = [
                    'subscriber_name' => (string) $subscriber->name,
                    'client_name' => $row['client_name'],
                    'client_first_name' => $this->scheduleService->firstName($row['client_name']),
                    'application_name' => (string) ($application->application_name ?? ''),
                    'missing_documents' => $row['missing_documents'],
                    'recipient_email' => $recipientEmail,
                ];

                $sent = $this->deliver($recipientEmail, fn () => BrandedMail::sendWithAlertsArchive(
                    $recipientEmail,
                    fn () => new DocumentReminderMail($subscriber, $payload),
                    function ($mail) use ($setting, $subscriber) {
                        if ($setting->bccSubscriber() && !empty($subscriber->email)) {
                            $mail->bcc($subscriber->email);
                        }
                    }
                ), 'document reminder for application #' . $application->id);
                if ($sent) {
                    $sentCount++;
                }
            }

            $setting->last_sent_at = now();
            $setting->save();

            $this->info('Processed document reminders for subscriber_id ' . $subscriber->id . ' (' . $sentCount . ' sent).');
        }
    }

    private function sendApplicationReminders(): void
    {
        if (!Schema::hasTable('application_reminders')) {
            return;
        }

        $reminders = ApplicationReminder::query()
            ->where('is_active', true)
            ->with(['subscriber', 'client', 'application', 'notifyUser'])
            ->get();

        foreach ($reminders as $reminder) {
            $subscriber = $reminder->subscriber;
            if (!$subscriber) {
                continue;
            }

            if (!$this->scheduleService->shouldRunAtScheduledTime($subscriber)) {
                continue;
            }

            if (!$this->scheduleService->shouldRunForFrequency($reminder->last_sent_at, (string) $reminder->email_frequency, $subscriber)) {
                continue;
            }

            $notifyUser = $reminder->notifyUser ?: $subscriber;
            $recipientEmail = trim((string) ($notifyUser->email ?? ''));
            if ($recipientEmail === '') {
                continue;
            }

            $payload = [
                'subscriber_name' => (string) $subscriber->name,
                'user_name' => (string) ($notifyUser->name ?? $subscriber->name),
                'recipient_name' => (string) ($notifyUser->name ?? $subscriber->name),
                'client_name' => (string) optional($reminder->client)->name,
                'application_name' => (string) optional($reminder->application)->application_name,
                'subject' => (string) $reminder->subject,
                'description' => (string) ($reminder->description ?? ''),
                'deadline' => $reminder->deadline ? $reminder->deadline->format('d-m-Y') : '-',
                'recipient_email' => $recipientEmail,
            ];

            $this->deliver($recipientEmail, fn () => BrandedMail::sendWithAlertsArchive(
                $recipientEmail,
                fn () => new ApplicationReminderMail($subscriber, $payload),
                function ($mail) use ($reminder, $subscriber, $recipientEmail) {
                    if ($reminder->bccSubscriber() && !empty($subscriber->email) && $subscriber->email !== $recipientEmail) {
                        $mail->bcc($subscriber->email);
                    }
                }
            ), 'application reminder #' . $reminder->id);

            $reminder->last_sent_at = now();
            $reminder->save();

            $this->info('Processed application reminder #' . $reminder->id . ' for subscriber_id ' . $subscriber->id . '.');
        }
    }

    /**
     * What clients still owe: each Invoice (AR) total less everything paid against it in Payments (AR),
     * plus stand-alone Payments (AR) records (no invoice) with a balance left.
     *
     * Payment rows only exist once a payment is recorded and each carries the full invoice total, so they
     * can't be summed per invoice: an unpaid invoice has no rows at all, and part-payments would be overcounted.
     */
    private function outstandingRowsForSubscriber(int $subscriberId, string $clientGroup)
    {
        $paidPerInvoice = PaymentARs::query()
            ->where('subscriber_id', $subscriberId)
            ->whereRaw('LOWER(type) = ?', ['ar'])
            ->groupBy('invoice_no')
            ->selectRaw('invoice_no, SUM(paid_amount) as paid_total');

        $outstanding = '(internal_invoices.total - COALESCE(paid.paid_total, 0))';
        $invoices = Internal_Invoices::query()
            ->from('internal_invoices')
            ->leftJoinSub($paidPerInvoice, 'paid', 'paid.invoice_no', '=', 'internal_invoices.invoice_no')
            ->where('internal_invoices.subscriber_id', $subscriberId)
            ->whereRaw('LOWER(internal_invoices.type) = ?', ['ar'])
            ->where(function ($query) {
                $query->whereNull('internal_invoices.status')->orWhereNotIn('internal_invoices.status', ['Paid', 'Cancelled']);
            })
            ->whereRaw($outstanding . ' > 0')
            ->selectRaw('internal_invoices.invoice_no, internal_invoices.detail as service_description, internal_invoices.to_name as recipient_name, internal_invoices.to_email as client_email, internal_invoices.due_date, ' . $outstanding . ' as outstanding_amount');
        $this->applyAmountThreshold($invoices, $clientGroup, $outstanding, false);

        $standalonePayments = PaymentARs::query()
            ->from('payment_ar')
            ->join('clients', 'clients.id', '=', 'payment_ar.client_id')
            ->where('payment_ar.subscriber_id', $subscriberId)
            ->whereRaw('LOWER(payment_ar.type) = ?', ['ar'])
            ->whereNotExists(function ($query) use ($subscriberId) {
                $query->selectRaw('1')
                    ->from('internal_invoices')
                    ->whereColumn('internal_invoices.invoice_no', 'payment_ar.invoice_no')
                    ->where('internal_invoices.subscriber_id', $subscriberId);
            })
            ->groupBy('payment_ar.invoice_no', 'payment_ar.service_description', 'clients.name', 'clients.email')
            ->selectRaw('payment_ar.invoice_no, payment_ar.service_description, clients.name as recipient_name, clients.email as client_email, NULL as due_date, MAX(payment_ar.amount) - SUM(payment_ar.paid_amount) as outstanding_amount')
            ->havingRaw('MAX(payment_ar.amount) - SUM(payment_ar.paid_amount) > 0');
        $this->applyAmountThreshold($standalonePayments, $clientGroup, 'MAX(payment_ar.amount) - SUM(payment_ar.paid_amount)', true);

        return $invoices->get()->concat($standalonePayments->get());
    }

    /**
     * Associate invoices with a balance left. The email goes to the associate, so it greets them and names
     * the client on a line of its own.
     */
    private function outstandingAssociateRowsForSubscriber(int $subscriberId, string $clientGroup)
    {
        $query = AssociateInvoice::query()
            ->from('associate_invoices')
            ->join('associates', 'associates.id', '=', 'associate_invoices.associate_id')
            ->where('associate_invoices.subscriber_id', $subscriberId)
            ->whereIn('associate_invoices.status', ['UnPaid', 'PartiallyPaid'])
            ->whereRaw('(associate_invoices.fees - associate_invoices.paid) > 0')
            ->selectRaw("associate_invoices.invoice_no, associate_invoices.client_name as invoice_client_name, COALESCE(NULLIF(associate_invoices.application_name, ''), NULLIF(associate_invoices.services, ''), associate_invoices.service_provided) as service_description, associate_invoices.due_date, associates.name as recipient_name, associates.email as associate_email, (associate_invoices.fees - associate_invoices.paid) as outstanding_amount");

        $this->applyAmountThreshold($query, $clientGroup, '(associate_invoices.fees - associate_invoices.paid)', false);

        return $query->get();
    }

    /**
     * Sends one reminder. A bad address or mail error is logged and skipped so it can't stop everyone else's
     * reminders, or leave the run unrecorded so the next scheduler pass sends the same emails again.
     */
    private function deliver(string $recipientEmail, callable $send, string $context): bool
    {
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->warn('Skipped ' . $context . ': invalid email address "' . $recipientEmail . '".');

            return false;
        }

        try {
            $send();

            return true;
        } catch (\Throwable $exception) {
            report($exception);
            $this->warn('Failed ' . $context . ': ' . $exception->getMessage());

            return false;
        }
    }

    private function applyAmountThreshold($query, string $clientGroup, string $amountExpression, bool $useHaving = false): void
    {
        $threshold = PaymentReminderSetting::CLIENT_GROUP_THRESHOLDS[$clientGroup] ?? null;

        if ($threshold !== null) {
            if ($useHaving) {
                $query->havingRaw($amountExpression . ' > ?', [$threshold]);
            } else {
                $query->whereRaw($amountExpression . ' > ?', [$threshold]);
            }
        }
    }
}
