<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Documents - Documents Checklist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f3f4f6;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page-wrap {
            max-width: 820px;
            margin: 32px auto;
            padding: 0 16px 40px;
        }

        .hero-card,
        .upload-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .hero-card {
            padding: 24px;
            margin-bottom: 20px;
        }

        .hero-title {
            color: #695EEE;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .meta-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
        }

        .meta-label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .upload-card {
            padding: 20px;
            margin-bottom: 16px;
        }

        .status-badge {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="hero-card">
            <div class="hero-title">Documents Checklist Upload</div>
            <p class="mb-0">
                Hello <strong>{{ $client_name }}</strong>, please upload the documents requested in your
                <strong>{{ trim($country . ' ' . $category) }}</strong> checklist
                @if($application_ref !== '')
                    ({{ $application_ref }})
                @endif
                for <strong>{{ $subscriber_name }}</strong>.
            </p>

            <div class="meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Client</span>
                    <strong>{{ $client_name }}</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Country</span>
                    <strong>{{ $country }}</strong>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Category</span>
                    <strong>{{ $category }}</strong>
                </div>
                @if($application_ref !== '')
                    <div class="meta-item">
                        <span class="meta-label">Application ID</span>
                        <strong>{{ $application_ref }}</strong>
                    </div>
                @endif
            </div>
        </div>

        @if(session('upload_success'))
            <div class="alert alert-success">{{ session('upload_success') }}</div>
        @endif

        @if(session('upload_error'))
            <div class="alert alert-danger">{{ session('upload_error') }}</div>
        @endif

        @if(empty($checklist_items))
            <div class="upload-card">
                <p class="mb-0">No checklist items are available for this application right now. Please contact {{ $subscriber_name }} if you need assistance.</p>
            </div>
        @else
            @php
                $groupedItems = collect($checklist_items)->groupBy('section');
            @endphp

            @foreach($groupedItems as $section => $items)
                <div class="upload-card">
                    <div class="section-title">{{ $section ?: 'Documents' }}</div>

                    @foreach($items as $item)
                        <div class="border rounded p-3 mb-3 {{ $loop->last ? 'mb-0' : '' }}">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <strong>{{ $item['label'] }}</strong>
                                </div>
                                @if(($item['status'] ?? '') === 'received')
                                    <span class="badge bg-success status-badge">Received</span>
                                @else
                                    <span class="badge bg-warning text-dark status-badge">Pending</span>
                                @endif
                            </div>

                            @if(($item['status'] ?? '') !== 'received')
                                <form method="POST" action="{{ $uploadAction }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                                    @csrf
                                    <input type="hidden" name="doc_folder" value="{{ $section ?: 'Other' }}">
                                    <input type="hidden" name="doc_type" value="{{ $item['label'] }}">
                                    <input type="hidden" name="doc_name" value="{{ $item['label'] }}">
                                    <div class="col-md-8">
                                        <label class="form-label mb-1">Choose file</label>
                                        <input type="file" name="doc_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <div class="form-text">jpg, jpeg, png, or pdf — max 4 MB</div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">Upload</button>
                                    </div>
                                </form>
                            @else
                                <p class="mb-0 text-muted small">This document has already been received. Upload again only if you need to replace it.</p>
                                <form method="POST" action="{{ $uploadAction }}" enctype="multipart/form-data" class="row g-2 align-items-end mt-2">
                                    @csrf
                                    <input type="hidden" name="doc_folder" value="{{ $section ?: 'Other' }}">
                                    <input type="hidden" name="doc_type" value="{{ $item['label'] }}">
                                    <input type="hidden" name="doc_name" value="{{ $item['label'] }}">
                                    <div class="col-md-8">
                                        <input type="file" name="doc_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-outline-primary w-100">Replace</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif

        <p class="text-muted small mb-0">All pictures, certificates, and documents should be clearly visible. PDF format is preferred where possible.</p>
    </div>
</body>
</html>
