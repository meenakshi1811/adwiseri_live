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
                @include('partials.payment_ar_ap_module_header', ['activeTab' => 'ar', 'clients' => $clients])
                @if(count($paymentAR ) != 0)
                @include('partials.table_filter_toolbar', [
                    'filterItems' => $paymentModeFilters ?? [],
                    'tableId' => 'clientTable',
                    'toolbarTitle' => 'Payments By Mode',
                    'totalCount' => count($paymentAR),
                ])
                  <div class="table-wrapper">
                  <table class="fl-table table table-hover p-0 m-0" id="clientTable">
                    <thead>
                      <tr>
                        <th class="p-1 text-center">Sr No.</th>
                        <th class="p-1 text-center">InvoiceID</th>
                        <th class="p-1 text-center">Client Name (ID)</th>
                        <th class="p-1 text-center">Application (ID)</th>
                        <th class="p-1 text-center">Payment Mode</th>
                        <th class="p-1 text-center">Amount To Pay</th>
                        <th class="p-1 text-center">Paid Amount</th>
                        <th class="p-1 text-center">Outstanding</th>
                        <th class="p-1 text-center">Payment Date</th>
                        <th class="p-1 text-center">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($paymentAR as $key => $payment)
                        @php
                          $paymentMode = trim((string) ($payment->payment_mode ?? '')) ?: 'Unspecified';
                          $paymentFilterKey = \App\Services\TableFilterCountService::keyFor($paymentMode);
                          $applicationDisplay = '';
                          if ($payment->application) {
                              $appName = trim((string) ($payment->application->application_name ?? ''));
                              $appCode = (string) ($payment->application->application_id ?? '');
                              $applicationDisplay = $appName !== '' ? $appName . ' (' . $appCode . ')' : $appCode;
                          } else {
                              $applicationDisplay = $payment->service_description;
                          }
                          $amountToPayDisplay = $payment->amount_to_pay_display
                              ?? number_format((float) ($payment->invoice_total ?? $payment->amount), 2, '.', '');
                          $outstanding = $payment->outstanding_balance ?? max(0, ($payment->amount - $payment->paid_amount));
                          $invoiceGroup = $invoicePaymentGroups[$payment->invoice_group_key] ?? null;
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
                            {{ $payment->client ? $payment->client->name .'('.$payment->client_id.')' : ''}}
                          </td>
                          <td class="p-1 text-center">{{ $applicationDisplay }}</td>
                          <td class="p-1 text-center">{{ $payment->payment_mode }}</td>
                          <td class="p-1 text-center amount">{{ $amountToPayDisplay }}</td>
                          <td class="p-1 text-center paid">{{ number_format((float) $payment->paid_amount, 2, '.', '') }}</td>
                          <td class="p-1 text-center outstanding">{{ number_format((float) $outstanding, 2, '.', '') }}</td>
                          <td class="p-1 text-center">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}
                          </td>
                          <td class="p-1 text-center action-icon">
                            @if($invoiceGroup)
                            <i class="fa-solid fa-eye btn p-1 invoice-payments-btn" role="button" tabindex="0"
                              aria-label="View payment entries of invoice {{ $payment->invoice_no }}"
                              data-bs-toggle="tooltip" data-bs-placement="top" title="View payment entries of this invoice"
                              data-payment-group="{{ $payment->invoice_group_key }}"></i>
                            @endif
                            @if($canSendPaymentReminders)
                              @include('partials.payment_reminder_button', [
                                  'reminderUrl' => route('send_payment_reminder'),
                                  'reminderParams' => ['invoice_no' => $payment->invoice_no],
                                  'invoiceLabel' => $payment->invoice_no,
                                  'recipientLabel' => optional($payment->client)->name,
                                  'outstanding' => $invoiceGroup['outstanding'] ?? number_format((float) $outstanding, 2, '.', ''),
                                  'blockedReason' => $invoiceGroup ? $invoiceGroup['reminder_block'] : 'This payment record has no invoice number.',
                              ])
                            @endif
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

