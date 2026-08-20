@php
    $activeTab = $activeTab ?? 'clients';
@endphp
<div class="row m-0 pb-2">
    <div class="col-3 border p-1 text-center {{ $activeTab === 'clients' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'clients') onclick="window.location.href = '{{ route('client') }}';" style="cursor:pointer;" @endif>
        Clients
    </div>
    <div class="col-3 border p-1 text-center {{ $activeTab === 'dependents' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'dependents') onclick="window.location.href = '{{ route('subscriber_dependents') }}';" style="cursor:pointer;" @endif>
        Spouse/Dependants
    </div>
    <div class="col-3 border p-1 text-center {{ $activeTab === 'enquiries' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'enquiries') onclick="window.location.href = '{{ route('enquiries') }}';" style="cursor:pointer;" @endif>
        Enquiries (Leads)
    </div>
    <div class="col-3 border p-1 text-center {{ $activeTab === 'accounts' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'accounts') onclick="window.location.href = '{{ route('client_accounts') }}';" style="cursor:pointer;" @endif>
        Accounts
    </div>
</div>
