<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;border:none;border-collapse:collapse;table-layout:fixed;">
    <tr>
        <td width="100%" style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;">
                <strong>Hello {{ $data['recipient_name'] ?? $data['name'] ?? 'Team Member' }},</strong>
            </p>
            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;">
                {{ $data['shared_by'] ?? 'A team member' }} from {{ $data['consultancy_name'] ?? 'your consultancy' }} has shared a report/chart with you.
            </p>
            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;">
                <strong>Attachment:</strong> {{ $fileName }}
            </p>
            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;">
                Please find the PDF attached to this email.
            </p>
            @include('emails.partials.signature')
        </td>
    </tr>
</table>
