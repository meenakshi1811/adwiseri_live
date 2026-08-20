@php
    $attachmentName = 'Invoice-' . trim(preg_replace('/[^a-zA-Z0-9._-]+/', '-', (string) ($data->invoice_no ?? 'document')), '-') . '.pdf';

    $summaryRows = [
        ['Invoice Number', $data->invoice_no ?? '-'],
        ['Invoice Date', !empty($data->invoice_date) ? date('d-m-Y', strtotime($data->invoice_date)) : '-'],
        ['Status', $data->status ?? '-'],
        ['Total', trim(($data->currency ?? 'Rs.') . ' ' . (isset($data->total) ? number_format((float) $data->total, 2) : '0.00'))],
    ];

    if (($data->status ?? '') !== 'Paid') {
        $summaryRows[] = ['Due Date', !empty($data->due_date) ? date('d-m-Y', strtotime($data->due_date)) : '-'];
    }
@endphp

<p style="margin:0 0 12px 0;word-wrap:break-word;overflow-wrap:break-word;max-width:100%;">Hello <strong>{{ $data->name }}</strong>,</p>
<p style="margin:0 0 16px 0;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">{{ $data->message }}</p>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="invoice-summary-table" style="margin:18px 0;width:100%;max-width:100%;table-layout:fixed;border:1px solid #e5e7eb;border-radius:8px;border-collapse:separate;overflow:hidden;box-sizing:border-box;">
    @foreach($summaryRows as $index => $row)
        <tr>
            <td class="invoice-summary-label" style="padding:12px 14px;{{ $index < count($summaryRows) - 1 ? 'border-bottom:1px solid #e5e7eb;' : '' }}width:38%;vertical-align:top;color:#6b7280;font-size:13px;line-height:1.4;word-wrap:break-word;overflow-wrap:break-word;box-sizing:border-box;">{{ $row[0] }}</td>
            <td class="invoice-summary-value" style="padding:12px 14px;{{ $index < count($summaryRows) - 1 ? 'border-bottom:1px solid #e5e7eb;' : '' }}width:62%;vertical-align:top;font-weight:600;font-size:14px;line-height:1.5;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;box-sizing:border-box;">{{ $row[1] }}</td>
        </tr>
    @endforeach
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin:0 0 16px 0;width:100%;max-width:100%;table-layout:fixed;box-sizing:border-box;">
    <tr>
        <td style="padding:14px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;box-sizing:border-box;max-width:100%;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="width:100%;max-width:100%;table-layout:fixed;">
                <tr>
                    <td width="52" valign="middle" style="width:52px;vertical-align:middle;padding:0;">
                        <div style="width:36px;height:44px;background:#dc2626;border-radius:4px;text-align:center;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:700;line-height:44px;">PDF</div>
                    </td>
                    <td width="24" valign="middle" style="width:24px;vertical-align:middle;padding:0;text-align:center;font-size:18px;line-height:1;color:#374151;">&#128206;</td>
                    <td valign="middle" style="vertical-align:middle;padding:0;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                        <div style="font-weight:600;font-size:14px;line-height:1.4;color:#1f2937;word-break:break-word;">{{ $attachmentName }}</div>
                        <div style="font-size:12px;line-height:1.5;color:#6b7280;margin-top:2px;">Attached to this email</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@if(!empty($data->payment_link) && filter_var($data->payment_link, FILTER_VALIDATE_URL))
    <p style="margin:0 0 16px 0;word-wrap:break-word;overflow-wrap:break-word;max-width:100%;">
        <strong>Payment Link:</strong>
        <a href="{{ $data->payment_link }}" style="color:#695EEE;text-decoration:underline;word-break:break-all;">Pay Now</a>
    </p>
@endif

@if(!empty($data->payment_qr_url))
    <div style="margin:0 0 16px 0;text-align:center;max-width:100%;">
        <p style="margin:0 0 8px 0;word-wrap:break-word;overflow-wrap:break-word;"><strong>Payment QR Code</strong></p>
        <img src="{{ $data->payment_qr_url }}" alt="Payment QR code" width="100" height="100" style="display:block;margin:0 auto;width:100px;max-width:100%;height:auto;object-fit:contain;border:1px solid #e5e7eb;border-radius:6px;padding:4px;">
        <p style="margin:8px 0 0 0;font-size:12px;color:#6b7280;word-wrap:break-word;overflow-wrap:break-word;">Scan to pay via UPI</p>
    </div>
@endif

@include('emails.partials.signature')
