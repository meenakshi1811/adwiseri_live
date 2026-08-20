@extends('web.layout.main')

@section('main-section')
@php

use App\Models\UserRoles;
$client_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Clients')->first();
$application_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Applications')->first();
$communication_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Communication')->first();
$invoice_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Invoices')->first();
$payment_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Payments')->first();
$report_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Reports')->first();
$subscription_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Subscription')->first();
$setting_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Settings')->first();
$support_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Support')->first();
@endphp
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            @include('partials.invoice_ar_ap_module_header', ['activeTab' => 'ap', 'invoice_roles' => $invoice_roles])
            @include('partials.table_filter_toolbar', [
                'filterItems' => $invoiceStatusFilters ?? [],
                'tableId' => 'clientTable',
                'toolbarTitle' => 'Invoices By Status',
                'totalCount' => count($invoices),
            ])
            <div class="table-wrapper">
                <table class="table table-hover table-bordered fl-table" id="clientTable">
                    <thead>
                        <tr>
                            <th class="p-1 text-center">Sr</th>
                            <th class="p-1 text-center">InvoiceID</th>
                            <th class="p-1 text-center">Vendor Name (ID)</th>
                            <th class="p-1 text-center">Product/Service(s) Taken</th>
                            {{-- <th class="p-1 text-center">Phone</th> --}}
                            {{-- <th class="p-1 text-center">Email</th> --}}
                            <th class="p-1 text-center">Amount</th>
                            <th class="p-1 text-center">Discount</th>
                            <th class="p-1 text-center">Tax</th>
                            <th class="p-1 text-center">Total</th>
                            <th class="p-1 text-center">Upload Invoice</th>
                            <th class="p-1 text-center">Status</th>
                            <th class="p-1 text-center">Due Date</th>
                            <th class="p-1 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $key => $invoice)
                            @php
                                $invoiceStatusLabel = \App\Services\TableFilterCountService::invoiceStatusLabel($invoice->status);
                                $invoiceFilterKey = \App\Services\TableFilterCountService::keyFor($invoiceStatusLabel);
                            @endphp
                            <tr data-filter-value="{{ $invoiceFilterKey }}">
                                <td class="p-1 text-center">{{ $key + 1 }}</td>
                                <td class="p-1 text-center">{{ $invoice->invoice_no }}</td>
                                <td  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $invoice->to_name }}"  class="p-1 text-center" style="position: relative;">@if(strlen($invoice->to_name) > 22){{ substr($invoice->to_name, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">
                                {{ $invoice->apVendorDisplay() }}</span>
                                @else
                                {{ $invoice->apVendorDisplay() }}
                                @endif</td>
                                <td class="p-1 text-center">{{ $invoice->detail }}</td>
                                {{-- <td style="position: relative;">@if(strlen($invoice->to_email) > 22){{ substr($invoice->to_email, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{$invoice->to_email}}</span> @else {{$invoice->to_email}} @endif</td> --}}
                                <td class="p-1 text-center" >{{ $invoice->amount }}</td>
                                <td class="p-1 text-center">{{ $invoice->discount }}%</td>
                                <td class="p-1 text-center">{{ $invoice->tax }}%</td>
                                <td class="p-1 text-center">{{ $invoice->total }}</td>
                                <td class="p-1 text-center">
                                    @if(!empty($invoice->uploaded_invoice))
                                        <a href="{{ asset('web_assets/users/' . $invoice->uploaded_invoice) }}" target="_blank" rel="noopener noreferrer">View PDF</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="p-1 text-center">
                                    @if($user->user_type == "Subscriber")
                                    <select class="form-control" id="inv_status{{$invoice->id}}" style="font-size: 14px;">
                                        <option {{($invoice->status == "PartiallyPaid") ? "selected":""}} value="PartiallyPaid">Partially Paid</option>
                                        <option {{($invoice->status == "Paid") ? "selected":""}} value="Paid">Paid</option>
                                        <option {{($invoice->status == "UnPaid") ? "selected":""}} value="UnPaid">Unpaid</option>
                                        <option {{($invoice->status == "Cancelled") ? "selected":""}} value="Cancelled">Cancelled</option>
                                    </select>
                                    @else
                                        {{ $invoice->status }}
                                    @endif
                                </td>
                                <td class="p-1 text-center">{{ $invoice->formatted_due_date }} </td>
                                <td class="p-1 text-center"><a style="background:none; border:none;" @if($invoice_roles->read_only == 1 or $invoice_roles->read_write_only == 1)
                                        href="{{ route('view_invoice', $invoice->id) }}" @else href="#" @endif class="m-0 p-0"><i
                                            class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></a>
                                    @if($invoice_roles->write_only == 1 or $invoice_roles->read_write_only == 1)
                                        <a style="background:none; border:none;" href="{{ route('edit_invoice_ap', $invoice->id) }}" class="m-0 p-0" title="Edit Invoice"><i class="fa-solid fa-pen-to-square p-1 text-primary" style="font-size:14px;"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                    <tbody>
                </table>
            </div>
            {{-- <div class="table-btn">
          <button>Previous</button>
          <button>1</button>
          <button>Next</button>
      </div> --}}
        </div>
    </div>

    </div>

    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
    </script>
    <script>
          document.addEventListener("DOMContentLoaded", function() {
    const headerCells = Array.from(document.querySelectorAll("#clientTable thead th"));
    const uploadHeaderIndex = headerCells.findIndex((th) => (th.textContent || "").trim().toLowerCase() === "upload invoice");
    if (uploadHeaderIndex !== -1) {
        headerCells[uploadHeaderIndex].remove();
        document.querySelectorAll("#clientTable tbody tr").forEach((row) => {
            const rowCells = row.querySelectorAll("td");
            if (rowCells[uploadHeaderIndex]) {
                rowCells[uploadHeaderIndex].remove();
            }
        });
    }
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
        function deleteinvoice(id) {
            var localtime = new Date();
            var conf = confirm('Are you sure you want to delete this invoice?');
            if (conf == true) {
                window.location.href = "delete_invoice/" + id + "/" + localtime.toString() + "";
            }
        }

        @foreach ($invoices as $key => $invoice)
            $("#inv_status{{$invoice->id}}").on("change",function(){
                var local_time = new Date();
                var id = {{$invoice->id}};
                var status = $(this).val();
                var localtime = local_time.toString();
                $.ajax({
                    url : "{{route('invoice_status')}}",
                    method: "POST",
                    data:{
                        "_token" : "{{ csrf_token() }}",
                        "id" : id,
                        "status" : status,
                        "localtime" : localtime,
                    },
                    cache: false,
                    success: function(data){
                        if(data.status == "success"){
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Invoice status updated successfully.'
                            })
                        }
                    }
                })
            });
        @endforeach
    </script>
    @if (session()->has('invoice_updated'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('invoice_updated'))
            })
        </script>
    @endif
    @if (session()->has('user_added'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Invoice created successfully.'
            })
        </script>
    @endif
    @if (session()->has('deleted'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Invoice deleted successfully.'
            })
        </script>
    @endif
    @if (session()->has('noclient'))
        <script>
            Swal.fire({
                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'No clients found.'
            })
        </script>
    @endif
@endsection()
