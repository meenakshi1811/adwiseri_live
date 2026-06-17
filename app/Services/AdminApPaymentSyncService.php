<?php

namespace App\Services;

use App\Models\Internal_Invoices;
use App\Models\PaymentARs;

class AdminApPaymentSyncService
{
    public function syncPaidInvoicesForSubscriber(int $subscriberId): void
    {
        $paidApInvoices = Internal_Invoices::where('subscriber_id', $subscriberId)
            ->whereRaw('LOWER(type) = ?', ['ap'])
            ->whereRaw('LOWER(status) = ?', ['paid'])
            ->where('total', '>', 0)
            ->get();

        foreach ($paidApInvoices as $invoice) {
            if (empty($invoice->invoice_no)) {
                continue;
            }

            $paymentExists = PaymentARs::where('subscriber_id', $subscriberId)
                ->whereRaw('LOWER(type) = ?', ['ap'])
                ->where('invoice_no', $invoice->invoice_no)
                ->exists();

            if ($paymentExists) {
                continue;
            }

            PaymentARs::create([
                'subscriber_id' => $subscriberId,
                'invoice_no' => $invoice->invoice_no,
                'service_provider' => $invoice->name ?: ($invoice->to_name ?: 'adwiseri.com'),
                'service_taken' => $invoice->detail ?: 'Subscription Fees',
                'amount' => (float) $invoice->total,
                'paid_amount' => (float) $invoice->total,
                'payment_mode' => $invoice->payment_mode ?: 'Online',
                'payment_date' => $invoice->updated_at ?: $invoice->created_at ?: now(),
                'type' => 'ap',
            ]);
        }
    }
}
