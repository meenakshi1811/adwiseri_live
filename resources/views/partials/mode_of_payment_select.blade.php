@php
    $selected = $selected ?? old('payment_mode', '');
    $fieldId = $fieldId ?? 'payment_mode';
@endphp
<div class="col-md-4 p-1">
    <label for="{{ $fieldId }}">Mode Of Payment <span class="text-danger" style="font-size: 18px;">*</span></label>
</div>
<div class="col-md-8 p-1">
    <select name="payment_mode" id="{{ $fieldId }}" required class="form-control form-select @error('payment_mode') is-invalid @enderror">
        <option value="">Select Mode Of Payment</option>
        @foreach(\App\Models\ClientAccount::PAYMENT_MODES as $mode)
            <option value="{{ $mode }}" {{ (string) $selected === (string) $mode ? 'selected' : '' }}>{{ $mode }}</option>
        @endforeach
    </select>
    @error('payment_mode')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>
