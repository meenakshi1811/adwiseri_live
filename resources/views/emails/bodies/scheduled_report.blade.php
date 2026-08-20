<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;border:none;border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;">
    <tr>
        <td style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                <strong>Hello {{ $data['name'] ?? $data['recipient_name'] }},</strong>
            </p>
            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                Your {{ $data['consultancy_name'] ?? 'consultancy' }}'s Report(s) are ready.
            </p>
            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                Please find the attached file or use the secure download link below.
            </p>

            @if(!empty($data['modules']) && is_array($data['modules']))
                <p style="margin:0 0 8px 0;font-size:14px;line-height:1.7;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                    <strong>Report Modules Included:</strong>
                </p>
                <ul style="margin:0 0 14px 18px;padding:0;font-size:14px;line-height:1.7;color:#1f2937;">
                    @foreach($data['modules'] as $module)
                        <li style="margin:0 0 4px 0;">{{ ucwords(str_replace('_', ' ', $module)) }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($data['download_link']))
                @include('emails.partials.report_download', [
                    'downloadLink' => $data['download_link'],
                    'fileName' => $data['file_name'] ?? null,
                ])
            @else
                <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                    The report PDF is attached to this email.
                </p>
            @endif

            @include('emails.partials.signature')
        </td>
    </tr>
</table>
