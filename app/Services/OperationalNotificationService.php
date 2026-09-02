<?php

namespace App\Services;

use App\Models\Applications;
use App\Models\ClientAccount;
use App\Models\Clients;
use App\Models\Internal_Invoices;
use App\Models\PaymentARs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class OperationalNotificationService
{
    public const CLOSED_STATUSES = [
        'Decision',
        'Appeal Decision',
        'AR / JR Decision',
        'Withdrawn',
        'Cancelled',
        'Closed',
    ];

    public function __construct(private NotificationService $notifications)
    {
    }

    public function notifyApplicationAssigned(User $assignee, Applications $application, ?Clients $client = null): void
    {
        $client = $client ?: Clients::find($application->client_id);
        $clientName = $client?->name ?: 'Client';
        $appName = $application->application_name ?: 'Application';
        $appCode = $application->application_id ?: $application->id;

        $title = sprintf(
            'New Application (%s - %s(%s)) has been assigned to you.',
            $clientName,
            $appName,
            $appCode
        );

        $this->notifications->notifyUser(
            $assignee,
            'application_assigned',
            $title,
            $title,
            $this->applicationLink($application),
            [
                'application_id' => $application->id,
                'client_id' => $client?->id,
                'assigned_to' => $assignee->id,
            ]
        );
    }

    public function notifyApplicationClosure(Applications $application, string $status, ?Clients $client = null): void
    {
        if (!in_array($status, self::CLOSED_STATUSES, true)) {
            return;
        }

        $subscriber = User::find($application->subscriber_id);
        if (!$subscriber || strtolower((string) $subscriber->user_type) !== 'subscriber') {
            $client = $client ?: Clients::find($application->client_id);
            $subscriber = $client ? User::find($client->subscriber_id) : null;
        }

        if (!$subscriber) {
            return;
        }

        $client = $client ?: Clients::find($application->client_id);
        $appCode = $application->application_id ?: $application->id;
        $clientId = $client?->id ?: ($application->client_id ?: 'N/A');

        $title = sprintf(
            '%s ::--> Application(%s) for Client (%s)',
            $status,
            $appCode,
            $clientId
        );

        $this->notifications->notifyUser(
            $subscriber,
            'application_closure',
            $title,
            $title,
            $this->applicationLink($application),
            [
                'application_id' => $application->id,
                'client_id' => $client?->id,
                'status' => $status,
            ]
        );
    }

    public function notifyFullPaymentReceived(
        int $subscriberId,
        ?Clients $client,
        ?Applications $application,
        ?string $invoiceNo = null
    ): void {
        $subscriber = User::find($subscriberId);
        if (!$subscriber) {
            return;
        }

        $clientName = $client?->name ?: 'Client';
        $clientId = $client?->id ?: 'N/A';
        $appName = $application?->application_name ?: 'Application';
        $appCode = $application
            ? ($application->application_id ?: $application->id)
            : ($invoiceNo ?: 'N/A');

        $title = sprintf(
            'Full Payment Received ::--> %s (%s) - %s (%s)',
            $clientName,
            $clientId,
            $appName,
            $appCode
        );

        $this->notifications->notifyConsultancyUsers(
            $subscriber,
            'full_payment_received',
            $title,
            $title,
            $application ? $this->applicationLink($application) : route('my_payments'),
            [
                'client_id' => $client?->id,
                'application_id' => $application?->id,
                'invoice_no' => $invoiceNo,
            ]
        );
    }

    public function notifyServiceDiscontinued(User $subscriber, string $serviceName): void
    {
        $title = $serviceName . ' is discontinued';

        $this->notifications->notifyConsultancyUsers(
            $subscriber,
            'service_withdrawal',
            $title,
            $title,
            route('my_settings') . '#service',
            ['service_name' => $serviceName]
        );
    }

    public function notifyServiceDeactivated(User $subscriber, string $serviceName): void
    {
        $title = sprintf('Service %s has been deactivated.', $serviceName);

        $this->notifications->notifyConsultancyUsers(
            $subscriber,
            'service_withdrawal',
            $title,
            $title,
            route('my_settings') . '#service',
            [
                'service_name' => $serviceName,
                'status' => 'deactivated',
            ]
        );
    }

    public function notifyServiceFeeUpdated(User $subscriber, string $serviceName, $newFees): void
    {
        $amount = is_numeric($newFees) ? number_format((float) $newFees, 2, '.', '') : (string) $newFees;
        $title = sprintf('Fees for %s have been changed to %s.', $serviceName, $amount);

        $this->notifications->notifyConsultancyUsers(
            $subscriber,
            'service_fee_updated',
            $title,
            $title,
            route('my_settings') . '#service',
            [
                'service_name' => $serviceName,
                'new_fees' => $amount,
            ]
        );
    }

    /**
     * Monday 08:00 local time — outstanding payments for closed applications.
     */
    public function dispatchWeeklyOutstandingDigests(): int
    {
        $sent = 0;
        $subscribers = User::query()
            ->where('user_type', 'Subscriber')
            ->get();

        foreach ($subscribers as $subscriber) {
            $timezone = $this->resolveTimezone($subscriber);
            $now = Carbon::now($timezone);

            if ((int) $now->format('G') !== 8 || (int) $now->format('i') !== 0) {
                continue;
            }

            if ($now->dayOfWeek !== Carbon::MONDAY) {
                continue;
            }

            $cacheKey = 'outstanding_payments_digest_' . $subscriber->id . '_' . $now->format('o-\\WW');
            if (Cache::has($cacheKey)) {
                continue;
            }

            $lines = $this->outstandingClosedApplicationLines((int) $subscriber->id);
            if (empty($lines)) {
                Cache::put($cacheKey, 1, $now->copy()->addDays(2));
                continue;
            }

            $body = "List of outstanding payments\n\n" . implode("\n", $lines);
            $created = $this->notifications->notifyUser(
                $subscriber,
                'outstanding_payments',
                'List of outstanding payments',
                $body,
                route('my_payments'),
                [
                    'week' => $now->format('o-W'),
                    'count' => count($lines),
                ]
            );

            if ($created) {
                $sent++;
            }

            Cache::put($cacheKey, 1, $now->copy()->addDays(8));
        }

        return $sent;
    }

    /**
     * @return list<string>
     */
    public function outstandingClosedApplicationLines(int $subscriberId): array
    {
        $applications = Applications::query()
            ->with('client')
            ->where('subscriber_id', $subscriberId)
            ->whereIn('application_status', self::CLOSED_STATUSES)
            ->get();

        $lines = [];

        foreach ($applications as $application) {
            $outstanding = $this->resolveApplicationOutstanding($subscriberId, $application);
            if ($outstanding <= 0) {
                continue;
            }

            $clientName = $application->client?->name ?: 'Client';
            $appName = $application->application_name ?: 'Application';
            $appCode = $application->application_id ?: $application->id;
            $amount = number_format($outstanding, 2, '.', '');

            $lines[] = sprintf('%s - %s (%s) - %s', $clientName, $appName, $appCode, $amount);
        }

        return $lines;
    }

    public function resolveApplicationOutstanding(int $subscriberId, Applications $application): float
    {
        $arOutstanding = $this->applicationArOutstanding($subscriberId, $application);
        if ($arOutstanding > 0) {
            return $arOutstanding;
        }

        return $this->applicationClientAccountOutstanding($subscriberId, $application);
    }

    public function invoiceIsFullyPaid(int $subscriberId, string $invoiceNo, string $type = 'ar'): bool
    {
        $invoice = Internal_Invoices::where('subscriber_id', $subscriberId)
            ->where('invoice_no', $invoiceNo)
            ->where('type', $type)
            ->first();

        if (!$invoice) {
            return false;
        }

        $paid = (float) PaymentARs::where('subscriber_id', $subscriberId)
            ->where('type', $type)
            ->where('invoice_no', $invoiceNo)
            ->sum('paid_amount');

        return $paid >= (float) $invoice->total;
    }

    private function applicationArOutstanding(int $subscriberId, Applications $application): float
    {
        $invoiceNos = PaymentARs::query()
            ->where('subscriber_id', $subscriberId)
            ->where('application_id', $application->id)
            ->whereRaw('LOWER(type) = ?', ['ar'])
            ->whereNotNull('invoice_no')
            ->distinct()
            ->pluck('invoice_no')
            ->filter();

        if ($invoiceNos->isEmpty()) {
            $rowOutstanding = (float) PaymentARs::query()
                ->where('subscriber_id', $subscriberId)
                ->where('application_id', $application->id)
                ->whereRaw('LOWER(type) = ?', ['ar'])
                ->selectRaw('COALESCE(MAX(amount) - SUM(paid_amount), 0) as outstanding')
                ->value('outstanding');

            return round(max(0, $rowOutstanding), 2);
        }

        $outstanding = 0.0;
        foreach ($invoiceNos as $invoiceNo) {
            $total = (float) Internal_Invoices::where('subscriber_id', $subscriberId)
                ->where('invoice_no', $invoiceNo)
                ->where('type', 'ar')
                ->value('total');
            $paid = (float) PaymentARs::where('subscriber_id', $subscriberId)
                ->where('type', 'ar')
                ->where('invoice_no', $invoiceNo)
                ->sum('paid_amount');
            $outstanding += max(0, $total - $paid);
        }

        return round($outstanding, 2);
    }

    private function applicationClientAccountOutstanding(int $subscriberId, Applications $application): float
    {
        $rows = ClientAccount::query()
            ->where('subscriber_id', $subscriberId)
            ->where('client_id', $application->client_id)
            ->where('application_id', $application->id)
            ->get(['trans_type', 'amount']);

        if ($rows->isEmpty()) {
            return 0.0;
        }

        $credit = 0.0;
        $debit = 0.0;
        foreach ($rows as $row) {
            if (strcasecmp((string) $row->trans_type, 'Credit') === 0) {
                $credit += (float) $row->amount;
            } else {
                $debit += (float) $row->amount;
            }
        }

        $balance = round($credit - $debit, 2);

        return $balance < 0 ? abs($balance) : 0.0;
    }

    private function applicationLink(Applications $application): string
    {
        try {
            return route('view_application', $application->id);
        } catch (\Throwable $e) {
            return route('applications');
        }
    }

    private function resolveTimezone(User $user): string
    {
        $timezone = $this->normalizeTimezone($user->timezone ?? null);
        if ($timezone !== null) {
            return $timezone;
        }

        return $this->normalizeTimezone(config('app.timezone', 'UTC')) ?? 'UTC';
    }

    private function normalizeTimezone($timezone): ?string
    {
        $timezone = is_string($timezone) ? trim($timezone) : '';

        if ($timezone === '') {
            return null;
        }

        try {
            new \DateTimeZone($timezone);

            return $timezone;
        } catch (\Exception $e) {
        }

        if (preg_match('/\((?:GMT|UTC)\s*([+\-]\d{1,2}:?\d{2})\)/i', $timezone, $offsetMatch)) {
            $normalized = $this->normalizeOffset($offsetMatch[1]);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        if (preg_match('/(Asia\/[A-Za-z_]+|Europe\/[A-Za-z_]+|America\/[A-Za-z_]+|Africa\/[A-Za-z_]+|Australia\/[A-Za-z_]+|Pacific\/[A-Za-z_]+|Atlantic\/[A-Za-z_]+|Indian\/[A-Za-z_]+)/', $timezone, $identifierMatch)) {
            try {
                new \DateTimeZone($identifierMatch[1]);

                return $identifierMatch[1];
            } catch (\Exception $e) {
            }
        }

        if (preg_match('/^(?:GMT|UTC)?\s*([+\-]\d{1,2}:?\d{2})$/i', $timezone, $offsetOnlyMatch)) {
            return $this->normalizeOffset($offsetOnlyMatch[1]);
        }

        return null;
    }

    private function normalizeOffset(string $offset): ?string
    {
        $offset = str_replace(' ', '', $offset);

        if (!preg_match('/^([+\-])(\d{1,2}):?(\d{2})$/', $offset, $match)) {
            return null;
        }

        $hours = (int) $match[2];
        $minutes = (int) $match[3];
        if ($hours > 14 || $minutes > 59) {
            return null;
        }

        return sprintf('%s%02d:%02d', $match[1], $hours, $minutes);
    }
}
