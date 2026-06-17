<div class="invoice-audit-bar mb-4">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="audit-card audit-created">
                <div class="audit-icon"><i class="fa-solid fa-user-plus"></i></div>
                <div>
                    <div class="audit-label">Created by</div>
                    <div class="audit-value">{{ $invoice->created_by_name ?? 'Not recorded' }}</div>
                    @if(!empty($invoice->created_at))
                        <div class="audit-meta">{{ $invoice->created_at->format('d M Y, H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="audit-card audit-updated">
                <div class="audit-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                <div>
                    <div class="audit-label">Last updated by</div>
                    <div class="audit-value">{{ $invoice->updated_by_name ?? 'Not yet updated' }}</div>
                    @if(!empty($invoice->updated_by_name) && !empty($invoice->updated_at))
                        <div class="audit-meta">{{ $invoice->updated_at->format('d M Y, H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .invoice-audit-bar .audit-card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        background: linear-gradient(135deg, #f8fbff 0%, #f3f6fa 100%);
        border: 1px solid #e3eaf3;
        border-radius: 10px;
        padding: 16px 18px;
        height: 100%;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .invoice-audit-bar .audit-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .invoice-audit-bar .audit-created .audit-icon {
        background: #e8f4ff;
        color: #0061f2;
    }
    .invoice-audit-bar .audit-updated .audit-icon {
        background: #eef9f1;
        color: #198754;
    }
    .invoice-audit-bar .audit-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .invoice-audit-bar .audit-value {
        font-size: 15px;
        font-weight: 600;
        color: #212529;
    }
    .invoice-audit-bar .audit-meta {
        font-size: 12px;
        color: #868e96;
        margin-top: 4px;
    }
    .invoice-form-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 28px;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
    }

    .invoice-edit-form {
        padding: 0;
        border: none;
        box-shadow: none;
        background: transparent;
    }
    .invoice-form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #edf2f7;
    }
    .invoice-form-header h3 {
        margin: 0;
        font-weight: 600;
    }
    .invoice-ref-badge {
        background: #eef4ff;
        color: #0061f2;
        border: 1px solid #cfe0ff;
        border-radius: 999px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
    }
    .invoice-form-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 0;
        border-top: none;
    }

    .register-box.login-box .invoice-form-actions .invoice-btn,
    .register-box.login-box .invoice-form-actions .invoice-btn:hover,
    .register-box.login-box .invoice-form-actions .invoice-btn:focus {
        text-decoration: none !important;
    }

    .register-box.login-box .invoice-form-actions .invoice-btn-cancel {
        background: #fff !important;
        color: #495057 !important;
        border-color: #d0d7de !important;
    }

    .register-box.login-box .invoice-form-actions .invoice-btn-cancel:hover {
        background: #f6f8fa !important;
        color: #212529 !important;
        border-color: #b6bec7 !important;
    }

    .register-box.login-box .invoice-form-actions .invoice-btn-primary {
        background: #0061f2 !important;
        color: #fff !important;
        border-color: #0061f2 !important;
    }

    .register-box.login-box .invoice-form-actions .invoice-btn-primary:hover {
        background: #0052cc !important;
        color: #fff !important;
        border-color: #0052cc !important;
    }

    /* Override global .column-client a { background: #17CFCF } */
    .column-client .invoice-form-card a.invoice-pdf-view-btn,
    .column-client .invoice-form-card .invoice-form-actions a.invoice-btn,
    .column-client .invoice-box .invoice-page-actions a.invoice-btn {
        margin: 0 !important;
        min-height: 44px;
        padding: 10px 20px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .column-client .invoice-form-card a.invoice-pdf-view-btn {
        background: #fff !important;
        color: #0061f2 !important;
        border: 1px solid #0061f2 !important;
        box-shadow: 0 1px 3px rgba(0, 97, 242, 0.12);
    }

    .column-client .invoice-form-card a.invoice-pdf-view-btn:hover {
        background: #eef4ff !important;
        color: #0052cc !important;
        border-color: #0052cc !important;
    }

    .column-client .invoice-form-card a.invoice-pdf-view-btn i,
    .column-client .invoice-form-card .invoice-upload-current-icon i {
        color: #dc3545;
    }

    .column-client .invoice-form-card .invoice-form-actions a.invoice-btn-cancel,
    .column-client .invoice-box .invoice-page-actions a.invoice-btn-cancel {
        background: #fff !important;
        color: #495057 !important;
        border: 1px solid #d0d7de !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .column-client .invoice-form-card .invoice-form-actions a.invoice-btn-cancel:hover,
    .column-client .invoice-box .invoice-page-actions a.invoice-btn-cancel:hover {
        background: #f6f8fa !important;
        color: #212529 !important;
        border-color: #b6bec7 !important;
    }

    .column-client .invoice-box .invoice-page-actions a.invoice-btn-primary,
    .column-client .invoice-box .invoice-page-actions a.invoice-btn-outline {
        box-shadow: none;
    }

    .column-client .invoice-box .invoice-page-actions a.invoice-btn-outline {
        background: #fff !important;
        color: #0061f2 !important;
        border: 1px solid #0061f2 !important;
    }

    .column-client .invoice-box .invoice-page-actions a.invoice-btn-outline:hover {
        background: #eef4ff !important;
        color: #0052cc !important;
        border-color: #0052cc !important;
    }

    .invoice-upload-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
    }

    .invoice-upload-current {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 14px;
        margin-bottom: 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    .invoice-upload-current-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #f1aeb5;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .invoice-upload-current-icon i {
        font-size: 20px;
        color: #dc3545;
    }

    .invoice-upload-current-body {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .invoice-upload-current-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }

    .invoice-upload-replace-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
    }

    .invoice-file-input {
        background: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: 8px !important;
        padding: 8px 12px !important;
    }

    .invoice-pdf-link,
    a.invoice-pdf-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: #fff;
        border: 1px solid #0061f2;
        border-radius: 8px;
        color: #0061f2 !important;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        box-shadow: 0 1px 3px rgba(0, 97, 242, 0.1);
        transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .invoice-pdf-link:hover,
    a.invoice-pdf-view-btn:hover {
        background: #eef4ff;
        border-color: #0052cc;
        color: #0052cc !important;
        box-shadow: 0 2px 6px rgba(0, 97, 242, 0.15);
    }

    .register-box.login-box .invoice-pdf-link,
    .register-box.login-box .invoice-pdf-link:hover,
    .register-box.login-box a.invoice-pdf-view-btn,
    .register-box.login-box a.invoice-pdf-view-btn:hover {
        text-decoration: none !important;
    }

    .invoice-pdf-link i,
    a.invoice-pdf-view-btn i {
        font-size: 14px;
    }

    .invoice-upload-hint {
        display: block;
        margin-top: 8px;
        font-size: 12px;
        color: #6c757d;
    }

    .invoice-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        min-width: 132px;
        padding: 10px 22px;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none !important;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        white-space: nowrap;
    }

    .invoice-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 97, 242, 0.18);
    }

    .invoice-btn-primary {
        background: #0061f2;
        color: #fff !important;
        border-color: #0061f2;
        box-shadow: 0 2px 8px rgba(0, 97, 242, 0.22);
    }

    .invoice-btn-primary:hover {
        background: #0052cc;
        border-color: #0052cc;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(0, 97, 242, 0.28);
    }

    .invoice-btn-cancel {
        background: #fff;
        color: #495057 !important;
        border-color: #d0d7de;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .invoice-btn-cancel:hover {
        background: #f6f8fa;
        border-color: #b6bec7;
        color: #212529 !important;
    }

    .invoice-btn-outline {
        background: #fff;
        color: #0061f2 !important;
        border-color: #0061f2;
        box-shadow: none;
    }

    .invoice-btn-outline:hover {
        background: #eef4ff;
        border-color: #0052cc;
        color: #0052cc !important;
    }

    .invoice-page-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }
</style>
