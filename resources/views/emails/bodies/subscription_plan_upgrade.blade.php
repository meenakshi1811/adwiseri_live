<p style="margin:0 0 12px 0;"><strong>Hello {{ $subscriberName }},</strong></p>
<p style="margin:0 0 16px 0;">We are pleased to inform you that your Subscription Plan has been upgraded.</p>
<p style="margin:0 0 16px 0;">Your subscription ({{ $oldPlanName }}) has been upgraded to {{ $planDetails }}.</p>
<p style="margin:0 0 8px 0;"><strong>Plan:</strong> {{ $planDetails }}</p>
<p style="margin:0 0 8px 0;"><strong>Validity:</strong> {{ $validityDuration }}</p>
@if(isset($paidAmount) && $paidAmount !== null && $paidAmount !== '')
<p style="margin:0 0 16px 0;"><strong>Paid Amount:</strong> USD {{ number_format((float) $paidAmount, 2) }}</p>
@endif
<p style="margin:0 0 16px 0;">Enjoy the benefits of upgraded plan.</p>
@include('emails.partials.signature')
