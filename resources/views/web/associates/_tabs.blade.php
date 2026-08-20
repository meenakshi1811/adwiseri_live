{{-- Shared tab bar for the Associates module --}}
<div class="row m-0 pb-2 module-tab-row">
    <div class="col-3 border p-1 text-center tab-anchor {{ request()->routeIs('associates') ? 'bg-info text-white' : 'top_modules' }}"
         @if(!request()->routeIs('associates')) onclick="window.location.href='{{ route('associates') }}';" @endif>
        Associates (B2B)
    </div>
    <div class="col-3 border p-1 text-center tab-anchor {{ request()->routeIs('associate_business') ? 'bg-info text-white' : 'top_modules' }}"
         @if(!request()->routeIs('associate_business')) onclick="window.location.href='{{ route('associate_business') }}';" @endif>
        Referrals
    </div>
    <div class="col-3 border p-1 text-center tab-anchor {{ request()->routeIs('associate_invoices') ? 'bg-info text-white' : 'top_modules' }}"
         @if(!request()->routeIs('associate_invoices')) onclick="window.location.href='{{ route('associate_invoices') }}';" @endif>
        Invoices
    </div>
    <div class="col-3 border p-1 text-center tab-anchor {{ request()->routeIs('associate_payments') ? 'bg-info text-white' : 'top_modules' }}"
         @if(!request()->routeIs('associate_payments')) onclick="window.location.href='{{ route('associate_payments') }}';" @endif>
        Payments
    </div>
</div>
