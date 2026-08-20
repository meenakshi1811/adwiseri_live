<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;margin:16px 0 0 0;border:none;border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;">
    <tr>
        <td style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
            <p style="margin:0 0 12px 0;font-size:14px;line-height:1.6;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                The report PDF is attached to this email. You can also download it using the secure link below. This link expires in 7 days.
            </p>
            @if(!empty($fileName))
                <p style="margin:0 0 16px 0;font-size:14px;line-height:1.6;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                    <strong>File:</strong> {{ $fileName }}
                </p>
            @endif
        </td>
    </tr>
    <tr>
        <td align="center" style="padding:0 0 16px 0;border:none;width:100%;max-width:100%;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;border:none;border-collapse:collapse;">
                <tr>
                    <td align="center" bgcolor="#695EEE" style="border-radius:6px;background-color:#695EEE;border:none;width:100%;max-width:100%;mso-padding-alt:12px 16px;">
                        <!--[if mso]>
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $downloadLink }}" style="height:44px;v-text-anchor:middle;width:100%;" arcsize="12%" strokecolor="#695EEE" fillcolor="#695EEE">
                            <w:anchorlock/>
                            <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">Download Report PDF</center>
                        </v:roundrect>
                        <![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $downloadLink }}"
                           class="email-cta"
                           target="_blank"
                           rel="noopener noreferrer"
                           style="display:block;width:100%;max-width:100%;box-sizing:border-box;padding:12px 16px;font-size:14px;font-weight:700;color:#ffffff !important;text-decoration:none;border-radius:6px;background-color:#695EEE;line-height:1.4;text-align:center;mso-hide:all;">
                            Download Report PDF
                        </a>
                        <!--<![endif]-->
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
            <p style="margin:0 0 8px 0;font-size:12px;line-height:1.6;color:#6b7280;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;">
                If the button does not work, copy and paste this link into your browser:
            </p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;border:none;border-collapse:collapse;">
                <tr>
                    <td class="email-url-box" style="padding:10px 12px;background-color:#f3f4f6;border-radius:6px;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-all;max-width:100%;font-size:11px;line-height:1.55;color:#695EEE;">
                        <a href="{{ $downloadLink }}" class="email-break-link" style="color:#695EEE;text-decoration:underline;word-wrap:break-word;overflow-wrap:break-word;word-break:break-all;white-space:normal;display:block;max-width:100%;">{{ $downloadLink }}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
