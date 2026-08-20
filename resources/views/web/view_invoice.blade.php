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
@php
if($invoice->user_id == null){
  $userid = 1;
}
else{
  $userid = $invoice->user_id;
}

$issuerLogo = \App\Support\InvoiceIssuerLogo::resolve($invoice, $u ?? null);
$subscriberLogoUrl = $issuerLogo['url'];
$subscriberId = $invoice->subscriber_id ?: ($issuerLogo['owner_user_id'] ?? $userid);

$invoiceAmount = (float) $invoice->amount;
$discountAmount = $invoiceAmount * ((float) $invoice->discount / 100);
$taxableAmount = $invoiceAmount - $discountAmount;
$taxAmount = $invoice->displaysTaxLine()
    ? $taxableAmount * ((float) $invoice->tax / 100)
    : 0;
$invoiceTotal = (float) $invoice->total;
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

    .text-right {
        text-align: right;
    }
</style>

<div class="col-lg-10 column-client">
    <div class="invoice-box">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                @if(!empty($subscriberLogoUrl))
                    <img src="{{ $subscriberLogoUrl }}" alt="Logo" style="max-height:70px; object-fit:contain;">
                @else
                    <div class="text-primary fw-bold" style="font-size: 1.35rem;">{{ $invoice->name ?? 'Adwiseri' }}</div>
                @endif
                @if(!empty($invoice->email) && !\App\Support\BrandedMail::isPlatformBrand($invoice->name ?? 'Adwiseri'))
                    <div>{{ $invoice->email }}</div>
                @endif
            </div>
            <div class="invoice-page-actions">
                @if($invoice->type !== 'ap' && !empty($invoice->to_email))
                    <form method="POST" action="{{ route('resend_invoice_email', $invoice->id) }}" class="d-inline"
                          onsubmit="return confirm('Resend invoice email to {{ $invoice->to_email }}?');">
                        @csrf
                        <button type="submit" class="invoice-btn invoice-btn-outline">
                            <i class="fa-solid fa-paper-plane"></i> Resend Email
                        </button>
                    </form>
                @endif
                <button
                    type="button"
                    class="invoice-btn invoice-btn-primary"
                    @if($invoice_roles->read_only == 1 or $invoice_roles->read_write_only == 1)
                        onclick="download_invoice({{ $invoice->id }})"
                    @endif
                >
                    <i class="fa-solid fa-download"></i> Download PDF
                </button>
                @if($invoice_roles->write_only == 1 or $invoice_roles->read_write_only == 1)
                    <a href="{{ $invoice->type === 'ap' ? route('edit_invoice_ap', $invoice->id) : route('edit_invoice', $invoice->id) }}"
                       class="invoice-btn invoice-btn-outline">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Invoice
                    </a>
                @endif
            </div>
        </div>

        @include('web.partials.invoice_audit_bar')

        <div class="text-center mb-3">
            <h3 class="text-primary mb-0">Invoice</h3>
        </div>

        @include('partials.invoice_document_styles')
        @include('partials.invoice_document_core', [
            'forPdf' => false,
            'qrSubscriberId' => $subscriberId,
            'showFooterThanks' => false,
        ])

        <div style="margin-top: 20px; text-align: center; font-size: 0.9rem; line-height: 1.6;">
            @if($invoice->type === 'ap' && !empty($invoice->uploaded_invoice))
                <div class="mb-3">
                    <strong>Uploaded Invoice:</strong>
                    <a href="{{ asset('web_assets/users/' . $invoice->uploaded_invoice) }}" target="_blank" rel="noopener noreferrer">Open PDF</a>
                </div>
                <div class="mb-3">
                    <iframe src="{{ asset('web_assets/users/' . $invoice->uploaded_invoice) }}" title="Uploaded Invoice PDF" style="width:100%;height:500px;border:1px solid #ddd;"></iframe>
                </div>
            @endif
            @include('partials.invoice_document_footer', [
                'isAdwiseriInvoice' => \App\Support\BrandedMail::isPlatformBrand($invoice->name ?? 'Adwiseri'),
            ])
        </div>

    </div>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        function download_invoice(id) {
            var a = window.open("{{ route('print_invoice', $invoice->id) }}", 'Print Invoice', 'height=700, width=1440');
            // setTimeout(() => {
            //   a.print();
            //   a.window.close();
            // }, 1000);
        }
    </script>
    <script>
        function deleteuser(id) {
            var conf = confirm('Are you sure you want to delete this invoice?');
            if (conf == true) {
                window.location.href = "delete_siteuser/" + id + "";
            }
        }
    </script>
    @if (session()->has('invoice_updated'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('invoice_updated', 'Invoice Updated Successfully !'))
            })
        </script>
    @endif
    @if (session()->has('invoice_email_sent'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Email Sent',
                text: @json(session('invoice_email_sent'))
            })
        </script>
    @endif
    @if (session()->has('invoice_email_failed'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Email Failed',
                text: @json(session('invoice_email_failed'))
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
    @if (session()->has('user_limit'))
        <script>
            Swal.fire({
                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                title: 'User Limit Reached',
                text: 'Upgrade your membership to add more users.'
            })
        </script>
    @endif
@endsection()
