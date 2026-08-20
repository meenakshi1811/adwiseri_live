@php
    $snapshotService = app(\App\Services\InvoiceSnapshotService::class);
    $fromMailData = isset($data);

    if ($fromMailData) {
        $billName = $data->name ?? '-';
        $billEmail = trim((string) ($data->to_email ?? ''));
        $billAddress = trim((string) ($data->to_address ?? ''));
        $billCity = trim((string) ($data->to_city ?? ''));
        $billState = trim((string) ($data->to_state ?? ''));
        $billCountry = trim((string) ($data->to_country ?? ''));
        $billPincode = trim((string) ($data->to_pincode ?? ''));
        $invoiceNo = $data->invoice_no ?? '-';
        $invoiceDate = !empty($data->invoice_date) ? date('d-m-Y', strtotime($data->invoice_date)) : '-';
        $dueDate = !empty($data->due_date) ? date('d-m-Y', strtotime($data->due_date)) : '-';
        $statusRaw = (string) ($data->status ?? '-');
        $amount = (float) ($data->amount ?? 0);
        $discountPercent = (float) ($data->discount ?? 0);
        $taxPercent = (float) ($data->tax ?? 0);
        $detailForPackageCheck = trim((string) ($data->detail ?? ''));
        if ($detailForPackageCheck === '' && !empty($data->items)) {
            $firstItem = collect($data->items)->first();
            $detailForPackageCheck = is_array($firstItem)
                ? (string) ($firstItem['detail'] ?? '')
                : (string) (is_object($firstItem) ? ($firstItem->detail ?? '') : '');
        }
        if (\App\Models\Internal_Invoices::isSubscriptionPackageDetail($detailForPackageCheck)
            || \App\Models\Internal_Invoices::itemsLookLikeSubscriptionPackage($data->items ?? [])) {
            $discountPercent = 0.0;
            $taxPercent = 0.0;
        }
        $exportTaxExempt = (bool) ($data->export_service_tax_exempt ?? false);
        $total = (float) ($data->total ?? 0);
        $currencyLabel = trim((string) ($data->currency ?? 'Rs.'));
        $taxLabel = \App\Models\Invoice_settings::resolveTaxLabel($data->tax_label ?? null);
        $paymentLink = trim((string) ($data->payment_link ?? ''));
        $qrPath = $data->payment_qr_path ?? null;
        $qrUrl = $data->payment_qr_url ?? null;
        $invoiceNote = trim((string) ($data->invoice_note ?? ''));
        $lineItems = collect($data->items ?? [])->filter(function ($item) {
            return !empty($item['detail'] ?? (is_object($item) ? $item->detail : null));
        })->map(function ($item) {
            return [
                'detail' => trim((string) ($item['detail'] ?? (is_object($item) ? $item->detail : ''))),
                'amount' => (float) ($item['amount'] ?? (is_object($item) ? $item->amount : 0)),
            ];
        });

        if ($lineItems->isEmpty()) {
            $lineItems = collect([[
                'detail' => trim((string) ($data->detail ?? 'Professional Services')),
                'amount' => $amount,
            ]]);
        }
    } else {
        $billName = $invoice->to_name ?? '-';
        $billEmail = trim((string) ($invoice->to_email ?? ''));
        $billAddress = trim((string) ($invoice->to_address ?? ''));
        $billCity = trim((string) ($invoice->to_city ?? ''));
        $billState = trim((string) ($invoice->to_state ?? ''));
        $billCountry = trim((string) ($invoice->to_country ?? ''));
        $billPincode = trim((string) ($invoice->to_pincode ?? ''));
        $invoiceNo = $invoice->invoice_no ?? '-';
        $invoiceDate = !empty($invoice->created_at) ? date('d-m-Y', strtotime($invoice->created_at)) : '-';
        $dueDate = !empty($invoice->due_date) ? date('d-m-Y', strtotime($invoice->due_date)) : '-';
        $statusRaw = (string) ($invoice->status ?? '-');
        $amount = (float) $invoice->amount;
        $discountPercent = (float) $invoice->discount;
        $taxPercent = (float) $invoice->tax;
        if ($invoice->isSubscriptionPackageInvoice()) {
            $discountPercent = 0.0;
            $taxPercent = 0.0;
        }
        $exportTaxExempt = $invoice->isExportServiceTaxExempt();
        $total = (float) $invoice->total;
        $currencyLabel = trim((string) (($user->currency ?? null) ?: ($u->currency ?? 'Rs.')));
        $taxLabel = $snapshotService->resolvedTaxLabel($invoice, $invoiceSetting ?? null);
        $paymentLink = $snapshotService->resolvedPaymentLink($invoice, $invoiceSetting ?? null);
        $qrFilename = $snapshotService->resolvedPaymentQrFilename($invoice, $invoiceSetting ?? null);
        $qrSubscriberId = $qrSubscriberId ?? ($u->id ?? ($invoice->subscriber_id ?? $invoice->user_id ?? 1));
        $qrPath = null;
        $qrUrl = null;

        if ($qrFilename !== '') {
            $qrDiskPath = public_path('web_assets/users/user' . $qrSubscriberId . '/' . $qrFilename);
            if (file_exists($qrDiskPath)) {
                $qrPath = $qrDiskPath;
                $qrUrl = asset('web_assets/users/user' . $qrSubscriberId . '/' . $qrFilename);
            }
        }

        $invoiceNote = $snapshotService->resolvedInvoiceNote($invoice, $invoiceSetting ?? null);
        $lineItems = $invoice->lineItems()->map(function ($item) {
            return [
                'detail' => trim((string) $item->detail),
                'amount' => (float) $item->amount,
            ];
        });
    }

    $statusLabel = $statusRaw === 'PartiallyPaid' ? 'Partially Paid' : ($statusRaw === 'UnPaid' ? 'Unpaid' : $statusRaw);
    $showTaxLine = !$exportTaxExempt && $taxPercent > 0;
    $discountAmount = $amount * ($discountPercent / 100);
    $taxable = $amount - $discountAmount;
    $taxAmount = $taxable * ($taxPercent / 100);
    $showSrNo = $lineItems->count() > 1;
    $amountColumnWidth = 28;
    $leadingColumnWidth = 100 - $amountColumnWidth;
    $srNoColumnWidth = $showSrNo ? 10 : 0;
    $descriptionColumnWidth = $showSrNo ? ($leadingColumnWidth - $srNoColumnWidth) : $leadingColumnWidth;
    $hasPaymentLink = $paymentLink !== '' && filter_var($paymentLink, FILTER_VALIDATE_URL);
    $hasQr = !empty($qrPath) || !empty($qrUrl);
    $noteParagraphs = array_values(array_filter(
        preg_split('/\R/', $invoiceNote) ?: [],
        static fn ($line) => trim((string) $line) !== ''
    ));
    $forPdf = $forPdf ?? $fromMailData;
    $qrImageSrc = $forPdf ? ($qrPath ?? null) : ($qrUrl ?? null);

    $billLocationParts = array_values(array_filter([
        trim($billCity . ($billState !== '' ? ', ' . $billState : '')),
        trim($billCountry . ($billPincode !== '' ? ' - ' . $billPincode : '')),
    ]));

    $issuerName = $fromMailData
        ? ($data->company_name ?? $data->subscriber_name ?? $data->from_name ?? 'Adwiseri')
        : ($invoice->name ?? 'Adwiseri');
    $issuerName = preg_replace('/^Sent on behalf of\s+/i', '', trim((string) $issuerName)) ?: 'Adwiseri';
    $isAdwiseriInvoice = $isAdwiseriInvoice ?? \App\Support\BrandedMail::isPlatformBrand($issuerName);
