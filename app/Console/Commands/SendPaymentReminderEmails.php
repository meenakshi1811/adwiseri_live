<?php

namespace App\Console\Commands;

use App\Mail\ApplicationReminderMail;
use App\Mail\DocumentReminderMail;
use App\Mail\PaymentReminderMail;
use App\Models\ApplicationReminder;
use App\Models\AssociateInvoice;
use App\Models\Invoice_settings;
use App\Models\PaymentARs;
use App\Models\PaymentReminderSetting;
use App\Models\User;
use App\Services\DocumentReminderService;
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
        private DocumentReminderService $documentReminderService
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

            $rows = $setting->sendsToAssociates()
                ? $this->outstandingAssociateRowsForSubscriber($subscriber->id, $setting->client_group)
                : $this->outstandingRowsForSubscriber($subscriber->id, $setting->client_group);

            $invoiceSetting = Invoice_settings::where('user_id', $subscriber->id)->first();
            $paymentLink = trim((string) ($invoiceSetting?->payment_link ?? ''));

            $sentCount = 0;

            foreach ($rows as $row) {
                $recipientEmail = $setting->sendsToAssociates()
                    ? trim((string) ($row->associate_email ?? ''))
                    : trim((string) ($row->client_email ?? ''));

                if ($recipientEmail === '' || (float) $row->outstanding_amount <= 0) {
                    continue;
                }

                $dueDateObject = $row->due_date ? Carbon::parse($row->due_date) : null;
                if ($dueDateObject && $dueDateObject->isFuture()) {
                    continue;
                }

                $outstandingAmount = number_format((float) $row->outstanding_amount, 2, '.', '');
                $serviceDescription = (string) ($row->service_description ?: '-');
                $dueDate = $dueDateObject ? $dueDateObject->format('d-m-Y') : '-';
                $clientName = (string) ($row->client_name ?? '-');

                $payload = [
                    'subscriber_name' => (string) $subscriber->name,
                    'client_first_name' => $this->scheduleService->firstName($clientName),
                    'client_name' => $clientName,
                    'name' => $clientName,
                    'currency_symbol' => $this->currencySymbol((string) $subscriber->currency),
                    'amount' => $outstandingAmount,
                    'outstanding_amount' => $outstandingAmount,
                    'invoice_no' => (string) $row->invoice_no,
                    'invoice_id' => (string) $row->invoice_no,
                    'service_description' => $serviceDescription,
                    'application_service' => $serviceDescription,
                    'payment_due_date' => $dueDate,
                    'due_date' => $dueDate,
                    'payment_link' => $paymentLink,
                ];

                $payload['recipient_email'] = $recipientEmail;
                BrandedMail::sendWithAlertsArchive(
                    $recipientEmail,
                    fn () => new PaymentReminderMail($subscriber, $payload),
                    function ($mail) use ($setting, $subscriber) {
                        if ($setting->bccSubscriber() && !empty($subscriber->email)) {
                            $mail->bcc($subscriber->email);
                        }
                    }
                );
                $sentCount++;
            }

            $setting->last_sent_at = now();
            $setting->save();

            $this->info('Processed payment reminders for subscriber_id ' . $subscriber->id . ' (' . $sentCount . ' sent).');
        }
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

                BrandedMail::sendWithAlertsArchive(
                    $recipientEmail,
                    fn () => new DocumentReminderMail($subscriber, $payload),
                    function ($mail) use ($setting, $subscriber) {
                        if ($setting->bccSubscriber() && !empty($subscriber->email)) {
                            $mail->bcc($subscriber->email);
                        }
                    }
                );
                $sentCount++;
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

            BrandedMail::sendWithAlertsArchive(
                $recipientEmail,
                fn () => new ApplicationReminderMail($subscriber, $payload),
                function ($mail) use ($reminder, $subscriber, $recipientEmail) {
                    if ($reminder->bccSubscriber() && !empty($subscriber->email) && $subscriber->email !== $recipientEmail) {
                        $mail->bcc($subscriber->email);
                    }
                }
            );

            $reminder->last_sent_at = now();
            $reminder->save();

            $this->info('Processed application reminder #' . $reminder->id . ' for subscriber_id ' . $subscriber->id . '.');
        }
    }

    private function outstandingRowsForSubscriber(int $subscriberId, string $clientGroup)
    {
        $query = PaymentARs::query()
            ->from('payment_ar')
            ->join('clients', 'clients.id', '=', 'payment_ar.client_id')
            ->leftJoin('internal_invoices', function ($join) use ($subscriberId) {
                $join->on('internal_invoices.invoice_no', '=', 'payment_ar.invoice_no')
                    ->where('internal_invoices.subscriber_id', '=', $subscriberId);
            })
            ->where('payment_ar.subscriber_id', $subscriberId)
            ->whereRaw('LOWER(payment_ar.type) = ?', ['ar'])
            ->groupBy('payment_ar.client_id', 'payment_ar.invoice_no', 'payment_ar.service_description', 'clients.name', 'clients.email', 'internal_invoices.due_date')
            ->selectRaw('payment_ar.client_id, payment_ar.invoice_no, payment_ar.service_description, clients.name as client_name, clients.email as client_email, internal_invoices.due_date, SUM(payment_ar.amount - payment_ar.paid_amount) as outstanding_amount')
            ->havingRaw('SUM(payment_ar.amount - payment_ar.paid_amount) > 0');

        $this->applyAmountThreshold($query, $clientGroup, 'SUM(payment_ar.amount - payment_ar.paid_amount)', true);

        return $query->get();
    }

    private function outstandingAssociateRowsForSubscriber(int $subscriberId, string $clientGroup)
    {
        $query = AssociateInvoice::query()
            ->from('associate_invoices')
            ->join('associates', 'associates.id', '=', 'associate_invoices.associate_id')
            ->where('associate_invoices.subscriber_id', $subscriberId)
            ->whereIn('associate_invoices.status', ['UnPaid', 'PartiallyPaid'])
            ->whereRaw('(associate_invoices.fees - associate_invoices.paid) > 0')
            ->selectRaw('associate_invoices.invoice_no, associate_invoices.client_name, associate_invoices.service_provided as service_description, associate_invoices.due_date, associates.email as associate_email, (associate_invoices.fees - associate_invoices.paid) as outstanding_amount');

        $this->applyAmountThreshold($query, $clientGroup, '(associate_invoices.fees - associate_invoices.paid)', false);

        return $query->get();
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

    private function currencySymbol(string $currency): string
    {
        if (preg_match('/\((.*?)\)/', $currency, $match)) {
            return $match[1] ?? '';
        }

        return $currency;
    }
}
