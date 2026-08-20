@php
    $supportEmail = $supportEmail ?? \App\Support\BrandedMail::supportEmail();
@endphp
<p style="margin:0 0 12px 0;"><strong>Dear {{ $name }},</strong></p>
<p style="margin:0 0 16px 0;">Congratulations! You have been rewarded with <strong>{{ $offer_label }}</strong> on your Adwiseri subscription account.</p>

@include('emails.partials.offer_details_table', [
    'offer_label' => $offer_label ?? '',
    'offer_description' => $offer_description ?? '',
    'credit_label' => $credit_label ?? null,
    'credit_value' => $credit_value ?? null,
    'credit_is_html' => $credit_is_html ?? false,
])

<p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
<p style="margin:0;">Check our <strong><a href="https://adwiseri.com/faqs" style="color:#695EEE;text-decoration:none;">FAQ Page</a></strong> or contact support team via email <a href="mailto:{{ $supportEmail }}" style="color:#695EEE;text-decoration:none;">{{ $supportEmail }}</a>.</p>
@include('emails.partials.signature')
