<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_no }}</title>
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
        $logoPath = null;
        if (!empty($document->logo_path)) {
            $candidate = str_starts_with((string) $document->logo_path, DIRECTORY_SEPARATOR)
                || preg_match('#^[A-Za-z]:\\\\#', (string) $document->logo_path)
                ? $document->logo_path
                : public_path($document->logo_path);
            if (file_exists($candidate)) {
                $logoPath = $candidate;
            }
        }
        $hasSubscriberLogo = !empty($logoPath);
        $companyName = $document->company_name ?? 'Adwiseri';
    @endphp

    <div class="sheet">
        <table class="header">
            <tr>
                <td>
                    @if($hasSubscriberLogo)
                        <img class="logo" src="{{ $logoPath }}" alt="Logo">
                    @else
                        <div class="company">{{ $companyName }}</div>
                    @endif
                    @if(!empty($document->email) && !($document->is_adwiseri ?? false))
                        <div>{{ $document->email }}</div>
                    @endif
                </td>
                <td class="title">INVOICE</td>
            </tr>
        </table>

        @include('partials.invoice_document_core', [
            'data' => $document,
            'forPdf' => true,
            'qrSubscriberId' => $subscriber->id,
            'showFooterThanks' => false,
            'isAdwiseriInvoice' => $document->is_adwiseri ?? false,
        ])

        <div style="margin-top: 24px;">
            @include('partials.invoice_document_footer', [
                'isAdwiseriInvoice' => $document->is_adwiseri ?? false,
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
