@php
    $supportEmail = \App\Support\BrandedMail::supportEmail();
    $textStyle = 'margin:0 0 16px 0;line-height:1.7;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;max-width:100%;';
@endphp

<style type="text/css">
    @media only screen and (max-width: 600px) {
        .admin-2fa-otp-code {
            font-size: 24px !important;
            letter-spacing: 4px !important;
        }

        .admin-2fa-otp-box {
            padding: 14px 10px !important;
        }

        .admin-2fa-otp-label {
            font-size: 12px !important;
        }
    }
</style>

<p style="{{ $textStyle }}"><strong>Hello {{ $data->name ?? 'Admin' }},</strong></p>

<p style="{{ $textStyle }}">A login attempt was made on your Adwiseri admin account. Use the verification code below to complete your sign in.</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;border-collapse:collapse;">
    <tr>
        <td align="center" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" class="admin-2fa-otp-box" style="width:100%;max-width:320px;margin:0 auto;background:#f3f5fb;border:1px solid #e5e7eb;border-radius:10px;border-collapse:separate;">
                <tr>
                    <td align="center" style="padding:16px 20px;box-sizing:border-box;">
                        <div class="admin-2fa-otp-label" style="font-size:13px;color:#6b7280;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;word-wrap:break-word;overflow-wrap:break-word;">Your OTP</div>
                        <div class="admin-2fa-otp-code" style="font-size:30px;font-weight:700;letter-spacing:6px;color:#695EEE;word-wrap:break-word;overflow-wrap:break-word;word-break:break-all;line-height:1.3;">{{ $data->otp }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="{{ $textStyle }}">This code is valid for <strong>{{ $data->ttlMinutes ?? 5 }} minutes</strong>. Please do not share it with anyone.</p>

<p style="{{ $textStyle }}">If you did not attempt to log in, please secure your account and contact support immediately at
    <a href="mailto:{{ $supportEmail }}" style="color:#695EEE;word-wrap:break-word;overflow-wrap:break-word;word-break:break-all;">{{ $supportEmail }}</a>.</p>

<p style="margin:16px 0 0 0;word-wrap:break-word;overflow-wrap:break-word;max-width:100%;">Sincerely,<br><strong>Adwiseri</strong></p>
