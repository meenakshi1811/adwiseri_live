@php
    $activeTab = $activeTab ?? 'ar';
    $arTabLabel = 'Payments (AR - Accounts Receivable)';
    $apTabLabel = 'Payments (AP - Accounts Payable)';
    $pageTitle = $activeTab === 'ap' ? 'Payments (AP)' : 'Payments (AR)';
    $hasClients = $hasClients ?? (count($clients ?? []) > 0);
@endphp

<div class="client-btn d-flex justify-content-between align-items-center mt-3 mb-3">
    <form class="form-inline d-flex justify-content-between align-items-center w-100">
        <h3 class="text-primary text-center flex-grow-1 text-center m-0">{{ $pageTitle }}</h3>
        @if(!$user->is_support)
        <p class="d-flex gap-2 mb-0 module-header-actions">
            @if($hasClients)
                <a href="{{ route('add_ar_payments') }}" class="m-0">Add Payments (AR) Record</a>
                <a href="{{ route('add_ap_payments') }}" class="m-0">Add Payments (AP) Record</a>
            @else
                <a href="javascript:void(0)" onclick="showNoClientAlert(); return false;" class="m-0">Add Payments (AR) Record</a>
                <a href="javascript:void(0)" onclick="showNoClientAlert(); return false;" class="m-0">Add Payments (AP) Record</a>
            @endif
            <button type="button"
                class="m-0 tax-data-header-btn"
                data-bs-toggle="modal"
                data-bs-target="#taxDataModal"
                onclick="if(typeof initTaxDataModal === 'function'){ initTaxDataModal(); }">
                Tax Summary
            </button>
        </p>
        @endif
    </form>
</div>
<div class="module-tab-strip payment-module-tabs d-flex w-100 pb-2">
    <div class="flex-fill border p-1 text-center tab-anchor {{ $activeTab === 'ar' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'ar') onclick="window.location.href = '{{ route('my_payments') }}';" @endif>
        {{ $arTabLabel }}
    </div>
    <div class="flex-fill border p-1 text-center tab-anchor {{ $activeTab === 'ap' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'ap') onclick="window.location.href = '{{ route('payment_made') }}';" @endif>
        {{ $apTabLabel }}
    </div>
</div>

@include('partials.tax_data_modal', [
    'taxDataUrl' => route('payments_tax_data'),
])
