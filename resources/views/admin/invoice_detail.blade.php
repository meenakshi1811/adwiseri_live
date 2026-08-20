@extends('admin.layout.main')

@section('main-section')

        @php
        $issuerLogo = \App\Support\InvoiceIssuerLogo::resolve($invoice, $u ?? null);
        $subscriberLogoUrl = $issuerLogo['url'];

        $invoiceAmount = (float) $invoice->amount;
        $discountPercent = $invoice->isSubscriptionPackageInvoice() ? 0.0 : (float) $invoice->discount;
        $taxPercent = $invoice->isSubscriptionPackageInvoice() ? 0.0 : (float) $invoice->tax;
        $discountAmount = $invoiceAmount * ($discountPercent / 100);
        $taxableAmount = $invoiceAmount - $discountAmount;
        $taxAmount = $taxableAmount * ($taxPercent / 100);
        $invoiceTotal = $invoice->isSubscriptionPackageInvoice()
            ? (float) $invoice->total
            : ($taxableAmount + $taxAmount);
        $taxLabel = app(\App\Services\InvoiceSnapshotService::class)->resolvedTaxLabel($invoice, $invoiceSetting ?? null);
        @endphp
        <style>
    .invoice-box {
        background: #fff;
        padding: 30px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .invoice-box h3 {
        font-weight: 600;
        border-bottom: 2px solid #695EEE;
        padding-bottom: 10px;
        margin-bottom: 30px;
    }

     .invoice-header {
        display: flex;
        justify-content: flex-start; /* Align items to the left */
        align-items: center;
        margin-bottom: 20px;
    }

    .invoice-header img {
        max-height: 80px;
        object-fit: contain;
        margin-right: 20px; /* Space between logo and text if needed later */
    }

    .invoice-meta p {
        margin: 0;
        line-height: 1.5;
    }

    .table-invoice {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }

    .table-invoice th, .table-invoice td {
        border: 1px solid #ddd;
        padding: 12px 15px;
        text-align: left;
    }

    .table-invoice th {
        background-color: #f7f9fb;
        font-weight: 600;
    }

    .table-invoice .desc-col {
        text-align: left;
    }

    .table-invoice .amount-col {
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .total-row td {
        font-weight: bold;
        background-color: #eef2f6;
    }

    .download-btn {
        background-color: #695EEE;
        color: #fff;
        padding: 8px 20px;
        border: none;
        border-radius: 4px;
        transition: 0.3s ease;
    }

    .download-btn:hover {
        background-color: #564BB0;
        cursor: pointer;
    }

    .note-box {
        background: #f8f9fa;
        padding: 15px;
        margin-top: 30px;
        border-left: 4px solid #695EEE;
        font-size: 0.95rem;
    }

    .text-right {
        text-align: right;
    }
</style>

<div class="col-lg-10 column-client">
    <div class="invoice-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                @if(!empty($subscriberLogoUrl))
                    <img src="{{ $subscriberLogoUrl }}" alt="Logo" style="max-height:70px; object-fit:contain;">
                @else
                    <div class="text-primary fw-bold" style="font-size: 1.35rem;">{{ $invoice->name ?? 'Adwiseri' }}</div>
                @endif
            </div>
            <div class="invoice-page-actions">
                <form method="POST" action="{{ route('admin_resend_invoice_email', $invoice->id) }}" class="d-inline"
                      onsubmit="return confirm('Resend invoice email to {{ $invoice->to_email }}?');">
                    @csrf
                    <button type="submit" class="invoice-btn invoice-btn-outline">
                        <i class="fa-solid fa-paper-plane"></i> Resend Email
                    </button>
                </form>
                <button type="button" class="invoice-btn invoice-btn-primary" onclick="download_invoice({{ $invoice->id }})">
                    <i class="fa-solid fa-download"></i> Download PDF
                </button>
                <a href="{{ route('admin_edit_invoice', $invoice->id) }}" class="invoice-btn invoice-btn-outline">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Invoice
                </a>
            </div>
        </div>

        @include('web.partials.invoice_audit_bar')

        <h3 class="text-primary text-center">Invoice</h3>
        <div class="d-flex justify-content-center align-items-center mb-3">
            <strong>{{ $invoice->name }}</strong>
        </div>

        <!-- <div class="invoice-header mb-4">
           
            <div>
                @if($invoice->subscriber_id)
                    <img src="{{ asset('web_assets/users/user'.$invoice->subscriber_id.'/' . $invoice->logo) }}" alt="Logo">
                @else
                    <img src="{{ asset('web_assets/users/user'.$invoice->user_id.'/' . $invoice->logo) }}" alt="Logo">
                @endif
            </div>
        </div> -->

        @include('partials.invoice_document_styles')
        @include('partials.invoice_document_core', [
            'forPdf' => false,
            'qrSubscriberId' => $invoice->subscriber_id ?: ($issuerLogo['owner_user_id'] ?? ($invoice->user_id ?? 1)),
        ])
    </div>
</div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
  </script>
  <script>
    function download_invoice(id){
        var a = window.open("{{ route('print_invoice_detail', $invoice->id) }}", 'Print Invoice', 'height=700, width=1440');
        // setTimeout(() => {
        //   a.print();
        //   a.window.close();
        // }, 1000);
    }
      $(document).ready(() => {
          $("#country").change(function(){
            var country = $(this).val();
            // console.log(counrty);
            $.ajax({
                url: 'get_states',
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    country: country,
                },
                cache:false,
                success: function(data){
                  console.log(data);
                    $("#state").html(data);
                }
            });
          });
          $("#subscriber").change(function(){
            var id = $(this).val();
            var name = 'subscriber';
            // console.log(counrty);
            $.ajax({
                url: 'get_job_role',
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    id: id,
                    name: name,
                },
                cache:false,
                success: function(data){
                  console.log(data);
                    $("#job_role").html(data);
                }
            });
          });
      });
  </script>
  <script>
      function deleteuser(id){
          var conf = confirm('Are you sure you want to delete this invoice?');
          if(conf == true){
              window.location.href = "delete_user/"+id+"";
          }
      }
  </script>

  @if(session()->has('invoice_updated'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Invoice updated successfully.'
      })
    </script>
  @endif

  @if(session()->has('invoice_email_sent'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Email Sent',
        text: @json(session('invoice_email_sent'))
      })
    </script>
  @endif

  @if(session()->has('invoice_email_failed'))
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Email Failed',
        text: @json(session('invoice_email_failed'))
      })
    </script>
  @endif

  @if(session()->has('deleted'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Invoice deleted successfully.'
      })
    </script>

  @endif

@endsection()
