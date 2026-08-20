<p style="margin:0 0 12px 0;">Dear {{ $data['client_name'] ?? 'Client' }},</p>

@php
    $applicationLabel = trim(($data['country'] ?? '') . ' ' . ($data['category'] ?? ''));
    $applicationRef = trim((string) ($data['application_id'] ?? ''));
@endphp

<p style="margin:0 0 12px 0;">
    Please find attached the documents checklist for your
    <strong>{{ $applicationLabel }}</strong> application{{ $applicationRef !== '' ? ' (' . $applicationRef . ')' : '' }}.
</p>

@if(!empty($data['custom_message']))
    <p style="margin:0 0 12px 0;">{!! nl2br(e($data['custom_message'])) !!}</p>
@endif

<p style="margin:0 0 12px 0;">The documents we need from you are listed below, and the same list is attached as a PDF for your records.</p>

@foreach(($data['sections'] ?? []) as $section)
    <p style="margin:16px 0 6px 0;font-weight:bold;">{{ $section['title'] ?? 'Documents' }}</p>
    <ul style="margin:0 0 12px 0;padding-left:20px;">
        @foreach(($section['items'] ?? []) as $item)
            <li style="margin:0 0 4px 0;">{{ $item['label'] ?? '' }}</li>
        @endforeach
    </ul>
@endforeach

<p style="margin:16px 0 12px 0;">Please reply to this email with the documents attached, or contact us if anything on the list is unclear.</p>

<p style="margin:0;">Regards,<br>{{ $data['given_by'] ?? '' }}@if(($data['given_by'] ?? '') !== ($data['subscriber_name'] ?? ''))<br>{{ $data['subscriber_name'] ?? '' }}@endif</p>
