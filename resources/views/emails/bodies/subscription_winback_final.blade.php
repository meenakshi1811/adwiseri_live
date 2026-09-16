@php
    $supportEmail = $supportEmail ?? \App\Support\BrandedMail::supportEmail();
    $walletAmount = $walletAmount ?? number_format((float) ($subscriber->wallet ?? 0), 2);
@endphp
<p style="margin:0 0 12px 0;"><strong>Hello {{ $subscriber->name }},</strong></p>
<p style="margin:0 0 12px 0;">Your Adwiseri subscription expired <strong>30 days ago</strong>. This is your <strong>final reminder</strong>.</p>
<p style="margin:0 0 12px 0;">Today is the last day of your 30-day win-back period. After today, your account will lapse.</p>
<p style="margin:0 0 12px 0;">Your <strong>wallet credit of USD {{ $walletAmount }}</strong> will also expire if you do not renew today.</p>
<p style="margin:0 0 20px 0;">Please renew now to keep your subscription and wallet credit.</p>
<p style="margin:0 0 20px 0;">
    <a href="{{ $renewalLink }}" class="email-cta" style="background:#695EEE;color:#ffffff;padding:12px 20px;border-radius:6px;text-decoration:none;display:inline-block;">Renew Now</a>
</p>
<p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
<p style="margin:0;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> or contact support team via email <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.</p>
@include('emails.partials.signature')
