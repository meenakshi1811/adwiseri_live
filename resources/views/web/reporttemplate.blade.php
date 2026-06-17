<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>adwiseri</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background:#F5F5F5;font-family:'Lato',Arial,sans-serif;color:#1f2937;">
    <div style="width:100%;padding:28px 12px;box-sizing:border-box;">
        <div style="max-width:640px;width:100%;margin:0 auto;background:#ffffff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-sizing:border-box;">
            <div style="background:#695EEE;text-align:center;padding:12px 20px 16px;">
                <a href="https://adwiseri.com/" style="display:inline-block;">
                    <img width="170" src="{{ url('web_assets/images/Style2.png') }}" alt="Adwiseri" style="max-width:100%;height:auto;display:block;">
                </a>
            </div>

            <div style="padding:24px 22px;box-sizing:border-box;width:100%;max-width:100%;">
                <h2 style="text-align:center;margin:0 0 20px 0;">Monthly Reports</h2>

                <p style="margin:0 0 14px 0;"><strong>Hello {{ $data->name }}</strong></p>
                <p style="margin:0 0 14px 0;line-height:1.7;">
                    Adwiseri monthly reports for Applications and Invoices have been generated.
                    Download the reports using the links below.
                </p>

                <p style="margin:0 0 8px 0;"><strong>Report Modules Included:</strong></p>
                <ul style="margin:0 0 16px 18px;padding:0;line-height:1.7;">
                    @if(isset($data->application))
                        <li>Applications Module</li>
                    @endif
                    @if(isset($data->invoice))
                        <li>Invoices Module</li>
                    @endif
                </ul>

                <p style="margin:0 0 8px 0;"><strong>Download Reports:</strong></p>
                <ul style="margin:0 0 20px 18px;padding:0;line-height:1.7;">
                    @if(isset($data->invoice))
                        <li><a href="{{ asset('public/Exports/User'.$data->id.'/'.$data->invoice) }}" download="Invoice_Report">Download Invoice Report</a></li>
                    @endif
                    @if(isset($data->application))
                        <li><a href="{{ asset('public/Exports/User'.$data->id.'/'.$data->application) }}" download="Application_Report">Download Application Report</a></li>
                    @endif
                </ul>

                <p style="margin:0;line-height:1.7;">
                    Thanks,<br>
                    <strong>The Adwiseri Team</strong>
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
