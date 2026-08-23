@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-primary text-center flex-grow-1 m-0">Associate Invoices</h3>
                <p class="m-0">
                    @if($canCreateInvoice ?? true)
                        @if(($hasClients ?? true))
                        <a href="{{ route('create_associate_invoice') }}">Create Invoice</a>
                        @else
                        <a href="javascript:void(0)" onclick="showNoClientAlert(); return false;">Create Invoice</a>
                        @endif
                    @else
                        <a href="#"
                           class="text-muted"
                           style="cursor: not-allowed; opacity: 0.65; text-decoration: none;"
                           onclick="event.preventDefault(); Swal.fire({ icon: 'info', title: 'Notice', text: 'Invoices are created for all associates.' });">
                            Create Invoice
                        </a>
                    @endif
                </p>
            </div>

            @include('web.associates._tabs')

            @if(count($invoices) != 0)
            @include('partials.table_filter_toolbar', [
                'filterItems' => $invoiceStatusFilters ?? [],
                'tableId' => 'invoiceTable',
                'toolbarTitle' => 'Invoices By Status',
                'totalCount' => count($invoices),
            ])
            <div class="table-wrapper">
                <table class="fl-table table table-hover p-0 m-0" id="invoiceTable">
                    <thead>
                    <tr>
                        <th class="text-center">Sr No.</th>
                        <th class="text-center">Invoice ID</th>
                        <th class="text-center">Associate</th>
                        <th class="text-center">Client Name (ID)</th>
                        <th class="text-center">Application</th>
                        <th class="text-center">Services</th>
                        <th class="text-center">Fees</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Due Date</th>
                        <th class="text-center">Paid</th>
                        <th class="text-center">Outstanding</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invoices as $key => $invoice)
                    @php
                        $assoc = $associates[$invoice->associate_id] ?? null;
                        $invoiceStatusLabel = \App\Services\TableFilterCountService::invoiceStatusLabel($invoice->status);
                        $invoiceFilterKey = \App\Services\TableFilterCountService::keyFor($invoiceStatusLabel);
                    @endphp
                    <tr data-filter-value="{{ $invoiceFilterKey }}">
                        <td class="p-1 text-center">{{ $key+1 }}</td>
                        <td class="p-1 text-center">{{ $invoice->id }}</td>
                        <td class="p-1 text-center">{{ $assoc ? $assoc->name . ' (' . $assoc->id . ')' : '-' }}</td>
                        <td class="p-1 text-center">{{ $invoice->client_name ?: '-' }}@if($invoice->client_id) ({{ $invoice->client_id }})@endif</td>
                        <td class="p-1 text-center">{{ $invoice->application_name ?: '-' }}</td>
                        <td class="p-1 text-center">{{ $invoice->services ?: $invoice->service_provided }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $invoice->fees, 2) }}</td>
                        <td class="p-1 text-center">{{ $statusOptions[$invoice->status] ?? $invoice->status }}</td>
                        <td class="p-1 text-center">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') : '-' }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $invoice->paid, 2) }}</td>
                        <td class="p-1 text-center">{{ number_format($invoice->outstanding, 2) }}</td>
                        <td class="p-1 text-center action-icon">
                            <a href="{{ route('view_associate_invoice', $invoice->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></a>
                            <a href="{{ route('edit_associate_invoice', $invoice->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fa-solid fa-pen-to-square btn p-1 text-primary" style="font-size:14px;"></i></a>
                            <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteInvoice({{ $invoice->id }})"></i>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-secondary px-3">No Invoices Created...</p>
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
    function deleteInvoice(id){
        if(confirm('Are you sure you want to delete this invoice? Related payment records will also be removed.')){
            window.location.href = "{{ url('delete_associate_invoice') }}/"+id;
        }
    }
</script>

@include('web.associates._alerts')

@endsection()
