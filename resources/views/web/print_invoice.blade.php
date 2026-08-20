<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice</title>
    @include('partials.invoice_document_styles')
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            padding: 20px;
        }

        .sheet {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #1f4bb8;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .header td {
            vertical-align: middle;
        }

        .title {
            text-align: right;
            font-size: 26px;
            font-weight: 700;
            color: #1f4bb8;
        }

        .logo {
            max-height: 55px;
            max-width: 200px;
            margin-bottom: 6px;
        }

        .company {
            font-size: 22px;
            font-weight: 700;
            color: #1f4bb8;
        }
    </style>
</head>

<body>
    @php
        $issuerLogo = \App\Support\InvoiceIssuerLogo::resolve($invoice, $u ?? null);
        $logoPath = $issuerLogo['disk_path'];
        $hasSubscriberLogo = !empty($logoPath);
        $qrSubscriberId = $invoice->subscriber_id ?: ($issuerLogo['owner_user_id'] ?? ($invoice->user_id ?? 1));
        $forPdf = false;
    @endphp

    <div class="sheet">
        <table class="header">
            <tr>
                <td>
                    @if(!empty($logoPath) && $hasSubscriberLogo)
                        <img class="logo" src="{{ $logoPath }}" alt="Logo">
                    @endif
                    @if(empty($hasSubscriberLogo))
                        <div class="company">{{ $invoice->name ?? 'Adwiseri' }}</div>
                    @endif
                    @if(!empty($invoice->email) && !\App\Support\BrandedMail::isPlatformBrand($invoice->name ?? 'Adwiseri'))
                        <div>{{ $invoice->email }}</div>
                    @endif
                </td>
                <td class="title">INVOICE</td>
            </tr>
        </table>

        @include('partials.invoice_document_core', [
            'forPdf' => false,
            'qrSubscriberId' => $qrSubscriberId,
            'showFooterThanks' => false,
            'isAdwiseriInvoice' => \App\Support\BrandedMail::isPlatformBrand($invoice->name ?? 'Adwiseri'),
        ])

        <div style="margin-top: 24px;">
            @include('partials.invoice_document_footer', [
                'isAdwiseriInvoice' => \App\Support\BrandedMail::isPlatformBrand($invoice->name ?? 'Adwiseri'),
            ])
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            }
        });
    </script>
</body>

</html>
