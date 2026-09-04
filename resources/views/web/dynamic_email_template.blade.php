<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $pageTitle ?? $headerTitle ?? 'Adwiseri' }}</title>
    <style type="text/css">
        body, table, td, p, a, h1 {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            max-width: 100%;
        }

        .email-content strong,
        .email-content b {
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .email-content table[role="presentation"],
        .email-content table[role="presentation"] tr,
        .email-content table[role="presentation"] td {
            border: none !important;
            margin: 0;
        }

        .email-content table[role="presentation"] td {
            padding: 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .email-content table[role="presentation"] {
            width: 100% !important;
            max-width: 100% !important;
            table-layout: fixed !important;
        }

        .email-content .email-url-box,
        .email-content .email-break-link {
            word-wrap: break-word !important;
            overflow-wrap: anywhere !important;
            word-break: break-all !important;
            white-space: normal !important;
            max-width: 100% !important;
        }

        .email-content,
        .email-content p,
        .email-content td,
        .email-content div {
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            max-width: 100%;
        }

        .email-content {
            min-width: 0;
        }

        .email-content a:not(.email-cta) {
            word-break: break-all;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .email-content a.email-cta {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            word-break: normal;
        }

        .email-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 12px auto;
        }

        .email-content h1,
        .email-content h2,
        .email-content h3,
        .email-content h4 {
            margin: 0 0 12px;
            line-height: 1.35;
            color: #111827;
        }

        .email-content h1 { font-size: 24px; }
        .email-content h2 { font-size: 20px; }
        .email-content h3 { font-size: 18px; }
        .email-content h4 { font-size: 16px; }

        .email-content p {
            margin: 0 0 12px;
        }

        .email-content ul,
        .email-content ol {
            margin: 0 0 12px 20px;
            padding: 0;
        }

        .email-content blockquote {
            margin: 0 0 12px;
            padding: 10px 14px;
            border-left: 4px solid #695EEE;
            background: #f8f9ff;
        }

        .email-content table:not([role="presentation"]) {
            width: 100%;
            max-width: 100%;
            margin: 12px 0;
            table-layout: fixed;
        }

        .email-content table:not([role="presentation"]) td,
        .email-content table:not([role="presentation"]) th {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
        }

        @media only screen and (max-width: 600px) {
            .email-shell {
                padding: 16px 8px !important;
            }

            .email-container {
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 8px !important;
            }

            .email-header {
                padding: 14px 16px !important;
            }

            .email-header-title {
                font-size: 18px !important;
                line-height: 1.4 !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }

            .email-content {
                padding: 18px 14px !important;
                font-size: 14px !important;
            }

            .email-footer {
                padding: 16px 14px !important;
            }

            .email-footer p,
            .email-footer a {
                font-size: 12px !important;
                line-height: 1.6 !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
            }

            .invoice-summary-table,
            .invoice-summary-table tbody,
            .invoice-summary-table tr,
            .invoice-summary-table td {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            .invoice-summary-table tr {
                border-bottom: 1px solid #e5e7eb !important;
            }

            .invoice-summary-table tr:last-child {
                border-bottom: none !important;
            }

            .invoice-summary-table td {
                text-align: left !important;
                border-bottom: none !important;
            }

            .invoice-summary-label {
                padding-bottom: 4px !important;
            }

            .invoice-summary-value {
                padding-top: 0 !important;
                padding-bottom: 10px !important;
            }

            .email-content table[role="presentation"],
            .email-content table[role="presentation"] tr,
            .email-content table[role="presentation"] td {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            .email-content .email-url-box,
            .email-content .email-break-link {
                word-break: break-all !important;
                overflow-wrap: anywhere !important;
                white-space: normal !important;
                display: inline !important;
                max-width: 100% !important;
            }

            .email-content a.email-cta {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
                padding: 14px 12px !important;
            }

            .email-footer p {
                display: block !important;
            }

            .email-footer .footer-platform-links a {
                display: block !important;
                margin: 4px 0 !important;
            }

            .email-footer .footer-platform-links span {
                display: none !important;
            }

            .email-footer .footer-contact-item,
            .email-footer .footer-contact-item a {
                display: inline !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f3f5fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;width:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="email-shell" style="width:100%;background-color:#f3f5fb;margin:0;padding:28px 12px;border-collapse:collapse;">
        <tr>
            <td align="center" valign="top" style="padding:0;margin:0;">
                <!--[if mso]>
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0"><tr><td>
                <![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="email-container" style="width:100%;max-width:640px;background:#ffffff;border-radius:10px;border:1px solid #e5e7eb;table-layout:fixed;">
                    <tr>
                        <td class="email-header" style="@if(!empty($headerLogoUrl) || (($footerMode ?? 'platform') === 'subscriber' && !empty($headerLogoAlt))) background:#ffffff;padding:24px 24px 20px;text-align:center;border-bottom:1px solid #e5e7eb; @else background:#695EEE;color:#ffffff;padding:18px 24px;text-align:center; @endif">
                            @if(!empty($headerLogoUrl))
                                <img width="170" src="{{ $headerLogoUrl }}" alt="{{ $headerLogoAlt ?? 'Logo' }}" style="max-width:170px;height:auto;border:0;display:inline-block;">
                            @elseif(($footerMode ?? 'platform') === 'subscriber' && !empty($headerLogoAlt))
                                <p style="margin:0;font-size:20px;font-weight:700;color:#111827;line-height:1.35;word-wrap:break-word;overflow-wrap:break-word;">{{ $headerLogoAlt }}</p>
                            @elseif(!empty($headerTitle))
                                <h1 class="email-header-title" style="margin:0;font-size:22px;font-weight:700;color:#ffffff;line-height:1.35;word-wrap:break-word;overflow-wrap:break-word;">{{ $headerTitle }}</h1>
                            @else
                                <a href="https://adwiseri.com/" style="display:inline-block;">
                                    <img width="170" src="{{ url('web_assets/images/Style2.png') }}" alt="Adwiseri" style="max-width:100%;height:auto;border:0;">
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" width="100%" style="padding:24px 22px;font-size:14px;line-height:1.7;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;width:100%;max-width:640px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;table-layout:fixed;border:none;border-collapse:collapse;">
                                <tr>
                                    <td width="100%" style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;width:100%;max-width:100%;">
                                        {!! $content !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:24px;line-height:24px;font-size:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="email-footer" style="text-align:center;background:#695EEE;padding:20px 24px;color:#ffffff;">
                            @if(($footerMode ?? 'platform') === 'subscriber' && !empty($subscriberFooter))
                                @if(!empty($subscriberFooter['address']))
                                    <p style="margin:0 0 8px;color:#ffffff;font-size:13px;line-height:1.6;word-wrap:break-word;overflow-wrap:break-word;">{{ $subscriberFooter['address'] }}</p>
                                @endif
                                <p style="margin:0;font-size:13px;line-height:1.6;word-wrap:break-word;overflow-wrap:break-word;">
                                    @if(!empty($subscriberFooter['website']) && !empty($subscriberFooter['website_url']))
                                        <span class="footer-contact-item">Web: <a href="{{ $subscriberFooter['website_url'] }}" style="color:#ffffff;text-decoration:underline;">{{ $subscriberFooter['website'] }}</a></span>
                                    @endif
                                    @if(!empty($subscriberFooter['website']) && !empty($subscriberFooter['email']))
                                        <span class="footer-contact-item" style="color:#ffffff;"> &nbsp;|&nbsp; </span>
                                    @endif
                                    @if(!empty($subscriberFooter['email']))
                                        <span class="footer-contact-item">Email: <a href="mailto:{{ $subscriberFooter['email'] }}" style="color:#ffffff;text-decoration:underline;">{{ $subscriberFooter['email'] }}</a></span>
                                    @endif
                                </p>
                            @else
                                <p style="margin:0 0 8px;color:#ffffff;font-size:13px;word-wrap:break-word;overflow-wrap:break-word;">&copy; {{ $copyrightYears ?? \App\Support\BrandedMail::copyrightYears() }} Adwiseri. All rights reserved.</p>
                                <p class="footer-platform-links" style="margin:0;font-size:13px;word-wrap:break-word;overflow-wrap:break-word;line-height:1.6;">
                                    <a style="color:#ffffff;text-decoration:none;" href="https://adwiseri.com/terms_of_use">Terms of Use</a>
                                    <span style="color:#ffffff;"> | </span>
                                    <a style="color:#ffffff;text-decoration:none;" href="https://adwiseri.com/privacy_policy">Privacy Policy</a>
                                    <span style="color:#ffffff;"> | </span>
                                    <a style="color:#ffffff;text-decoration:none;" href="https://adwiseri.com/contactus">Contact Support</a>
                                </p>
                                <p style="margin:10px 0 0;color:#ffffff;font-size:12px;line-height:1.6;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">Need help? Check our <a href="https://adwiseri.com/faqs" style="color:#ffffff;text-decoration:underline;">FAQ Page</a> or contact support via <a href="mailto:{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}" style="color:#ffffff;text-decoration:underline;word-break:break-all;">{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}</a></p>
                            @endif
                        </td>
                    </tr>
                </table>
                <!--[if mso]>
                </td></tr></table>
                <![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
