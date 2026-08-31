{{-- Multi-select service checkbox list.
     Expects: $services (option list), $selectedServices (array of pre-checked values), $otherService (optional custom Other label) --}}
@php
    $selectedServices = $selectedServices ?? [];
    $otherService = old('other_service', $otherService ?? '');
@endphp
<div class="d-flex flex-wrap" style="gap:14px;">
    @foreach($services as $service)
    <label class="m-0" style="min-width:150px;">
        <input type="checkbox"
            name="services[]"
            value="{{ $service }}"
            class="associate-service-checkbox"
            data-service-value="{{ $service }}"
            {{ in_array($service, $selectedServices) ? 'checked' : '' }}> {{ $service }}
    </label>
    @endforeach
</div>
<div id="otherServiceFieldWrap" class="mt-2" style="display:none;">
    <label for="other_service" class="form-label mb-1">Other service name<span class="text-danger">*</span></label>
    <input type="text"
        name="other_service"
        id="other_service"
        maxlength="255"
        class="form-control @error('other_service') is-invalid @enderror"
        value="{{ $otherService }}"
        placeholder="Enter the other service provided">
    @error('other_service')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
</div>
@error('services')<span class="text-danger d-block" style="font-size:13px;"><strong>{{ $message }}</strong></span>@enderror

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var otherWrap = document.getElementById('otherServiceFieldWrap');
        var otherInput = document.getElementById('other_service');
        var checkboxes = document.querySelectorAll('.associate-service-checkbox');

        if (!otherWrap || !otherInput || !checkboxes.length) {
            return;
        }

        function syncOtherServiceField() {
            var otherChecked = false;
            checkboxes.forEach(function (checkbox) {
                if (checkbox.dataset.serviceValue === 'Other' && checkbox.checked) {
                    otherChecked = true;
                }
            });

            otherWrap.style.display = otherChecked ? 'block' : 'none';
            otherInput.required = otherChecked;

            if (!otherChecked) {
                otherInput.value = '';
            }
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', syncOtherServiceField);
        });

        syncOtherServiceField();
    });
</script>
