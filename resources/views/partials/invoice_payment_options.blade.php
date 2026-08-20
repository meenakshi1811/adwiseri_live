@php
    $snapshotService = app(\App\Services\InvoiceSnapshotService::class);
    $paymentLink = $snapshotService->resolvedPaymentLink($invoice, $invoiceSetting ?? null);
    $qrFilename = $snapshotService->resolvedPaymentQrFilename($invoice, $invoiceSetting ?? null);
    $qrSubscriberId = $qrSubscriberId ?? ($u->id ?? ($invoice->subscriber_id ?? $invoice->user_id ?? 1));
    $qrUrl = null;

    if ($qrFilename !== '') {
        $qrPath = public_path('web_assets/users/user' . $qrSubscriberId . '/' . $qrFilename);
        if (file_exists($qrPath)) {
            $qrUrl = asset('web_assets/users/user' . $qrSubscriberId . '/' . $qrFilename);
        }
    }

    $hasPaymentLink = $paymentLink !== '' && filter_var($paymentLink, FILTER_VALIDATE_URL);
@endphp
@if($hasPaymentLink || $qrUrl)
    <div class="invoice-payment-options" style="margin-top: 16px; display: flex; align-items: flex-start; gap: 24px; flex-wrap: wrap;">
        @if($hasPaymentLink)
            <p class="payment-link-line" style="margin: 0;">
                <strong>Payment Link:</strong>
                <a class="payment-link-anchor" href="{{ $paymentLink }}" target="_blank" rel="noopener noreferrer">{{ $paymentLink }}</a>
            </p>
        @endif
        @if($qrUrl)
            <div style="text-align: center;">
                <div style="font-size: 12px; font-weight: 600; margin-bottom: 6px;">Payment QR Code</div>
                <img src="{{ $qrUrl }}" alt="Payment QR code" title="Scan to pay via UPI"
                    style="width: 100px; height: 100px; object-fit: contain; border: 1px solid #d1d5db; border-radius: 6px; padding: 4px; background: #fff;">
                <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">Scan to pay (UPI)</div>
            </div>
        @endif
    </div>
@endif
@once
<style>
    .column-client .invoice-payment-options a.payment-link-anchor,
    .invoice-payment-options a.payment-link-anchor {
        color: #695EEE !important;
        background: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        min-height: auto !important;
        font-weight: 400 !important;
        font-size: inherit;
        word-break: break-all;
        display: inline;
        text-decoration: underline !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        white-space: normal;
    }

    .column-client .invoice-payment-options a.payment-link-anchor:hover,
    .column-client .invoice-payment-options a.payment-link-anchor:focus,
    .invoice-payment-options a.payment-link-anchor:hover,
    .invoice-payment-options a.payment-link-anchor:focus {
        color: #0052cc !important;
        background: none !important;
        border: none !important;
        text-decoration: underline !important;
    }
</style>
@endonce