@if(count($paymentAR) != 0)
<style>
  .invoice-payments-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
    gap: .75rem 1rem;
    margin-bottom: 1rem;
  }

  .invoice-payments-summary > div {
    display: flex;
    flex-direction: column;
    gap: .125rem;
    font-size: .9rem;
  }

  .invoice-payments-summary .invoice-payments-label {
    color: #6c757d;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .02em;
  }
</style>

<div class="modal fade" id="invoicePaymentsModal" tabindex="-1" aria-labelledby="invoicePaymentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header py-2" style="background:#695EEE;color:#fff;">
        <h5 class="modal-title fs-6" id="invoicePaymentsModalLabel">Payment Entries</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-3">
        <div class="invoice-payments-summary">
          <div><span class="invoice-payments-label">Invoice ID</span><strong data-field="invoice_no"></strong></div>
          <div><span class="invoice-payments-label">Client Name (ID)</span><strong data-field="client"></strong></div>
          <div><span class="invoice-payments-label">Application (ID)</span><strong data-field="service"></strong></div>
          <div><span class="invoice-payments-label">Invoice Total</span><strong data-field="total"></strong></div>
          <div><span class="invoice-payments-label">Total Paid</span><strong data-field="paid"></strong></div>
          <div>
            <span class="invoice-payments-label">Outstanding</span>
            <span><strong data-field="outstanding"></strong> <span class="badge" data-field="status"></span></span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0" id="invoicePaymentsTable">
            <thead class="table-light">
              <tr>
                <th class="text-center">Sr No.</th>
                <th class="text-center">Payment Date</th>
                <th class="text-center">Payment Mode</th>
                <th class="text-center">Paid Amount</th>
                <th class="text-center">Outstanding After</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
    var invoicePaymentGroups = @json((object) $invoicePaymentGroups);
    var statusBadges = { Cleared: 'bg-success', Outstanding: 'bg-warning text-dark', Cancelled: 'bg-secondary' };

    function showInvoicePayments(trigger) {
        var group = invoicePaymentGroups[trigger.getAttribute('data-payment-group')];
        if (!group) {
            return;
        }

        var tooltip = window.bootstrap && bootstrap.Tooltip ? bootstrap.Tooltip.getInstance(trigger) : null;
        if (tooltip) {
            tooltip.hide();
        }

        var $modal = $('#invoicePaymentsModal');
        $('#invoicePaymentsModalLabel').text('Payment Entries' + (group.invoice_no ? ' - Invoice ' + group.invoice_no : ''));
        ['invoice_no', 'client', 'service', 'total', 'paid', 'outstanding'].forEach(function (field) {
            $modal.find('[data-field="' + field + '"]').text(group[field] || '-');
        });
        $modal.find('[data-field="status"]')
            .attr('class', 'badge ' + (statusBadges[group.status] || 'bg-secondary'))
            .text(group.status);

        var $tbody = $('#invoicePaymentsTable tbody').empty();
        group.entries.forEach(function (entry, index) {
            $('<tr>')
                .append($('<td class="text-center">').text(index + 1))
                .append($('<td class="text-center">').text(entry.date))
                .append($('<td class="text-center">').text(entry.mode))
                .append($('<td class="text-center">').text(entry.paid))
                .append($('<td class="text-center">').text(entry.balance))
                .appendTo($tbody);
        });

        var modalEl = document.getElementById('invoicePaymentsModal');
        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    }

    $(document)
        .on('click', '.invoice-payments-btn', function (event) {
            event.preventDefault();
            showInvoicePayments(this);
        })
        .on('keydown', '.invoice-payments-btn', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                showInvoicePayments(this);
            }
        });

    // Delegated, so rows on every DataTables page get their tooltips.
    var paymentsTable = document.getElementById('clientTable');
    if (paymentsTable && window.bootstrap && bootstrap.Tooltip) {
        new bootstrap.Tooltip(paymentsTable, { selector: '[data-bs-toggle="tooltip"]' });
    }
})();
</script>
@endpush
@endif

<script>
    function deleteinvoice(id){
      var localtime = new Date();
        var conf = confirm('Are you sure you want to delete this invoice?');
        if(conf == true){
            window.location.href = "delete_payment/"+id+"/"+localtime.toString()+"";
        }
    }
</script>
@if(session()->has('payments_received'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: "{{ session('payments_received') }}" // Wrap in double quotes
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
