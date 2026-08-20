<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;line-height:1.6;">
    <tr>
        <td style="padding:10px 12px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;width:38%;vertical-align:top;">Offer Type</td>
        <td style="padding:10px 12px;border:1px solid #e5e7eb;vertical-align:top;">{{ $offer_label ?? '' }}</td>
    </tr>
    <tr>
        <td style="padding:10px 12px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;vertical-align:top;">Description</td>
        <td style="padding:10px 12px;border:1px solid #e5e7eb;vertical-align:top;">{{ $offer_description ?? '' }}</td>
    </tr>
    @if (!empty($credit_label) && !empty($credit_value))
    <tr>
        <td style="padding:10px 12px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;vertical-align:top;">{{ $credit_label }}</td>
        <td style="padding:10px 12px;border:1px solid #e5e7eb;vertical-align:top;">
            @if (!empty($credit_is_html))
                {!! $credit_value !!}
            @else
                {{ $credit_value }}
            @endif
        </td>
    </tr>
    @endif
</table>
