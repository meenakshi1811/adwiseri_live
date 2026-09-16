@php
    $supportEmail = $supportEmail ?? \App\Support\BrandedMail::supportEmail();
    $daysExpired = (int) ($daysExpired ?? 0);
    $daysLeft = (int) ($daysLeft ?? 0);
@endphp
<p style="margin:0 0 12px 0;"><strong>Hello {{ $subscriber->name }},</strong></p>
<p style="margin:0 0 12px 0;">Your Adwiseri subscription expired <strong>{{ $daysExpired }} {{ $daysExpired === 1 ? 'day' : 'days' }} ago</strong>.</p>
<p style="margin:0 0 12px 0;">You are still in the 30-day win-back period. Please renew within <strong>{{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }}</strong> to keep your account active.</p>
<p style="margin:0 0 20px 0;">
    <a href="{{ $renewalLink }}" class="email-cta" style="background:#695EEE;color:#ffffff;padding:12px 20px;border-radius:6px;text-decoration:none;display:inline-block;">Renew Now</a>
</p>
<p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
<p style="margin:0;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> or contact support team via email <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.</p>
@include('emails.partials.signature')
