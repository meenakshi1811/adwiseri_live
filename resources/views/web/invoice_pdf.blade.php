<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @include('partials.invoice_document_styles')
    <style>
        @page {
            margin: 24px 28px 70px 28px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
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

        .company {
            font-size: 22px;
            font-weight: bold;
            color: #1f4bb8;
        }

        .title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }

        .logo {
            max-height: 55px;
            max-width: 200px;
            margin-bottom: 6px;
        }
    </style>
</head>

<body>
    @php
        $amount = (float) ($data->amount ?? 0);
        $subscriberName = trim((string) ($data->company_name ?? $data->subscriber_name ?? $data->from_name ?? 'Adwiseri'));
        $subscriberName = preg_replace('/^Sent on behalf of\s+/i', '', $subscriberName) ?: 'Adwiseri';
        $subscriberEmail = trim((string) ($data->subscriber_email ?? $data->display_from_email ?? ''));
        $subscriberLogoCandidates = [];

        if (!empty($data->logo_path)) {
            if (str_starts_with((string) $data->logo_path, DIRECTORY_SEPARATOR)
                || preg_match('#^[A-Za-z]:\\\\#', (string) $data->logo_path)) {
                $subscriberLogoCandidates[] = $data->logo_path;
            } else {
                $subscriberLogoCandidates[] = public_path($data->logo_path);
            }
        }

        if (!empty($data->logo)) {
            foreach (array_filter([$data->subscriber_id ?? null, $data->user_id ?? null, $data->added_by ?? null]) as $logoUserId) {
                $subscriberLogoCandidates[] = public_path('web_assets/users/user' . $logoUserId . '/' . $data->logo);
            }

            $subscriberLogoCandidates[] = public_path('web_assets/users/logos/' . $data->logo);
        }

        $fallbackLogoCandidates = [
            public_path('web_assets/images/Style2_blue.png'),
            public_path('web_assets/images/Style2.png'),
            public_path('web_assets/images/default_logo.png'),
        ];
        $logoPath = null;
        $hasSubscriberLogo = false;

        foreach (array_unique($subscriberLogoCandidates) as $logoCandidate) {
            if (!empty($logoCandidate) && file_exists($logoCandidate)) {
                $logoPath = $logoCandidate;
                $hasSubscriberLogo = true;
                break;
            }
        }

        if (empty($logoPath)) {
            foreach ($fallbackLogoCandidates as $logoCandidate) {
                if (!empty($logoCandidate) && file_exists($logoCandidate)) {
                    $logoPath = $logoCandidate;
                    break;
                }
            }
        }

        $forPdf = true;
        $showCompanyName = empty($logoPath);
        $isAdwiseriInvoice = \App\Support\BrandedMail::isPlatformBrand($subscriberName);
    @endphp

    <table class="header">
        <tr>
            <td>
                @if(!empty($logoPath))
                    <img class="logo" src="{{ $logoPath }}" alt="Logo">
                @endif
                @if($showCompanyName)
                    <div class="company">{{ $subscriberName }}</div>
                @endif
                @if(!$isAdwiseriInvoice && !empty($subscriberEmail))
                    <div>{{ $subscriberEmail }}</div>
                @endif
            </td>
            <td class="title">
                INVOICE
            </td>
        </tr>
    </table>

    @include('partials.invoice_document_core', [
        'forPdf' => true,
        'isAdwiseriInvoice' => $isAdwiseriInvoice,
        'showFooterThanks' => false,
    ])

    <div class="invoice-doc-pdf-footer">
        @include('partials.invoice_document_footer', ['isAdwiseriInvoice' => $isAdwiseriInvoice])
    </div>
</body>

</html>
