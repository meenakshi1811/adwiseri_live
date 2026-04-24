<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>adwiseri</title>
</head>
<body style="margin:0;padding:0;background:#F5F5F5;font-family:'Lato',Arial,sans-serif;color:#1f2937;">
    <div style="width:100%;padding:28px 12px;box-sizing:border-box;">
        <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;">
            <div style="background:#695EEE;text-align:center;padding:12px 20px 16px;">
                <a href="https://adwiseri.com/" style="display:inline-block;">
                    <img width="170" src="{{ url('web_assets/images/Style2.png') }}" alt="Adwiseri" style="max-width:100%;height:auto;display:block;">
                </a>
            </div>

            <div style="padding:24px 30px;line-height:1.7;">
                <h2 style="text-align:center;margin:0 0 20px 0;">Scheduled Report</h2>

                <p style="margin:0 0 14px 0;"><strong>Hello {{ $data['recipient_name'] }},</strong></p>
                <p style="margin:0 0 14px 0;">
                    @if(($data['frequency'] ?? '') === 'daily')
                        Please find your scheduled report for <strong>{{ $data['start_date'] }}</strong>.
                    @else
                        Please find scheduled report(s) attached for the period
                        <strong>{{ $data['start_date'] }} - {{ $data['end_date'] }}</strong>.
                    @endif
                </p>

                @if(!empty($data['modules']) && is_array($data['modules']))
                    <p style="margin:0 0 8px 0;"><strong>Report Modules Included:</strong></p>
                    <ul style="margin:0 0 14px 18px;padding:0;">
                        @foreach($data['modules'] as $module)
                            <li>{{ ucwords(str_replace('_', ' ', $module)) }}</li>
                        @endforeach
                    </ul>
                @endif

                <p style="margin:0 0 14px 0;">
                    For your convenience, the report is attached to this email and can also be downloaded using the secure link below.
                </p>

                <p style="margin:0 0 8px 0;"><strong>Download Link:</strong></p>
                <p style="margin:0 0 20px 0;word-break:break-all;">
                    <a href="{{ $data['download_link'] }}">{{ $data['download_link'] }}</a>
                </p>

                <p style="margin:0;">
                    Sincerely,<br>
                    <strong>Adwiseri</strong>
                </p>
            </div>

            <div style="text-align:center;background:#695EEE;padding:20px 15px;color:#ffffff;">
                <p style="margin:0 0 8px 0;">&copy; {{ date('Y') }} adwiseri. All rights reserved.</p>
                <p style="margin:0;">
                    <a style="color:#ffffff;" href="https://adwiseri.com/terms_of_use">Terms of Use</a> |
                    <a style="color:#ffffff;" href="https://adwiseri.com/privacy_policy">Privacy Policy</a> |
                    <a style="color:#ffffff;" href="https://adwiseri.com/contactus">Contact Support</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
