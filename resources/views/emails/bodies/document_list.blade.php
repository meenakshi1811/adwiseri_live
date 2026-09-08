<p style="margin:0 0 12px 0;">Dear {{ $data['client_name'] ?? 'Client' }},</p>

@php
    $applicationLabel = trim(($data['country'] ?? '') . ' ' . ($data['category'] ?? ''));
    $applicationRef = trim((string) ($data['application_id'] ?? ''));
    $uploadUrl = trim((string) ($data['upload_url'] ?? ''));
    $fileName = trim((string) ($data['attachment_name'] ?? ''));
@endphp

<p style="margin:0 0 12px 0;">
    Please find attached the documents checklist for your
    <strong>{{ $applicationLabel }}</strong> application{{ $applicationRef !== '' ? ' (' . $applicationRef . ')' : '' }}.
</p>

@if(!empty($data['custom_message']))
    <p style="margin:0 0 12px 0;">{!! nl2br(e($data['custom_message'])) !!}</p>
@endif

@if($uploadUrl !== '')
    @include('emails.partials.document_checklist_upload', [
        'uploadUrl' => $uploadUrl,
        'fileName' => $fileName,
    ])
@else
    <p style="margin:0 0 12px 0;">
        The full checklist is attached as a PDF for your records. Please review it and send us the required documents at your earliest convenience.
    </p>
@endif

<p style="margin:16px 0 12px 0;">If you prefer, you may also reply to this email with your documents attached, or contact us if anything on the checklist is unclear.</p>

<p style="margin:0;">Regards,<br>{{ $data['given_by'] ?? '' }}@if(($data['given_by'] ?? '') !== ($data['subscriber_name'] ?? ''))<br>{{ $data['subscriber_name'] ?? '' }}@endif</p>
