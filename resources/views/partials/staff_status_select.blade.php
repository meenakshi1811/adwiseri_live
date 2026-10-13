@php
    $staffId = $staffId ?? ($staff->id ?? null);
    $isActive = ($status ?? ($staff->status ?? 'false')) === 'true';
@endphp
<select class="form-select form-select-sm staff-status-select mx-auto"
        style="min-width: 110px; max-width: 140px;"
        data-user-id="{{ $staffId }}"
        data-current="{{ $isActive ? 'true' : 'false' }}"
        aria-label="Staff status">
    <option value="true" @if($isActive) selected @endif>Active</option>
    <option value="false" @if(!$isActive) selected @endif>Inactive</option>
</select>
