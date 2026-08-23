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
                @include('partials.payment_ar_ap_module_header', ['activeTab' => 'ap', 'clients' => $clients])

                @if(count($paymentAP) != 0)
                @include('partials.table_filter_toolbar', [
                    'filterItems' => $paymentModeFilters ?? [],
                    'tableId' => 'clientTable',
                    'toolbarTitle' => 'Payments By Mode',
                    'totalCount' => count($paymentAP),
                ])
                  <div class="table-wrapper">
                  <table class="fl-table table table-hover p-0 m-0" id="clientTable">
                    <thead>
                      <tr>
                        <th class="p-1 text-center">Sr No.</th>
                        <th class="p-1 text-center">InvoiceID</th>
                        <th class="p-1 text-center">Vendor Name (ID)</th>
                        <th class="p-1 text-center">Product/Service Taken</th>
                        <th class="p-1 text-center">Payment Mode</th>
                        <th class="p-1 text-center">Amount To Pay</th>
                        <th class="p-1 text-center">Paid Amount</th>
                        <th class="p-1 text-center">Outstanding</th>
                        <th class="p-1 text-center">Payment Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($paymentAP as $key => $payment)
                        @php
                          $paymentMode = trim((string) ($payment->payment_mode ?? '')) ?: 'Unspecified';
                          $paymentFilterKey = \App\Services\TableFilterCountService::keyFor($paymentMode);
                          $amountToPayDisplay = $payment->amount_to_pay_display
                              ?? number_format((float) ($payment->invoice_total ?? $payment->amount), 2, '.', '');
                          $outstanding = $payment->outstanding_balance ?? max(0, ((float) $payment->amount - (float) $payment->paid_amount));
                        @endphp
                        <tr 
                          data-filter-value="{{ $paymentFilterKey }}"
                          data-invoice-no="{{ $payment->invoice_no }}"
                          data-client-id="{{ $payment->client_id }}" 
                          data-application-id="{{ $payment->application_id }}"
                          data-created-at="{{ $payment->created_at->toDateTimeString() }}">
                          <td class="p-1 text-center">{{ $key+1 }}</td>
                          <td class="p-1 text-center">{{ $payment->invoice_no }}</td>
                          <td class="p-1 text-center">
                            @php
                              $vendorId = \App\Models\Internal_Invoices::resolveVendorIdForPayment(
                                  $payment->invoice_no,
                                  $payment->subscriber_id,
                                  $payment->service_provider
                              );
                            @endphp
                            {{ \App\Models\Internal_Invoices::formatVendorDisplay($payment->service_provider, $vendorId) }}
                          </td>
                          <td class="p-1 text-center">
                            {{ $payment->service_taken }}
                          </td>
                          <td class="p-1 text-center">{{ $payment->payment_mode }}</td>
                          <td class="p-1 text-center amount">{{ $amountToPayDisplay }}</td>
                          <td class="p-1 text-center paid">{{ number_format((float) $payment->paid_amount, 2, '.', '') }}</td>
                          <td class="p-1 text-center outstanding">{{ number_format((float) $outstanding, 2, '.', '') }}</td>
                          <td class="p-1 text-center">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                @else
                <p class="text-secondary px-3">No Payment Records to show</p>
                @endif
            </div>
        </div>

    </div>

</div>
<script>
    function deleteinvoice(id){
      var localtime = new Date();
        var conf = confirm('Are you sure you want to delete this invoice?');
        if(conf == true){
            window.location.href = "delete_payment/"+id+"/"+localtime.toString()+"";
        }
    }
</script>

@if(session()->has('advance_payment'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: "{{ session('advance_payment') }}" // Wrap in double quotes
    });
  </script>
@endif
@if(session()->has('user_added'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Payment recorded successfully.'
    })
  </script>

@endif
@if(session()->has('deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Payment deleted successfully.'
    })
  </script>

@endif
@if(session()->has('user_limit'))
  <script>
    Swal.fire({
      icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
      title: 'User Limit Reached',
      text: 'Upgrade your membership to add more users.'
    })
  </script>

@endif
@endsection()
