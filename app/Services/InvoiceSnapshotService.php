<?php

namespace App\Services;

use App\Models\Internal_Invoices;
use App\Models\Invoice_settings;

class InvoiceSnapshotService
{
    public function applySettingsSnapshot(Internal_Invoices $invoice, ?Invoice_settings $settings): void
    {
        if ($settings === null) {
            return;
        }

        $invoice->tax_label = Invoice_settings::resolveTaxLabel($settings->tax_label);
        $invoice->payment_link = $settings->payment_link;
        $invoice->payment_qr_code = $settings->payment_qr_code;
        $invoice->invoice_note = $settings->invoice_note;
    }

    public function resolvedTaxLabel(Internal_Invoices $invoice, ?Invoice_settings $fallbackSettings = null): string
    {
        if (!empty($invoice->tax_label)) {
            return Invoice_settings::resolveTaxLabel($invoice->tax_label);
        }

        return Invoice_settings::resolveTaxLabel($fallbackSettings?->tax_label);
    }

    public function resolvedPaymentLink(Internal_Invoices $invoice, ?Invoice_settings $fallbackSettings = null): string
    {
        $link = trim((string) ($invoice->payment_link ?? $fallbackSettings?->payment_link ?? ''));

        return $link;
    }

    public function resolvedPaymentQrFilename(Internal_Invoices $invoice, ?Invoice_settings $fallbackSettings = null): string
    {
        return trim((string) ($invoice->payment_qr_code ?? $fallbackSettings?->payment_qr_code ?? ''));
    }

    public function resolvedInvoiceNote(Internal_Invoices $invoice, ?Invoice_settings $fallbackSettings = null): string
    {
        return trim((string) ($invoice->invoice_note ?? ''));
    }
}
