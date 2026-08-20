<p style="margin:0 0 12px 0;"><strong>Hello {{ $subscriberName }},</strong></p>
<p style="margin:0 0 16px 0;">We are pleased to confirm that your subscription has been renewed.</p>
<p style="margin:0 0 8px 0;"><strong>Plan:</strong> {{ $planDetails }}</p>
<p style="margin:0 0 8px 0;"><strong>Validity:</strong> {{ $validityDuration }}</p>
@if(isset($paidAmount) && $paidAmount !== null && $paidAmount !== '')
<p style="margin:0 0 16px 0;"><strong>Paid Amount:</strong> USD {{ number_format((float) $paidAmount, 2) }}</p>
@endif
<p style="margin:0 0 16px 0;">Your invoice is attached to this email. Thank you for continuing with Adwiseri.</p>
@include('emails.partials.signature')
