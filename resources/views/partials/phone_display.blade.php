@php
    $formattedPhone = \App\Support\PhoneNumber::displayWithDialCode($phone ?? null);
@endphp
@if($formattedPhone !== '')
    <span class="phone-display-number">{{ $formattedPhone }}</span>
@else
    <span class="phone-display-empty">{{ $emptyText ?? '—' }}</span>
@endif