@endphp

<table class="invoice-doc-grid" style="width:100%; margin-bottom:16px;">
    <tr>
        <td style="width:50%; padding-right:8px; vertical-align:top;">
            <div class="invoice-doc-section-title">Bill To</div>
            <div class="invoice-doc-box">
                <strong>{{ $billName }}</strong><br>
                @if($billAddress !== '')
                    {{ $billAddress }}<br>
                @endif
                @foreach($billLocationParts as $locationLine)
                    {{ $locationLine }}<br>
                @endforeach
                @if($billEmail !== '')
                    {{ $billEmail }}
                @endif
            </div>
        </td>
        <td style="width:50%; padding-left:8px; vertical-align:top;">
            <div class="invoice-doc-section-title">Invoice Details</div>
            <div class="invoice-doc-box">
                <strong>Invoice No:</strong> {{ $invoiceNo }}<br>
                <strong>Invoice Date:</strong> {{ $invoiceDate }}<br>
                @if($statusRaw !== 'Paid')
                    <strong>Due Date:</strong> {{ $dueDate }}<br>
                @endif
                <strong>Status:</strong> {{ $statusLabel }}
            </div>
        </td>
    </tr>
</table>

<table class="invoice-doc-items">
    <thead>
        <tr>
            @if($showSrNo)
                <th class="invoice-doc-col-sr" style="width:{{ $srNoColumnWidth }}%;">Sr. No.</th>
            @endif
            <th class="invoice-doc-col-description" style="width:{{ $descriptionColumnWidth }}%;">Description</th>
            <th class="invoice-doc-col-amount" style="width:{{ $amountColumnWidth }}%;">Amount ({{ $currencyLabel }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lineItems as $index => $lineItem)
            <tr>
                @if($showSrNo)
                    <td class="invoice-doc-col-sr">{{ $index + 1 }}</td>
                @endif
                <td class="invoice-doc-col-description">{{ $lineItem['detail'] }}</td>
                <td class="invoice-doc-col-amount">{{ number_format((float) $lineItem['amount'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="invoice-doc-totals">
    <tbody>
        <tr>
            <td class="invoice-doc-totals-label" style="width:{{ $leadingColumnWidth }}%;">Subtotal</td>
            <td class="invoice-doc-totals-amount" style="width:{{ $amountColumnWidth }}%;">{{ number_format($amount, 2) }}</td>
        </tr>
        @if($discountPercent > 0)
            <tr>
                <td class="invoice-doc-totals-label">Discount ({{ number_format($discountPercent, 2) }}%)</td>
                <td class="invoice-doc-totals-amount">- {{ number_format($discountAmount, 2) }}</td>
            </tr>
        @endif
        @if($showTaxLine)
            <tr>
                <td class="invoice-doc-totals-label">{{ $taxLabel }} ({{ number_format($taxPercent, 2) }}%)</td>
                <td class="invoice-doc-totals-amount">{{ number_format($taxAmount, 2) }}</td>
            </tr>
        @endif
        <tr class="invoice-doc-grand-total">
            <td class="invoice-doc-totals-label">Total</td>
            <td class="invoice-doc-totals-amount">{{ number_format($total, 2) }}</td>
        </tr>
    </tbody>
</table>

@if($hasPaymentLink || $hasQr)
    <div class="invoice-doc-payment" style="margin-top:16px;">
        @if($hasPaymentLink)
            <p style="margin:0 0 10px 0; text-align:center;">
                <strong>Payment Link:</strong>
                @if($forPdf)
                    {{ $paymentLink }}
                @else
                    <a href="{{ $paymentLink }}" target="_blank" rel="noopener noreferrer">{{ $paymentLink }}</a>
                @endif
            </p>
        @endif
        @if($hasQr && !empty($qrImageSrc))
            <div style="text-align:center;">
                <div style="font-size:12px; font-weight:600; margin-bottom:6px;">Payment QR Code</div>
                <img src="{{ $qrImageSrc }}" alt="Payment QR code"
                    style="width:100px; height:100px; object-fit:contain; border:1px solid #d1d5db; border-radius:6px; padding:4px; background:#fff;">
                <div style="font-size:11px; color:#6b7280; margin-top:4px;">Scan to pay via UPI</div>
            </div>
        @endif
    </div>
@endif

@if(count($noteParagraphs) > 0)
    <div class="invoice-doc-note" style="margin-top:18px; font-size:12px; line-height:1.6;">
        <div style="font-weight:bold; margin-bottom:6px; text-align:left;">Note :</div>
        @foreach($noteParagraphs as $paragraph)
            <div style="margin-bottom:6px;">{{ $paragraph }}</div>
        @endforeach
    </div>
@endif

@if(!($forPdf ?? false) && ($showFooterThanks ?? true))
    @include('partials.invoice_document_footer', ['isAdwiseriInvoice' => $isAdwiseriInvoice])
@endif
