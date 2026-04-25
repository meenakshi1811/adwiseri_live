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

            <div style="padding:24px 22px;line-height:1.7;box-sizing:border-box;width:100%;max-width:100%;">
                <h2 style="text-align:center;margin:0 0 20px 0;">Welcome</h2>

                <div style="margin-bottom:20px;">
                    @if(isset($data->password))
                        <p style="margin:0 0 12px 0;"><strong>Hello {{ $data->name }}</strong></p>
                        <p style="margin:0;">Your OTP for password recovery is {{ $data->otp }}.</p>
                    @elseif(isset($data->message))
                        <p style="margin:0 0 12px 0;"><strong>Hello,</strong></p>
                        <p style="margin:0;">{{ $data->name }} sent a message to you from adwiseri.</p>
                    @elseif(isset($data->how_did_hear))
                        <p style="margin:0 0 12px 0;"><strong>Hello,</strong></p>
                        <p style="margin:0;">A demo request was submitted on adwiseri.com.</p>
                    @else
                        <p style="margin:0 0 12px 0;"><strong>Hello {{ $data->name }}</strong></p>
                        <p style="margin:0;">Thanks for joining. We're really excited to have you on board.<br>Your Email Verification OTP is {{ $data->otp }}.</p>
                    @endif
                </div>

                <div>
                    @if(isset($data->message))
                        <p style="margin:0 0 6px 0;"><strong>Name:</strong> {{ $data->name }}</p>
                        <p style="margin:0 0 6px 0;"><strong>Email:</strong> {{ $data->email }}</p>
                        <p style="margin:0 0 6px 0;"><strong>Phone:</strong> {{ $data->phone }}</p>
                        <p style="margin:0 0 6px 0;"><strong>Country:</strong> {{ $data->country }}</p>
                        <p style="margin:0 0 12px 0;"><strong>City:</strong> {{ $data->city }}</p>
                        <p style="margin:0;"><strong>Message:</strong> {{ $data->message }}</p>
                    @else
                        <p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
                        <p style="margin:0 0 10px 0;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> for a quick answer.</p>
                        <p style="margin:0;">You can always contact our support team via live chat and email.<br>We will be happy to help you!<br><br>Thanks,<br><strong>The Adwiseri Team</strong></p>
                    @endif
                </div>
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
