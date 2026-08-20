{{-- Multi-select service checkbox list.
     Expects: $services (option list), $selectedServices (array of pre-checked values) --}}
@php $selectedServices = $selectedServices ?? []; @endphp
<div class="d-flex flex-wrap" style="gap:14px;">
    @foreach($services as $service)
    <label class="m-0" style="min-width:150px;">
        <input type="checkbox" name="services[]" value="{{ $service }}"
            {{ in_array($service, $selectedServices) ? 'checked' : '' }}> {{ $service }}
    </label>
    @endforeach
</div>
@error('services')<span class="text-danger d-block" style="font-size:13px;"><strong>{{ $message }}</strong></span>@enderror
