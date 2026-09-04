@php
    $chunkSize = (int) ($broadcastLimits['chunk_size'] ?? config('mail.broadcast_chunk_size', 25));
    $chunkDelay = (int) ($broadcastLimits['chunk_delay_seconds'] ?? config('mail.broadcast_chunk_delay_seconds', 2));
    $subjectMax = (int) ($broadcastLimits['subject_max'] ?? 200);
    $bodyMax = (int) ($broadcastLimits['body_max'] ?? 50000);
    $maxRecipients = (int) ($broadcastLimits['max_recipients'] ?? config('mail.broadcast_max_recipients', 0));
    $orgName = trim((string) ($subscriberFooter['organization'] ?? ''));
    $subscriberName = trim((string) ($subscriberFooter['name'] ?? ''));
    $senderDisplayName = $subscriberName !== ''
        ? \App\Support\BrandedMail::alertsFromName($subscriberName)
        : ($orgName !== '' ? \App\Support\BrandedMail::sentOnBehalfOf($orgName) : 'Sent on behalf of Subscriber');
    $subscriberEmail = trim((string) ($subscriberFooter['email'] ?? ''));
    $alertsFrom = \App\Support\BrandedMail::alertsFromAddress();
    $usageLimit = (int) ($broadcastUsage['limit'] ?? 0);
    $usageUsed = (int) ($broadcastUsage['used'] ?? 0);
    $usageRemaining = (int) ($broadcastUsage['remaining'] ?? 0);
    $usagePerYear = (int) ($broadcastUsage['per_year'] ?? 0);
    $usagePlanName = trim((string) ($broadcastUsage['plan_name'] ?? ''));
@endphp

<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-at"></i></div>
    <div>
        <div class="eb-info-label">Sender (From)</div>
        <div class="eb-info-value">{{ $senderDisplayName }} &lt;{{ $alertsFrom }}&gt;</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-reply"></i></div>
    <div>
        <div class="eb-info-label">Reply To</div>
        <div class="eb-info-value">{{ $subscriberEmail ?: 'Add your email in Profile' }}</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-copy"></i></div>
    <div>
        <div class="eb-info-label">Monitoring Copy (Bcc)</div>
        <div class="eb-info-value">{{ $subscriberEmail ? 'One copy sent to ' . $subscriberEmail : 'Not configured' }}</div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-file-lines"></i></div>
    <div>
        <div class="eb-info-label">Email Format</div>
        <div class="eb-info-value">Your logo, rich HTML body, and branded footer</div>
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
        <div class="eb-info-value" id="eb_footer_email">{{ $subscriberEmail ?: 'Not configured' }}</div>
    </div>
</div>

<div class="eb-delivery-divider"></div>

<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-gauge-high"></i></div>
    <div>
        <div class="eb-info-label">Recipients per Broadcast</div>
        <div class="eb-info-value">
            @if($maxRecipients > 0)
                Up to {{ number_format($maxRecipients) }} recipients per broadcast
            @else
                No fixed maximum — all selected staff or clients with a valid email
            @endif
        </div>
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
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-tags"></i></div>
    <div>
        <div class="eb-info-label">Plan Email Allowance</div>
        <div class="eb-info-value" id="eb_plan_email_allowance">
            @if($usagePerYear > 0)
                {{ number_format($usagePerYear) }}/year on {{ $usagePlanName !== '' ? $usagePlanName : 'your plan' }}
                @if($usageLimit > $usagePerYear)
                    ({{ number_format($usageLimit) }} total this subscription term)
                @endif
            @else
                Not included on {{ $usagePlanName !== '' ? $usagePlanName : 'your current plan' }}
            @endif
        </div>
    </div>
</div>
<div class="eb-info-item">
    <div class="eb-info-icon"><i class="fa-solid fa-chart-pie"></i></div>
    <div>
        <div class="eb-info-label">Email Usage (This Term)</div>
        <div class="eb-info-value" id="eb_email_usage">
            @if($usageLimit > 0)
                {{ number_format($usageUsed) }} used · {{ number_format($usageRemaining) }} remaining of {{ number_format($usageLimit) }}
            @else
                Upgrade your plan to send email broadcasts
            @endif
        </div>
    </div>
</div>

<div class="eb-tip-box">
    <p><i class="fa-solid fa-lightbulb"></i><strong>Delivery details:</strong> From shows as Sent on behalf of your subscriber name via {{ $alertsFrom }}. Replies go to your profile email. One Bcc copy is sent to you with the first batch so you can review the format. Large selections are queued in batches automatically.</p>
</div>
