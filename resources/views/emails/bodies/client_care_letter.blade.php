<p style="margin:0 0 12px 0;">Dear {{ $data['client']->name }},</p>
<p style="margin:0 0 12px 0;">Please find attached your {{ $data['document_title'] }} for your {{ $data['application_type'] }} matter.</p>
<p style="margin:0 0 12px 0;">This document includes your instructions, advice provided, agreed work scope, timelines, fees/disbursements and complaints guidance.</p>
<p style="margin:0 0 16px 0;">Kindly review, sign, and return it by replying to this email.</p>
<p style="margin:0;">Regards,<br>{{ $data['adviser_name'] }}@if(($data['adviser_name'] ?? '') !== ($data['organisation_name'] ?? ''))<br>{{ $data['organisation_name'] }}@endif</p>
