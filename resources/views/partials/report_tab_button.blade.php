@php
    $moduleKey = $module ?? '';
    $hasData = ($reportModuleAvailability[$moduleKey] ?? false) === true;
    $isActive = ($active ?? false) === true;
    $tabId = $id ?? '';
    $tabTarget = $target ?? '';
    $tabLabel = $label ?? '';
    $clickHandler = $onclick ?? '';
@endphp
<li class="nav-item" role="presentation">
    @if($hasData)
        <button class="nav-link {{ $isActive ? 'active' : '' }}"
            onclick="{{ $clickHandler }}"
            id="{{ $tabId }}"
            data-report-module="{{ $moduleKey }}"
            data-bs-toggle="tab"
            data-bs-target="{{ $tabTarget }}"
            type="button"
            role="tab"
            aria-controls="{{ ltrim($tabTarget, '#') }}"
            aria-selected="{{ $isActive ? 'true' : 'false' }}">{{ $tabLabel }}</button>
    @else
        <button class="nav-link report-tab-disabled {{ $isActive ? 'active' : '' }}"
            type="button"
            role="tab"
            id="{{ $tabId }}"
            data-report-module="{{ $moduleKey }}"
            data-report-disabled="1"
            aria-disabled="true">{{ $tabLabel }}</button>
    @endif
</li>
