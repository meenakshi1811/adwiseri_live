@php
    $supportEmail = $supportEmail ?? \App\Support\BrandedMail::supportEmail();
@endphp
<p style="margin:0 0 12px 0;"><strong>Dear {{ $name }},</strong></p>
<p style="margin:0 0 16px 0;">We are pleased to inform you that credits, discounts, or additional term benefits have been applied to your Adwiseri subscription account. Details are below:</p>
<p style="margin:0 0 8px 0;"><strong>Transaction Type:</strong> {{ ucwords(str_replace('_', ' ', $type ?? '')) }}</p>
@if (($type ?? '') === 'double_term')
    <p style="margin:0 0 8px 0;"><strong>Description:</strong> Double Subscription Term</p>
    <p style="margin:0 0 16px 0;"><strong>Details:</strong> One additional year has been added to your subscription expiry date.</p>
@else
    <p style="margin:0 0 8px 0;"><strong>Credit Amount:</strong> USD {{ number_format((float) ($credit_amount ?? $value ?? 0), 2) }}</p>
    <p style="margin:0 0 16px 0;"><strong>Description:</strong> {{ $description ?? 'One-off Credit / Offer / Dispute Resolution' }}</p>
@endif
<p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
<p style="margin:0;">Check our <strong><a href="https://adwiseri.com/faqs" style="color:#695EEE;text-decoration:none;">FAQ Page</a></strong> or contact support team via email <a href="mailto:{{ $supportEmail }}" style="color:#695EEE;text-decoration:none;">{{ $supportEmail }}</a>.</p>
@include('emails.partials.signature')
