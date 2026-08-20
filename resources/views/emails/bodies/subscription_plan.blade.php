<p style="margin:0 0 12px 0;"><strong>Hello {{ $subscriberName }},</strong></p>
<p style="margin:0 0 16px 0;">We are pleased to inform you that {{ $title }}.</p>
<p style="margin:0 0 8px 0;"><strong>Plan Details:</strong> {{ $planDetails }}</p>
<p style="margin:0 0 8px 0;"><strong>Validity Duration:</strong> {{ $validityDuration }}</p>
@if(isset($paidAmount) && $paidAmount !== null && $paidAmount !== '')
<p style="margin:0 0 16px 0;"><strong>Paid Amount:</strong> USD {{ number_format((float) $paidAmount, 2) }}</p>
@endif
<p style="margin:0 0 10px 0;"><strong>Have a question?</strong></p>
<p style="margin:0 0 10px 0;">Check our <strong><a href="https://adwiseri.com/faqs">FAQ Page</a></strong> for a quick answer.</p>
<p style="margin:0;">You can contact support team via email <a href="mailto:{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}">{{ $supportEmail ?? \App\Support\BrandedMail::supportEmail() }}</a> or by raising ticket.</p>
@include('emails.partials.signature')
