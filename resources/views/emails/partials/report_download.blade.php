<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;margin:16px 0 0 0;border:none;border-collapse:collapse;table-layout:fixed;mso-table-lspace:0;mso-table-rspace:0;">
    <tr>
        <td width="100%" style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
            <p style="margin:0 0 12px 0;font-size:14px;line-height:1.6;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
                The report PDF is attached to this email.
            </p>
            <p style="margin:0 0 12px 0;font-size:14px;line-height:1.6;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
                You can also download it using the secure link below. This link expires in 7 days.
            </p>
            @if(!empty($fileName))
                <p style="margin:0 0 16px 0;font-size:14px;line-height:1.6;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
                    <strong>File:</strong> {{ $fileName }}
                </p>
            @endif
        </td>
    </tr>
    <tr>
        <td width="100%" align="center" style="padding:0 0 16px 0;border:none;width:100%;max-width:100%;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;border-collapse:collapse;table-layout:fixed;">
                <tr>
                    <td width="100%" align="center" bgcolor="#695EEE" style="border-radius:6px;background-color:#695EEE;width:100%;max-width:100%;mso-padding-alt:14px 12px;">
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
                           style="display:block;width:100%;max-width:100%;box-sizing:border-box;padding:14px 12px;font-size:14px;font-weight:700;color:#ffffff !important;text-decoration:none;border-radius:6px;background-color:#695EEE;line-height:1.4;text-align:center;mso-hide:all;">
                            Download Report PDF
                        </a>
                        <!--<![endif]-->
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td width="100%" style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
            <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
                If the button does not work,
                <a href="{{ $downloadLink }}" class="email-break-link" target="_blank" rel="noopener noreferrer" style="color:#695EEE;text-decoration:underline;font-weight:600;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">tap this secure download link</a>.
            </p>
        </td>
    </tr>
</table>
