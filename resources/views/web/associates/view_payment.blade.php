@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <div class="col-12 d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-primary m-0">Payment Record</h3>
            <a href="{{ route('associate_payments') }}" class="btn btn-secondary btn-sm">Back to Payments</a>
        </div>

        <div class="col">
            <table class="table table-bordered">
                <tr><th style="width:35%;">Invoice ID</th><td>{{ $payment->associate_invoice_id ?: $payment->id }}</td></tr>
                <tr><th>Associate</th><td>{{ $associate ? trim($associate->name . ' (' . $associate->id . ')') : $payment->associate_id }}</td></tr>
                <tr><th>Client Name (ID)</th><td>{{ $payment->client_name ?: '-' }}@if($payment->client_id) ({{ $payment->client_id }})@endif</td></tr>
                <tr><th>Linked Application</th><td>{{ $payment->application_name ?: '-' }}</td></tr>
                <tr><th>Services</th><td>{{ $payment->services ?: $payment->service_provided }}</td></tr>
                <tr><th>Fees</th><td>{{ number_format((float) $payment->fees, 2) }}</td></tr>
                <tr><th>Paying</th><td>{{ number_format((float) $payment->paying, 2) }}</td></tr>
                <tr><th>Payment Mode</th><td>{{ $payment->payment_mode }}</td></tr>
                <tr><th>Payment Date</th><td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') : '-' }}</td></tr>
                @if($invoice)
                <tr><th>Invoice Outstanding (current)</th><td>{{ number_format($invoice->outstanding, 2) }}</td></tr>
                @endif
            </table>
        </div>
    </div>
</div>
</div>

@endsection()
