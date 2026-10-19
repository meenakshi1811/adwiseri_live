@php
    $inputName = $inputName ?? '';
    $inputId = $inputId ?? $inputName;
    $prompt = $prompt ?? '';
    $value = $value ?? '';
    $hint = $hint ?? null;
    $layout = $layout ?? 'row';
@endphp

{{-- Options come from the Settings catalogue via the study visa cascade script; the saved value is kept as an option. --}}
<select name="{{ $inputName }}" id="{{ $inputId }}" data-current="{{ $value }}" data-prompt="{{ $prompt }}"
    class="form-control form-select @error($inputName) is-invalid @enderror">
    <option value="">{{ $prompt }}</option>
    @if ($value !== '')
        <option value="{{ $value }}" selected>{{ $value }}</option>
    @endif
</select>
@if ($hint)
    <div class="form-text">{{ $hint }}</div>
@endif
@error($inputName)<span class="invalid-feedback" @if ($layout !== 'modal') role="alert" @endif><strong>{{ $message }}</strong></span>@enderror
