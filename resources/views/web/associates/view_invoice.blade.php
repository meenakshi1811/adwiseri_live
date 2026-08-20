@extends('web.layout.main')

@section('main-section')
@php
    $data = $document;
    $subscriberLogoUrl = $document->logo_url ?? null;
    $companyName = $document->company_name ?? 'Adwiseri';
    $associateEmail = trim((string) ($associate->email ?? ''));
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

    .invoice-page-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .invoice-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 0.9rem;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .invoice-btn-primary {
        background-color: #695EEE;
        color: #fff;
    }

    .invoice-btn-primary:hover {
        background-color: #564BB0;
        color: #fff;
    }

    .invoice-btn-outline {
        background: #fff;
        color: #695EEE;
        border-color: #695EEE;
    }

    .invoice-btn-outline:hover {
        background: #f5f3ff;
        color: #564BB0;
    }
</style>

<div class="col-lg-10 column-client">
    <div class="invoice-box">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                @if(!empty($subscriberLogoUrl))
                    <img src="{{ $subscriberLogoUrl }}" alt="Logo" style="max-height:70px; object-fit:contain;">
                @else
                    <div class="text-primary fw-bold" style="font-size: 1.35rem;">{{ $companyName }}</div>
                @endif
                @if(!empty($document->email) && !($document->is_adwiseri ?? false))
                    <div>{{ $document->email }}</div>
                @endif
            </div>
            <div class="invoice-page-actions">
                @if($associateEmail !== '')
                    <form method="POST" action="{{ route('resend_associate_invoice_email', $invoice->id) }}" class="d-inline"
                          onsubmit="return confirm('Send invoice email to {{ $associateEmail }}?');">
                        @csrf
                        <button type="submit" class="invoice-btn invoice-btn-outline">
                            <i class="fa-solid fa-paper-plane"></i> Send Email
                        </button>
                    </form>
                @endif
                <button type="button" class="invoice-btn invoice-btn-primary"
                    onclick="window.open('{{ route('print_associate_invoice', $invoice->id) }}', 'Print Invoice', 'height=700,width=1440');">
                    <i class="fa-solid fa-download"></i> Download PDF
                </button>
                <a href="{{ route('edit_associate_invoice', $invoice->id) }}" class="invoice-btn invoice-btn-outline">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Invoice
                </a>
                <a href="{{ route('associate_invoices') }}" class="invoice-btn invoice-btn-outline">
                    Back to Invoices
                </a>
            </div>
        </div>

        <div class="text-center mb-3">
            <h3 class="text-primary mb-0">Invoice</h3>
        </div>

        @include('partials.invoice_document_styles')
        @include('partials.invoice_document_core', [
            'data' => $document,
            'forPdf' => false,
            'qrSubscriberId' => $subscriber->id,
            'showFooterThanks' => false,
            'isAdwiseriInvoice' => $document->is_adwiseri ?? false,
        ])

        <div style="margin-top: 20px; text-align: center; font-size: 0.9rem; line-height: 1.6;">
            @include('partials.invoice_document_footer', [
                'isAdwiseriInvoice' => $document->is_adwiseri ?? false,
            ])
        </div>

        <div class="mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
            <div class="row g-2 mb-3 text-start">
                <div class="col-md-4"><strong>Paid:</strong> {{ number_format((float) $invoice->paid, 2) }}</div>
                <div class="col-md-4"><strong>Outstanding:</strong> {{ number_format($invoice->outstanding, 2) }}</div>
                <div class="col-md-4">
                    <strong>Associate:</strong>
                    {{ $associate ? $associate->name : '-' }}
                </div>
            </div>

            <h5 class="text-primary text-start">Payment Records</h5>
            @if(count($payments) != 0)
            <table class="fl-table table table-hover mb-0">
                <thead>
                <tr>
                    <th class="text-center">Sr No.</th>
                    <th class="text-center">Paid</th>
                    <th class="text-center">MOP</th>
                    <th class="text-center">Payment Date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payments as $key => $payment)
                <tr>
                    <td class="p-1 text-center">{{ $key+1 }}</td>
                    <td class="p-1 text-center">{{ number_format((float) $payment->paying, 2) }}</td>
                    <td class="p-1 text-center">{{ $payment->payment_mode }}</td>
                    <td class="p-1 text-center">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') : '-' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <p class="text-secondary text-start mb-0">No payments recorded yet.</p>
            @endif
        </div>
    </div>
</div>
</div>
</div>

@if (session()->has('invoice_email_sent'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Email Sent',
            text: @json(session('invoice_email_sent'))
        })
    </script>
@endif
@if (session()->has('invoice_email_error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Email Failed',
            text: @json(session('invoice_email_error'))
        })
    </script>
@endif

@endsection()
