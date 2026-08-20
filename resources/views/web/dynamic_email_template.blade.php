<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $headerTitle ?? 'Adwiseri' }}</title>
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
            table-layout: fixed;
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

        .email-content table:not([role="presentation"]) {
            max-width: 100%;
            table-layout: auto;
        }

        .email-content a:not(.email-cta) {
            word-break: break-all;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .email-content a.email-cta {
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
            margin: 12px 0;
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
                display: block !important;
                max-width: 100% !important;
            }

            .email-content a[style*="display:inline-block"],
            .email-content a[style*="display: inline-block"],
            .email-content .email-cta {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
                margin: 10px 0 !important;
            }

            .email-footer p {
                display: block !important;
            }

            .email-footer span {
                display: none !important;
            }

            .email-footer a {
                display: block !important;
                margin: 4px 0 !important;
            }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f3f5fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div class="email-shell" style="width:100%;padding:28px 12px;box-sizing:border-box;">
        <div class="email-container" style="max-width:640px;width:100%;margin:0 auto;background:#ffffff;border-radius:10px;border:1px solid #e5e7eb;box-sizing:border-box;">
            <div class="email-header" style="background:#695EEE;color:#ffffff;padding:18px 24px;text-align:center;box-sizing:border-box;">
                @if(!empty($headerTitle))
                    <h1 class="email-header-title" style="margin:0;font-size:22px;font-weight:700;color:#ffffff;line-height:1.35;word-wrap:break-word;overflow-wrap:break-word;">{{ $headerTitle }}</h1>
                @else
                    <a href="https://adwiseri.com/" style="display:inline-block;">
                        <img width="170" src="{{ url('web_assets/images/Style2.png') }}" alt="Adwiseri" style="max-width:100%;height:auto;">
                    </a>
                @endif
            </div>
            <div class="email-content" style="padding:24px 22px;font-size:14px;line-height:1.7;overflow-wrap:anywhere;word-break:break-word;box-sizing:border-box;width:100%;max-width:100%;min-width:0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;table-layout:fixed;border:none;border-collapse:collapse;">
                    <tr>
                        <td style="padding:0;border:none;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;width:100%;">
                            {!! $content !!}
                        </td>
                    </tr>
                </table>
            </div>
            <div style="height:24px;line-height:24px;font-size:0;">&nbsp;</div>
            <div class="email-footer" style="text-align:center;background:#695EEE;padding:20px 24px;color:#ffffff;box-sizing:border-box;">
                @if(($footerMode ?? 'platform') === 'subscriber' && !empty($subscriberFooter))
                    <p style="margin:0 0 8px;color:#ffffff;font-size:13px;word-wrap:break-word;overflow-wrap:break-word;">{{ $subscriberFooter['copyright'] ?? '' }}</p>
                    @if(!empty($subscriberFooter['address']))
                        <p style="margin:0 0 8px;color:#ffffff;font-size:13px;line-height:1.6;word-wrap:break-word;overflow-wrap:break-word;">{{ $subscriberFooter['address'] }}</p>
                    @endif
                    <p style="margin:0;font-size:13px;line-height:1.6;word-wrap:break-word;overflow-wrap:break-word;">
                        @if(!empty($subscriberFooter['website']) && !empty($subscriberFooter['website_url']))
                            Web: <a href="{{ $subscriberFooter['website_url'] }}" style="color:#ffffff;text-decoration:underline;word-break:break-all;">{{ $subscriberFooter['website'] }}</a>
                        @endif
                        @if(!empty($subscriberFooter['website']) && !empty($subscriberFooter['email']))
                            <span style="color:#ffffff;"> | </span>
                        @endif
                        @if(!empty($subscriberFooter['email']))
                            Email: <a href="mailto:{{ $subscriberFooter['email'] }}" style="color:#ffffff;text-decoration:underline;word-break:break-all;">{{ $subscriberFooter['email'] }}</a>
                        @endif
                    </p>
                @else
                    <p style="margin:0 0 8px;color:#ffffff;font-size:13px;word-wrap:break-word;overflow-wrap:break-word;">&copy; {{ $copyrightYears ?? \App\Support\BrandedMail::copyrightYears() }} Adwiseri. All rights reserved.</p>
                    <p style="margin:0;font-size:13px;word-wrap:break-word;overflow-wrap:break-word;line-height:1.6;">
                        <a style="color:#ffffff;text-decoration:none;" href="https://adwiseri.com/terms_of_use">Terms of Use</a>
                        <span style="color:#ffffff;"> | </span>
                        <a style="color:#ffffff;text-decoration:none;" href="https://adwiseri.com/privacy_policy">Privacy Policy</a>
                        <span style="color:#ffffff;"> | </span>
                        <a style="color:#ffffff;text-decoration:none;" href="https://adwiseri.com/contactus">Contact Support</a>
                    </p>
                    <p style="margin:10px 0 0;color:#ffffff;font-size:12px;line-height:1.6;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">Need help? Check our <a href="https://adwiseri.com/faqs" style="color:#ffffff;text-decoration:underline;">FAQ Page</a> or contact support via <a href="mailto:{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}" style="color:#ffffff;text-decoration:underline;word-break:break-all;">{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}</a></p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
