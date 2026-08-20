<p style="margin:0 0 12px 0;"><strong>Hello {{ $subscriber->name }},</strong></p>
<p style="margin:0 0 12px 0;">Your Adwiseri subscription will expire in <strong>{{ $daysRemaining }} days</strong>.</p>
<p style="margin:0 0 20px 0;">Please renew your subscription to avoid interruptions to your account.</p>
<p style="margin:0 0 20px 0;">
    <a href="{{ $renewalLink }}" style="background:#695EEE;color:#ffffff;padding:12px 20px;border-radius:6px;text-decoration:none;display:inline-block;">Renew Now</a>
</p>
<p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
<p style="margin:0;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> or contact support team via email <a href="mailto:{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}">{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}</a>.</p>
@include('emails.partials.signature')
