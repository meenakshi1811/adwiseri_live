@php
    $attachmentItems = \App\Support\InternalMessageAttachments::normalizeList($message->attachments ?? null);
    $subscriberId = (int) ($message->subscriber_id ?? 0);
    $messageId = (int) ($message->id ?? 0);
@endphp

@if(count($attachmentItems) > 0)
    <hr>
    <div class="col-md-4 p-1">
        <label>Attachments</label>
    </div>
    <div class="col-md-8 p-1">
        <ul class="list-unstyled mb-0">
            @foreach($attachmentItems as $attachment)
                <li class="mb-1">
                    <a href="{{ \App\Support\InternalMessageAttachments::publicUrl($subscriberId, $messageId, $attachment['file']) }}"
                        target="_blank" rel="noopener" download="{{ $attachment['name'] }}">
                        <i class="fa-solid fa-paperclip me-1"></i>{{ $attachment['name'] }}
                    </a>
                    @if(!empty($attachment['size']))
                        <span class="text-muted small">({{ number_format($attachment['size'] / 1024, 1) }} KB)</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
