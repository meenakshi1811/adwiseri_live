@php
    $activeTab = $activeTab ?? 'ar';
    $arTabLabel = 'Invoices (AR)';
    $apTabLabel = 'Invoices (AP)';
    $pageTitle = $activeTab === 'ap' ? 'Invoices (AP)' : 'Invoices (AR)';
    $hasClients = $hasClients ?? (count($clients ?? []) > 0);
    $canWriteInvoices = \App\Support\UserModuleAccess::canWrite($invoice_roles ?? null, $user);
@endphp

<div class="client-btn d-flex justify-content-between align-items-center mt-3 mb-3">
    <form class="form-inline d-flex justify-content-between align-items-center w-100">
        <h3 class="text-primary text-center flex-grow-1 text-center m-0">{{ $pageTitle }}</h3>
        <p class="d-flex gap-2 mb-0 module-header-actions">
            @if($hasClients)
                <a @if($canWriteInvoices) href="{{ route('new_invoice') }}" @else href="#" @endif class="m-0">Add Invoices (AR) Record</a>
            @else
                <a @if($canWriteInvoices) href="javascript:void(0)" onclick="showNoClientAlert(); return false;" @else href="#" @endif class="m-0">Add Invoices (AR) Record</a>
            @endif
            <a @if($canWriteInvoices) href="{{ route('new_invoice_ap') }}" @else href="#" @endif class="m-0">Add Invoices (AP) Record</a>
        </p>
    </form>
</div>
<div class="module-tab-strip invoice-module-tabs d-flex w-100 pb-2">
    <div class="flex-fill border p-1 text-center tab-anchor {{ $activeTab === 'ar' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'ar') onclick="window.location.href = '{{ route('invoices') }}';" @endif>
        {{ $arTabLabel }}
    </div>
    <div class="flex-fill border p-1 text-center tab-anchor {{ $activeTab === 'ap' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'ap') onclick="window.location.href = '{{ route('invoice_payment_made') }}';" @endif>
        {{ $apTabLabel }}
    </div>
</div>
