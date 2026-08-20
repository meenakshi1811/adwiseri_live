<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documents Checklist</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
            line-height: 1.5;
            margin: 28px 32px;
        }

        .page-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 0 0 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        .header-table th,
        .header-table td {
            border: 1px solid #111;
            padding: 8px 10px;
            vertical-align: middle;
            text-align: center;
        }

        .header-table th {
            width: 16.66%;
            font-weight: bold;
            background: #f5f5f5;
        }

        .section-block {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .section-title {
            font-weight: bold;
            margin: 0 0 4px;
            text-align: left;
        }

        .section-rule {
            border: 0;
            border-top: 1px solid #111;
            margin: 0 0 10px;
        }

        .doc-item {
            margin: 0 0 14px 0;
            page-break-inside: avoid;
        }

        .doc-label {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .doc-preview-image {
            display: block;
            max-width: 100%;
            max-height: 320px;
            width: auto;
            height: auto;
            border: 1px solid #ccc;
            margin-top: 4px;
        }

        .doc-pdf-note {
            margin-top: 4px;
            font-size: 11px;
            color: #333;
        }

        .format-note {
            margin-top: 28px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page-title">DOCUMENTS CHECKLIST</div>

    <table class="header-table">
        <tr>
            <th>Client</th>
            <th>Country</th>
            <th>Category</th>
            <th>Date</th>
            <th>Time</th>
            <th>Given By</th>
        </tr>
        <tr>
            <td>{{ $client_name }}</td>
            <td>{{ $country }}</td>
            <td>{{ $category ?? $application_name }}</td>
            <td>{{ $date }}</td>
            <td>{{ $time }}</td>
            <td>{{ $given_by }}</td>
        </tr>
    </table>

    @foreach ($sections as $section)
        <div class="section-block">
            <div class="section-title">{{ $section['title'] }}</div>
            <hr class="section-rule">

            @foreach ($section['items'] as $item)
                <div class="doc-item">
                    <div class="doc-label">{{ $item['label'] }}</div>
                    @if(($item['preview_type'] ?? null) === 'image' && !empty($item['preview_src']))
                        <img src="{{ $item['preview_src'] }}" class="doc-preview-image" alt="{{ $item['label'] }}">
                    @elseif(($item['preview_type'] ?? null) === 'pdf' && !empty($item['file_label']))
                        <div class="doc-pdf-note">Uploaded file: {{ $item['file_label'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="format-note">Note :- All Pictures, Certificates, Documents must be clearly visible and in PDF format if possible.</div>
</body>
</html>
