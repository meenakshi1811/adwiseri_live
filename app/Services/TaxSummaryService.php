<?php

namespace App\Services;

use App\Models\Internal_Invoices;
use App\Models\PaymentARs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Collected tax on AR client invoices, attributed to payment dates.
 * Skips subscription-package and export-service-tax-exempt invoices.
 */
class TaxSummaryService
{
    public const DURATIONS = [
        'today' => 'Today',
        'last_week' => 'Last Week',
        'last_month' => 'Last Month',
        'last_quarter' => 'Last Quarter',
        'last_year' => 'Last Year',
        'since_inception' => 'Since Inception',
    ];

    /**
     * @return array{
     *     total_collected_tax: float,
     *     by_timeline: array<int, array{duration: string, tax_amount: string}>,
     *     by_year: array<int, array{year: string, tax_amount: string}>
     * }
     */
    public function summary(User $subscriber): array
    {
        $invoiceMap = $this->taxableInvoiceMap($subscriber);
        $payments = $this->arPaymentsWithTax($subscriber, $invoiceMap);

        $byTimeline = [];
        foreach (self::DURATIONS as $key => $label) {
            [$from, $to] = $this->durationRange($key);
            $amount = $this->sumPaymentsInRange($payments, $from, $to);

            $byTimeline[] = [
                'duration' => $label,
                'tax_amount' => $this->formatAmount($amount),
            ];
        }

        $yearTotals = [];
        foreach ($payments as $payment) {
            $year = (string) $payment['year'];
            $yearTotals[$year] = ($yearTotals[$year] ?? 0.0) + $payment['tax_amount'];
        }

        krsort($yearTotals, SORT_NUMERIC);

        $byYear = [];
        foreach ($yearTotals as $year => $amount) {
            $byYear[] = [
                'year' => $year,
                'tax_amount' => $this->formatAmount($amount),
            ];
        }

        $sinceInception = $this->sumPaymentsInRange($payments, null, null);

        return [
            'total_collected_tax' => round($sinceInception, 2),
            'total_collected_tax_formatted' => $this->formatAmount($sinceInception),
            'by_timeline' => $byTimeline,
            'by_year' => $byYear,
        ];
    }

    public function totalCollectedTax(User $subscriber): float
    {
        $invoiceMap = $this->taxableInvoiceMap($subscriber);
        $payments = $this->arPaymentsWithTax($subscriber, $invoiceMap);

        return round($this->sumPaymentsInRange($payments, null, null), 2);
    }

    /**
     * @return array<string, Internal_Invoices>
     */
    private function taxableInvoiceMap(User $subscriber): array
    {
        $invoices = Internal_Invoices::query()
            ->where('subscriber_id', $subscriber->id)
            ->whereRaw('LOWER(type) = ?', ['ar'])
            ->whereNotIn(DB::raw('LOWER(status)'), ['cancelled', 'withdrawn'])
            ->get(['invoice_no', 'amount', 'discount', 'tax', 'total', 'detail', 'export_service_tax_exempt']);

        $map = [];

        foreach ($invoices as $invoice) {
            if ($invoice->isSubscriptionPackageInvoice() || $invoice->isExportServiceTaxExempt()) {
                continue;
            }

            if ($this->invoiceTaxAmount($invoice) <= 0) {
                continue;
            }

            $invoiceNo = trim((string) $invoice->invoice_no);
            if ($invoiceNo !== '') {
                $map[$invoiceNo] = $invoice;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, Internal_Invoices>  $invoiceMap
     * @return array<int, array{paid_at: Carbon, year: int, tax_amount: float}>
     */
    private function arPaymentsWithTax(User $subscriber, array $invoiceMap): array
    {
        if ($invoiceMap === []) {
            return [];
        }

        $rows = PaymentARs::query()
            ->where('subscriber_id', $subscriber->id)
            ->whereRaw('LOWER(type) = ?', ['ar'])
            ->where('paid_amount', '>', 0)
            ->whereNotNull('invoice_no')
            ->where('invoice_no', '!=', '')
            ->get(['invoice_no', 'paid_amount', 'payment_date', 'created_at']);

        $payments = [];

        foreach ($rows as $row) {
            $invoiceNo = trim((string) $row->invoice_no);
            $invoice = $invoiceMap[$invoiceNo] ?? null;

            if (!$invoice) {
                continue;
            }

            $taxAmount = $this->taxFromPayment($row, $invoice);
            if ($taxAmount <= 0) {
                continue;
            }

            $paidAt = $this->resolvePaymentDate($row);

            $payments[] = [
                'paid_at' => $paidAt,
                'year' => (int) $paidAt->format('Y'),
                'tax_amount' => $taxAmount,
            ];
        }

        return $payments;
    }

    private function taxFromPayment(PaymentARs $payment, Internal_Invoices $invoice): float
    {
        $invoiceTotal = round((float) $invoice->total, 2);
        if ($invoiceTotal <= 0) {
            return 0.0;
        }

        $paid = round((float) $payment->paid_amount, 2);
        if ($paid <= 0) {
            return 0.0;
        }

        $taxAmount = $this->invoiceTaxAmount($invoice);

        return $taxAmount * min(1.0, $paid / $invoiceTotal);
    }

    private function invoiceTaxAmount(Internal_Invoices $invoice): float
    {
        $amount = (float) ($invoice->amount ?? 0);
        $discount = (float) ($invoice->discount ?? 0);
        $taxPercent = (float) ($invoice->tax ?? 0);

        if ($taxPercent <= 0 || $amount <= 0) {
            return 0.0;
        }

        $discountedSubtotal = $amount - ($amount * $discount / 100);

        return round($discountedSubtotal * ($taxPercent / 100), 2);
    }

    private function resolvePaymentDate(PaymentARs $payment): Carbon
    {
        $raw = $payment->payment_date ?: $payment->created_at;

        return Carbon::parse($raw);
    }

    /**
     * @param  array<int, array{paid_at: Carbon, year: int, tax_amount: float}>  $payments
     */
    private function sumPaymentsInRange(array $payments, ?Carbon $from, ?Carbon $to): float
    {
        $total = 0.0;

        foreach ($payments as $payment) {
            $paidAt = $payment['paid_at'];

            if ($from && $paidAt->lt($from)) {
                continue;
            }

            if ($to && $paidAt->gt($to)) {
                continue;
            }

            $total += $payment['tax_amount'];
        }

        return round($total, 2);
    }

    /**
     * Mirrors dashboard duration windows in DashboardPreferenceService.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function durationRange(string $duration): array
    {
        if ($duration === 'since_inception') {
            return [null, null];
        }

        $now = Carbon::now();

        switch ($duration) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];

            case 'last_week':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];

            case 'last_month':
                return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()];

            case 'last_quarter':
                return [
                    $now->copy()->subMonths(3)->startOfMonth()->startOfDay(),
                    $now->copy()->endOfMonth()->endOfDay(),
                ];

            case 'last_year':
                return [
                    $now->copy()->subYear()->startOfYear()->startOfDay(),
                    $now->copy()->subYear()->endOfYear()->endOfDay(),
                ];
        }

        return [null, null];
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
