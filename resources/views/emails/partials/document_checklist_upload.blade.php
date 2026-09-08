<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;margin:16px 0 0 0;border:none;border-collapse:collapse;table-layout:fixed;mso-table-lspace:0;mso-table-rspace:0;">
    <tr>
        <td width="100%" style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
            <p style="margin:0 0 12px 0;font-size:14px;line-height:1.6;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
                The full documents checklist is attached to this email as a PDF. You can also upload the required documents online using the secure link below to save time and effort.
            </p>
            @if(!empty($fileName))
                <p style="margin:0 0 16px 0;font-size:14px;line-height:1.6;color:#1f2937;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
                    <strong>Attachment:</strong> {{ $fileName }}
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
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $uploadUrl }}" style="height:44px;v-text-anchor:middle;width:100%;" arcsize="12%" strokecolor="#695EEE" fillcolor="#695EEE">
                            <w:anchorlock/>
                            <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">Upload Documents Online</center>
                        </v:roundrect>
                        <![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $uploadUrl }}"
                           class="email-cta"
                           target="_blank"
                           rel="noopener noreferrer"
                           style="display:block;width:100%;max-width:100%;box-sizing:border-box;padding:14px 12px;font-size:14px;font-weight:700;color:#ffffff !important;text-decoration:none;border-radius:6px;background-color:#695EEE;line-height:1.4;text-align:center;mso-hide:all;">
                            Upload Documents Online
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
                <a href="{{ $uploadUrl }}" class="email-break-link" target="_blank" rel="noopener noreferrer" style="color:#695EEE;text-decoration:underline;font-weight:600;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">open this secure upload link</a>.
            </p>
        </td>
    </tr>
</table>
