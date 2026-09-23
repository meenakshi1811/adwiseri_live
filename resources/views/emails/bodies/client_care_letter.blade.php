<p style="margin:0 0 12px 0;">Dear {{ $data['client']->name }},</p>
@php
    $isIaaLetter = ($data['letter_type'] ?? '') === 'oisc_iaa';
    $documentTitle = $data['document_title'] ?? ($isIaaLetter ? 'Client Care Letter' : 'Service Agreement');
@endphp
<p style="margin:0 0 12px 0;">Please find attached your <strong>{{ $documentTitle }}</strong> for your {{ $data['application_type'] }} matter.</p>
<p style="margin:0 0 12px 0;">This document includes your instructions, advice provided, agreed work scope, timelines, fees and disbursements@if($isIaaLetter), and complaints guidance including IAA escalation details@else, and complaints guidance@endif.</p>
<p style="margin:0 0 16px 0;">Kindly review, sign, and return the attached {{ $isIaaLetter ? 'letter' : 'agreement' }} by replying to this email.</p>
<p style="margin:0;">Regards,<br>{{ $data['adviser_name'] }}@if(($data['adviser_name'] ?? '') !== ($data['organisation_name'] ?? ''))<br>{{ $data['organisation_name'] }}@endif</p>
