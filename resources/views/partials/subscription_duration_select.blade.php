@php
    use App\Services\SubscriptionTermPricing;

    $fieldName = $name ?? 'plan_duration';
    $fieldId = $id ?? $fieldName;
    $selectedDuration = SubscriptionTermPricing::normalizeDuration((int) ($selected ?? 1));
    $inputClass = $class ?? 'form-select';
@endphp
{{-- $form (optional): id of the form this select belongs to when it sits outside that form. --}}
<select id="{{ $fieldId }}" name="{{ $fieldName }}" class="{{ $inputClass }}" @if(!empty($form)) form="{{ $form }}" @endif @if(!empty($required)) required @endif>
    @foreach (SubscriptionTermPricing::durationOptions() as $option)
        <option value="{{ $option['value'] }}" {{ $selectedDuration === $option['value'] ? 'selected' : '' }}>
            {{ $option['label'] }}@if($option['hint']) — {{ $option['hint'] }}@endif
        </option>
    @endforeach
</select>
