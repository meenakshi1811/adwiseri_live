@extends('web.layout.main')

@section('main-section')
@php
    $associate = $business->associate;
@endphp

    <div class="col-lg-10 column-client">
        <div class="col-12 d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-primary m-0">Business (Referral) Entry</h3>
            <a href="{{ route('associate_business') }}" class="btn btn-secondary btn-sm">Back to Business (Referrals)</a>
        </div>

        <div class="col">
            <table class="table table-bordered">
                <tr><th style="width:35%;">Associate</th><td>{{ $associate ? trim($associate->name . ' (' . $associate->id . ')') : '-' }}</td></tr>
                <tr><th>Client Name (ID)</th><td>{{ $business->client_name ?: '-' }}@if($business->client_id) ({{ $business->client_id }})@endif</td></tr>
                <tr><th>Application</th><td>{{ $business->application_name ?: '-' }}</td></tr>
                <tr><th>Services</th><td>{{ $business->formattedServices() }}</td></tr>
                <tr><th>Fees</th><td>{{ number_format((float) $business->fees, 2) }}</td></tr>
                <tr><th>Application Status</th><td>{{ $business->application_status ?: '-' }}</td></tr>
                <tr><th>Home Country</th><td>{{ $business->home_country ?: '-' }}</td></tr>
                <tr><th>Visa Country</th><td>{{ $business->visa_country ?: '-' }}</td></tr>
                <tr><th>Application Type</th><td>{{ $business->application_type ?: '-' }}</td></tr>
                <tr><th>Created Date</th><td>{{ $business->created_at ? \Carbon\Carbon::parse($business->created_at)->format('d-m-Y') : '-' }}</td></tr>
            </table>
        </div>
    </div>
</div>
</div>

@endsection()
