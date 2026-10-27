{{-- Invoice form/page buttons (.invoice-btn). Used by the Add and Edit invoice forms and invoice pages. --}}
@once
<style>
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
        background: #695EEE !important;
        color: #fff !important;
        border-color: #695EEE !important;
    }

    .register-box.login-box .invoice-form-actions .invoice-btn-primary:hover {
        background: #695EEE !important;
        color: #fff !important;
        border-color: #695EEE !important;
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
        background: #695EEE;
        color: #fff !important;
        border-color: #695EEE;
        box-shadow: 0 2px 8px rgba(0, 97, 242, 0.22);
    }

    .invoice-btn-primary:hover {
        background: #695EEE;
        border-color: #695EEE;
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
        color: #695EEE !important;
        border-color: #695EEE;
        box-shadow: none;
    }

    .invoice-btn-outline:hover {
        background: #eef4ff;
        border-color: #695EEE;
        color: #695EEE !important;
    }
</style>
@endonce
