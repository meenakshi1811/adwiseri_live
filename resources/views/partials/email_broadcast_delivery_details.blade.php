@php
    $chunkSize = (int) ($broadcastLimits['chunk_size'] ?? config('mail.broadcast_chunk_size', 25));
    $chunkDelay = (int) ($broadcastLimits['chunk_delay_seconds'] ?? config('mail.broadcast_chunk_delay_seconds', 2));
    $subjectMax = (int) ($broadcastLimits['subject_max'] ?? 200);
    $bodyMax = (int) ($broadcastLimits['body_max'] ?? 50000);
    $orgName = trim((string) ($subscriberFooter['organization'] ?? ''));
@endphp

<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-at"></i></div>
    <div>
        <div class="eb-info-label">Sender</div>
        <div class="eb-info-value">{{ $user->email ?: 'Not configured' }}</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-reply"></i></div>
    <div>
        <div class="eb-info-label">Reply To</div>
        <div class="eb-info-value">{{ $user->email ?: 'Not configured' }}</div>
    </div>
</div>
@if($orgName !== '')
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-building"></i></div>
    <div>
        <div class="eb-info-label">Organisation</div>
        <div class="eb-info-value">{{ $orgName }}</div>
    </div>
</div>
@endif
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-file-lines"></i></div>
    <div>
        <div class="eb-info-label">Email Format</div>
        <div class="eb-info-value">Rich HTML body with your branded footer</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-location-dot"></i></div>
    <div>
        <div class="eb-info-label">Footer Address</div>
        <div class="eb-info-value" id="eb_footer_address">{{ $subscriberFooter['address'] ?: 'Add your address in Profile' }}</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-globe"></i></div>
    <div>
        <div class="eb-info-label">Footer Website</div>
        <div class="eb-info-value" id="eb_footer_website">{{ $subscriberFooter['website'] ?: 'Add your website in Profile' }}</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-envelope"></i></div>
    <div>
        <div class="eb-info-label">Footer Email</div>
        <div class="eb-info-value" id="eb_footer_email">{{ $subscriberFooter['email'] ?: 'Not configured' }}</div>
    </div>
</div>

<div class="eb-delivery-divider"></div>

<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-gauge-high"></i></div>
    <div>
        <div class="eb-info-label">Recipients per Broadcast</div>
        <div class="eb-info-value">No fixed maximum — all selected staff or clients with a valid email</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-layer-group"></i></div>
    <div>
        <div class="eb-info-label">Sending Rate</div>
        <div class="eb-info-value">{{ $chunkSize }} emails per batch, {{ $chunkDelay }}s pause between batches</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-text-width"></i></div>
    <div>
        <div class="eb-info-label">Content Limits</div>
        <div class="eb-info-value">Subject up to {{ number_format($subjectMax) }} chars · Body up to {{ number_format($bodyMax) }} chars</div>
    </div>
</div>

<div class="eb-tip-box">
    <p><i class="fa-solid fa-lightbulb"></i><strong>Delivery details:</strong> Footer address, website, and email are loaded from this subscriber's Profile. Refresh this page after profile changes. Broadcasts queue in the background — you do not need to stay on this page.</p>
</div>
