<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>adwiseri</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
</head>

<style>
    body {
        background: #F5F5F5;
        font-family: 'Lato';
    }
    .email-container {
        border-radius: 10px;
        width: 100%;
        max-width: 640px;
        background: white;
        padding-bottom: 40px;
        position: relative;
        margin: auto;
    }
    .email-body p {
        margin: 0 0 16px 0;
        line-height: 1.9;
    }
    .email-body .details-line {
        margin-bottom: 16px;
    }

    .footer-links a {
        color: white;
        text-decoration: underline;
        margin: 0 10px;
        font-size: 14px;
    }

    .footer-links {
        margin-top: 10px;
    }
</style>

<body style="background: #F5F5F5; margin:0px;">
    <div style="margin:40px 0px;">
        <div class="email-container">
            <div style="background:#695EEE;padding:16px 0px;text-align:center;border-radius:10px 10px 0 0;">
                <a class="navbar-brand text-white" href="https://adwiseri.com/">
                    <img width="170" src="{{ url('web_assets/images/Style2.png') }}" alt="adwiseri logo" />
                </a>
            </div>
            <h2 style="text-align:center;padding:20px 0px;">{{ $title ?? 'Subscription Plan Details' }}</h2>
            <div style="margin-bottom:20px;text-align:center;">
                <img src="{{ url('web_assets/images/handshake.png') }}" width="150px" height="auto"
                    style="border:1px solid lightgrey;border-radius:7px;padding:15px 25px;position: relative;margin:auto;" alt="Subscription handshake">
            </div>
            <div class="email-body" style="padding: 0px 30px;">
                <div style="margin-bottom:40px;">
                    <p><strong>Hello {{ $subscriberName }},</strong></p>
                    <p>We are pleased to inform you that {{ $title }}.</p>
                    <p>
                        The subscription details are as follows:<br><br>
                        <span class="details-line"><strong>Plan Name</strong> : {{ $planDetails }}</span><br>
                        <span class="details-line"><strong>Validity Duration</strong> : {{ $validityDuration }} Days starting from today</span><br>
                    </p>
                </div>

                <div style="margin-bottom:40px;">
                    <p><strong>Have a question?</strong></p>
                    <p>Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> for a quick answer.</p>
                    <p>
                        You can always contact our support team via email or by raising a ticket from the support section.<br>
                        We will be happy to help you!<br><br>
                        Thanks,<br>
                        <b>The Adwiseri Team</b>
                    </p>
                </div>
            </div>
            <div style="text-align:center;background:#695EEE;padding:20px 15px;color:white;border-radius:0 0 10px 10px;">
                <p style="margin:0 0 10px 0;">&copy; {{ date('Y') }} adwiseri. All rights reserved.</p>
                <div style="text-align:center" class="footer-links">
                    <a style="text-align:center; color:white;" href="https://adwiseri.com/terms_of_use">Terms of Use</a> |
                    <a style="text-align:center; color:white;" href="https://adwiseri.com/privacy_policy">Privacy Policy</a> |
                    <a style="text-align:center; color:white;" href="https://adwiseri.com/contactus">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
