@php
    use App\Services\SubscriptionTermPricing;

    $fieldName = $name ?? 'plan_duration';
    $fieldId = $id ?? $fieldName;
    $selectedDuration = SubscriptionTermPricing::normalizeDuration((int) ($selected ?? 1));
    $inputClass = $class ?? 'form-select';
@endphp
<select id="{{ $fieldId }}" name="{{ $fieldName }}" class="{{ $inputClass }}" @if(!empty($required)) required @endif>
    @foreach (SubscriptionTermPricing::durationOptions() as $option)
        <option value="{{ $option['value'] }}" @selected($selectedDuration === $option['value'])>
            {{ $option['label'] }}@if($option['hint']) — {{ $option['hint'] }}@endif
        </option>
    @endforeach
</select>
