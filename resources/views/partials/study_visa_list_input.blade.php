@php
    $useSelect = $useSelect ?? false;
    $inputName = $inputName ?? '';
    $inputId = $inputId ?? $inputName;
    $placeholder = $placeholder ?? '';
    $prompt = $prompt ?? '';
    $value = $value ?? '';
    $hint = $hint ?? null;
    $layout = $layout ?? 'row';
@endphp

@if ($useSelect)
    {{-- Options come from the Settings catalogue via the study visa cascade script; the saved value is kept as an option. --}}
    <select name="{{ $inputName }}" id="{{ $inputId }}" data-current="{{ $value }}"
        class="form-control form-select @error($inputName) is-invalid @enderror">
        <option value="">{{ $prompt }}</option>
        @if ($value !== '')
            <option value="{{ $value }}" selected>{{ $value }}</option>
        @endif
    </select>
@else
    <input type="text" name="{{ $inputName }}" id="{{ $inputId }}" maxlength="255"
        class="form-control @error($inputName) is-invalid @enderror"
        value="{{ $value }}" placeholder="{{ $placeholder }}">
@endif
@if ($hint)
    <div class="form-text">{{ $hint }}</div>
@endif
@error($inputName)<span class="invalid-feedback" @if ($layout !== 'modal') role="alert" @endif><strong>{{ $message }}</strong></span>@enderror
