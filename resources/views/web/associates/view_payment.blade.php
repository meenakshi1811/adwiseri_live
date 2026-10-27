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

        @if($invoicePayments->isNotEmpty())
        <div class="col mt-4">
            <h5 class="text-primary mb-3">Payment Entries for Invoice {{ $payment->associate_invoice_id ?: $payment->id }}</h5>
            <div class="table-wrapper">
                <table class="fl-table table table-hover m-0 associate-invoice-payments">
                    <thead>
                    <tr>
                        <th class="text-center">Sr No.</th>
                        <th class="text-center">Payment Date</th>
                        <th class="text-center">Payment Mode</th>
                        <th class="text-center">Paid</th>
                        <th class="text-center">Outstanding After</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invoicePayments as $index => $entry)
                    @php $isCurrentEntry = (int) $entry->id === (int) $payment->id; @endphp
                    <tr @if($isCurrentEntry) class="is-current" aria-current="true" @endif>
                        <td class="p-1 text-center">{{ $index + 1 }}</td>
                        <td class="p-1 text-center">{{ $entry->payment_date ? \Carbon\Carbon::parse($entry->payment_date)->format('d-m-Y') : '-' }}</td>
                        <td class="p-1 text-center">{{ $entry->payment_mode ?: '-' }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $entry->paying, 2) }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $entry->outstanding_after, 2) }}</td>
                        <td class="p-1 text-center action-icon">
                            @if($isCurrentEntry)
                                <span class="badge rounded-pill associate-invoice-payments-viewing">Viewing</span>
                            @else
                                <a href="{{ route('view_associate_payment', $entry->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="View" aria-label="View payment {{ $index + 1 }}"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-center">{{ number_format((float) $invoicePayments->sum('paying'), 2) }}</th>
                        <th class="text-center">{{ number_format((float) $invoicePayments->last()->outstanding_after, 2) }}</th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
</div>

<style>
    .associate-invoice-payments tr.is-current > td {
        --bs-table-accent-bg: #EEEBFF;
        font-weight: 600;
    }

    .associate-invoice-payments-viewing {
        background: #695EEE;
        font-weight: 600;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        [].slice.call(document.querySelectorAll('.associate-invoice-payments [data-bs-toggle="tooltip"]')).forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    });
</script>

@endsection()
