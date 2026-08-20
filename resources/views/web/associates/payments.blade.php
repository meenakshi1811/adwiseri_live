@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-primary text-center flex-grow-1 m-0">Associate Payments</h3>
                <p class="m-0">
                    <a href="{{ route('add_associate_payment') }}">Add Payment Record</a>
                </p>
            </div>

            @include('web.associates._tabs')

            @if(count($payments) != 0)
            @include('partials.table_filter_toolbar', [
                'filterGroups' => [
                    [
                        'key' => 'mode',
                        'label' => 'Payments By Mode',
                        'rowAttribute' => 'data-payment-mode',
                        'items' => $paymentModeFilters ?? [],
                        'totalCount' => count($payments),
                    ],
                    [
                        'key' => 'outstanding',
                        'label' => 'Payments By Outstanding Amount',
                        'rowAttribute' => 'data-outstanding-range',
                        'items' => $paymentOutstandingFilters ?? [],
                        'totalCount' => count($payments),
                    ],
                ],
                'tableId' => 'paymentTable',
            ])
            <div class="table-wrapper">
                <table class="fl-table table table-hover p-0 m-0" id="paymentTable">
                    <thead>
                    <tr>
                        <th class="text-center">Sr No.</th>
                        <th class="text-center">Invoice ID</th>
                        <th class="text-center">Associate</th>
                        <th class="text-center">Client Name (ID)</th>
                        <th class="text-center">Application</th>
                        <th class="text-center">Services</th>
                        <th class="text-center">To Pay / Total</th>
                        <th class="text-center">Paid</th>
                        <th class="text-center">Outstanding</th>
                        <th class="text-center">MOP</th>
                        <th class="text-center">Payment Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($payments as $key => $payment)
                    @php
                        $assoc = $associates[$payment->associate_id] ?? null;
                        $feesDisplay = $payment->fees_display
                            ?? number_format((float) ($payment->invoice_total ?? $payment->fees), 2, '.', '');
                        $outstanding = $payment->outstanding_balance
                            ?? max(0, (float) $payment->fees - (float) $payment->paying);
                        $paymentMode = trim((string) ($payment->payment_mode ?? '')) ?: 'Unspecified';
                        $paymentModeFilterKey = \App\Services\TableFilterCountService::keyFor($paymentMode);
                        $outstandingFilterKey = \App\Services\TableFilterCountService::keyFor(
                            \App\Services\TableFilterCountService::outstandingAmountRangeLabel((float) $outstanding)
                        );
                    @endphp
                    <tr data-payment-mode="{{ $paymentModeFilterKey }}" data-outstanding-range="{{ $outstandingFilterKey }}">
                        <td class="p-1 text-center">{{ $key+1 }}</td>
                        <td class="p-1 text-center">{{ $payment->associate_invoice_id ?: $payment->id }}</td>
                        <td class="p-1 text-center">{{ $assoc ? $assoc->name . ' (' . $assoc->id . ')' : '-' }}</td>
                        <td class="p-1 text-center">{{ $payment->client_name ?: '-' }}@if($payment->client_id) ({{ $payment->client_id }})@endif</td>
                        <td class="p-1 text-center">{{ $payment->application_name ?: '-' }}</td>
                        <td class="p-1 text-center">{{ $payment->services ?: $payment->service_provided }}</td>
                        <td class="p-1 text-center">{{ $feesDisplay }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $payment->paying, 2, '.', '') }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $outstanding, 2, '.', '') }}</td>
                        <td class="p-1 text-center">{{ $payment->payment_mode }}</td>
                        <td class="p-1 text-center">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') : '-' }}</td>
                        <td class="p-1 text-center action-icon">
                            <a href="{{ route('view_associate_payment', $payment->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></a>
                            <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deletePayment({{ $payment->id }})"></i>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-secondary px-3">No Payment Records Added...</p>
            @endif
        </div>
    </div>

</div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
    });
    function deletePayment(id){
        if(confirm('Are you sure you want to delete this payment record? The invoice balance will be adjusted.')){
            window.location.href = "{{ url('delete_associate_payment') }}/"+id;
        }
    }
</script>

@include('web.associates._alerts')

@endsection()
