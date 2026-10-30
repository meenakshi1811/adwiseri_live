{{-- One person in an Email Broadcast picker. Without an email they can't be picked and aren't counted.
     $class: the list's checkbox class, $value, $email, $label, optional $note (shown muted) and $groups (team keys). --}}
@php $optionEmail = strtolower(trim((string) $email)); @endphp
<label class="eb-recipient-option{{ $optionEmail === '' ? ' is-unavailable' : '' }}" data-search="{{ strtolower($label . ' ' . ($note ?? '')) }}">
    <input type="checkbox" class="{{ $class }} eb-person{{ $optionEmail === '' ? ' eb-unavailable' : '' }}" name="recipients[]" value="{{ $value }}" data-email="{{ $optionEmail }}" data-groups="{{ $groups ?? '' }}" @if($optionEmail === '') disabled @endif>
    <span>{{ $label }}@if(trim((string) ($note ?? '')) !== '') <span class="text-muted">({{ $note }})</span>@endif</span>
    @if($optionEmail === '')
    <span class="eb-recipient-note">No email</span>
    @endif
</label>
