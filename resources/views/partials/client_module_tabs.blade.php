@php
    $activeTab = $activeTab ?? 'clients';
@endphp
<div class="row m-0 pb-2 module-tab-row client-module-tabs">
    <div class="col-3 border p-1 text-center tab-anchor {{ $activeTab === 'clients' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'clients') onclick="window.location.href = '{{ route('client') }}';" style="cursor:pointer;" @else onclick="adwiseriShowTabMain('{{ route('client') }}')" @endif>
        Clients
    </div>
    <div class="col-3 border p-1 text-center tab-anchor {{ $activeTab === 'dependents' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'dependents') onclick="window.location.href = '{{ route('subscriber_dependents') }}';" style="cursor:pointer;" @else onclick="adwiseriShowTabMain('{{ route('subscriber_dependents') }}')" @endif>
        Spouse/Dependants
    </div>
    <div class="col-3 border p-1 text-center tab-anchor {{ $activeTab === 'enquiries' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'enquiries') onclick="window.location.href = '{{ route('enquiries') }}';" style="cursor:pointer;" @else onclick="adwiseriShowTabMain('{{ route('enquiries') }}')" @endif>
        Enquiries (Leads)
    </div>
    <div class="col-3 border p-1 text-center tab-anchor {{ $activeTab === 'accounts' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'accounts') onclick="window.location.href = '{{ route('client_accounts') }}';" style="cursor:pointer;" @else onclick="adwiseriShowTabMain('{{ route('client_accounts') }}')" @endif>
        Accounts
    </div>
</div>
