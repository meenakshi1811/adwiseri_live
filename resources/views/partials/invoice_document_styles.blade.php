@once
<style>
    .invoice-doc-section-title {
        font-size: 11px;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: bold;
    }

    .invoice-doc-box {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 10px;
        min-height: 90px;
        line-height: 1.55;
    }

    table.invoice-doc-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        table-layout: fixed;
    }

    table.invoice-doc-items th,
    table.invoice-doc-items td {
        border: 1px solid #d1d5db;
        padding: 8px;
        vertical-align: middle;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    table.invoice-doc-items th {
        background: #eff3ff;
        font-weight: bold;
        text-align: center;
    }

    table.invoice-doc-items td.invoice-doc-col-sr,
    table.invoice-doc-items th.invoice-doc-col-sr {
        text-align: center;
    }

    table.invoice-doc-items th.invoice-doc-col-description {
        text-align: center;
    }

    table.invoice-doc-items td.invoice-doc-col-description {
        text-align: center;
        white-space: pre-line;
    }

    table.invoice-doc-items td.invoice-doc-col-amount,
    table.invoice-doc-items th.invoice-doc-col-amount {
        text-align: center;
    }

    table.invoice-doc-totals {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 0;
    }

    table.invoice-doc-totals td {
        border: 1px solid #d1d5db;
        padding: 8px;
        vertical-align: middle;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    table.invoice-doc-totals td.invoice-doc-totals-label {
        text-align: center;
    }

    table.invoice-doc-totals td.invoice-doc-totals-amount {
        text-align: center;
    }

    table.invoice-doc-totals tr.invoice-doc-grand-total td {
        font-weight: bold;
        background: #eff3ff;
    }

    table.invoice-doc-items tr.invoice-doc-grand-total td {
        font-weight: bold;
        background: #eff3ff;
    }

    .invoice-doc-payment {
        border-top: 1px solid #e5e7eb;
        padding-top: 14px;
    }

    .invoice-doc-footer-thanks {
        margin-top: 24px;
        text-align: center;
        font-size: 12px;
        color: #4b5563;
    }

    .invoice-doc-pdf-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: -50px;
        width: 100%;
        padding: 0;
        box-sizing: border-box;
    }

    .invoice-doc-pdf-footer .invoice-doc-footer-thanks {
        margin-top: 0;
    }
</style>
@endonce
