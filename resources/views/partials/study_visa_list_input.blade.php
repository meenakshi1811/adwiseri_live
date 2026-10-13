@php
    $studyVisaOptions = $studyVisaOptions ?? [];
    $optionKey = $optionKey ?? '';
    $inputName = $inputName ?? '';
    $inputId = $inputId ?? ($inputName . '_' . uniqid());
    $listId = $listId ?? ($inputId . '_options');
    $placeholder = $placeholder ?? '';
    $options = $optionKey !== '' ? ($studyVisaOptions[$optionKey] ?? []) : [];
    $value = $value ?? '';
    $errorBag = $errorBag ?? $inputName;
    $layout = $layout ?? 'row';
@endphp

@if ($layout === 'modal')
    @if (count($options) > 0)
        <input type="text" name="{{ $inputName }}" id="{{ $inputId }}" maxlength="255" list="{{ $listId }}"
            class="form-control @error($errorBag) is-invalid @enderror"
            value="{{ $value }}" placeholder="{{ $placeholder }}" autocomplete="off">
        <datalist id="{{ $listId }}">
            @foreach ($options as $option)
                <option value="{{ $option }}"></option>
            @endforeach
        </datalist>
    @else
        <input type="text" name="{{ $inputName }}" id="{{ $inputId }}" maxlength="255"
            class="form-control @error($errorBag) is-invalid @enderror"
            value="{{ $value }}" placeholder="{{ $placeholder }}">
        <div class="form-text">Build this list under Settings → Countries &amp; Categories.</div>
    @endif
    @error($errorBag)<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
@else
    @if (count($options) > 0)
        <input type="text" name="{{ $inputName }}" id="{{ $inputId }}" maxlength="255" list="{{ $listId }}"
            class="form-control @error($errorBag) is-invalid @enderror"
            value="{{ $value }}" placeholder="{{ $placeholder }}" autocomplete="off">
        <datalist id="{{ $listId }}">
            @foreach ($options as $option)
                <option value="{{ $option }}"></option>
            @endforeach
        </datalist>
    @else
        <input type="text" name="{{ $inputName }}" id="{{ $inputId }}" maxlength="255"
            class="form-control @error($errorBag) is-invalid @enderror"
            value="{{ $value }}" placeholder="{{ $placeholder }}">
        <div class="form-text">Build this list under Settings → Countries &amp; Categories.</div>
    @endif
    @error($errorBag)<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
@endif
