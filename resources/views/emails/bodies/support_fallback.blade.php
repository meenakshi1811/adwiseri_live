<p style="margin:0 0 12px 0;"><strong>Hello,</strong></p>
<p style="margin:0 0 16px 0;">A new support ticket has been raised on Adwiseri.</p>
<p style="margin:0 0 6px 0;"><strong>Ticket ID:</strong> {{ $data->ticket_id }}</p>
<p style="margin:0 0 6px 0;"><strong>Subscriber ID:</strong> {{ $data->subscriber_id }}</p>
<p style="margin:0 0 6px 0;"><strong>Department:</strong> {{ $data->support ?? $data->department ?? '-' }}</p>
<p style="margin:0 0 6px 0;"><strong>Date:</strong> {{ !empty($data->date) ? date('d-m-Y H:i:s', strtotime($data->date)) : '-' }}</p>
<p style="margin:0 0 6px 0;"><strong>Issue:</strong> {{ $data->issue }}</p>
<p style="margin:0 0 16px 0;"><strong>Attachment:</strong> {{ $data->attachment_label ?? 'No attachment' }}</p>
@include('emails.partials.signature')
