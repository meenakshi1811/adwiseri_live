<style>
    .eb-page-title {
        font-size: 1.65rem;
        font-weight: 700;
        color: #695EEE;
        letter-spacing: -0.02em;
    }

    .eb-page-subtitle {
        color: #64748b;
        font-size: 0.92rem;
        margin-top: 0.25rem;
    }

    .eb-card {
        background: #fff;
        border: 1px solid #e8eaf3;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        height: 100%;
    }

    .eb-card-header {
        background: linear-gradient(135deg, #695EEE 0%, #7c6ff0 100%);
        color: #fff;
        padding: 1rem 1.35rem;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .eb-card-body {
        padding: 1.35rem;
    }

    .eb-form-group {
        margin-bottom: 1.25rem;
    }

    .eb-form-label {
        display: block;
        font-weight: 600;
        font-size: 0.88rem;
        color: #334155;
        margin-bottom: 0.45rem;
    }

    .eb-form-label .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .eb-form-hint {
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 0.35rem;
    }

    .eb-type-toggle {
        display: inline-flex;
        background: #f1f5f9;
        border-radius: 10px;
        padding: 4px;
        gap: 4px;
        width: 100%;
        max-width: 320px;
    }

    .eb-type-btn {
        flex: 1;
        border: none;
        background: transparent;
        color: #64748b;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.55rem 0.75rem;
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .eb-type-btn.active {
        background: #fff;
        color: #695EEE;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    .eb-type-btn i {
        margin-right: 5px;
    }

    .eb-recipient-wrap {
        position: relative;
    }

    .eb-recipient-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        min-height: 42px;
        padding: 0.55rem 0.85rem;
        border: 1px solid #dbe1ea;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .eb-recipient-trigger:hover,
    .eb-recipient-trigger.show {
        border-color: #695EEE;
        box-shadow: 0 0 0 3px rgba(105, 94, 238, 0.12);
    }

    .eb-recipient-trigger-text {
        color: #64748b;
        font-size: 0.9rem;
    }

    .eb-recipient-trigger.has-selection .eb-recipient-trigger-text {
        color: #1e293b;
        font-weight: 600;
    }

    .eb-recipient-badge {
        background: #695EEE;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        margin-left: 0.5rem;
        display: none;
    }

    .eb-recipient-trigger.has-selection .eb-recipient-badge {
        display: inline-block;
    }

    .eb-recipient-panel {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
        max-height: 320px;
        overflow: hidden;
    }

    .eb-recipient-panel.open {
        display: block;
    }

    .eb-recipient-search-wrap {
        padding: 0.75rem;
        border-bottom: 1px solid #eef2f7;
        background: #fafbfc;
    }

    .eb-recipient-search {
        width: 100%;
        border: 1px solid #dbe1ea;
        border-radius: 8px;
        padding: 0.45rem 0.65rem 0.45rem 2rem;
        font-size: 0.85rem;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242 1.106a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E") no-repeat 0.65rem center;
    }

    .eb-recipient-search:focus {
        outline: none;
        border-color: #695EEE;
        box-shadow: 0 0 0 3px rgba(105, 94, 238, 0.12);
    }

    .eb-recipient-list {
        max-height: 240px;
        overflow-y: auto;
        padding: 0.35rem 0;
    }

    .eb-recipient-group-title {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #94a3b8;
        padding: 0.55rem 0.85rem 0.25rem;
    }

    .eb-recipient-option {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 0.85rem;
        cursor: pointer;
        transition: background 0.15s ease;
        font-size: 0.88rem;
        color: #334155;
    }

    .eb-recipient-option:hover {
        background: #f8fafc;
    }

    .eb-recipient-option input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #695EEE;
        cursor: pointer;
        flex-shrink: 0;
    }

    .eb-recipient-option.hidden-by-search {
        display: none;
    }

    .eb-input,
    .eb-select,
    .eb-textarea {
        width: 100%;
        border: 1px solid #dbe1ea;
        border-radius: 10px;
        padding: 0.6rem 0.85rem;
        font-size: 0.9rem;
        color: #1e293b;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .eb-input:focus,
    .eb-select:focus,
    .eb-textarea:focus {
        outline: none;
        border-color: #695EEE;
        box-shadow: 0 0 0 3px rgba(105, 94, 238, 0.12);
    }

    .eb-textarea {
        min-height: 220px;
        resize: vertical;
        line-height: 1.55;
    }

    .ck-editor__editable {
        min-height: 220px;
    }

    .eb-input-readonly {
        background: #f8fafc;
        color: #475569;
        cursor: not-allowed;
    }

    .eb-char-count {
        text-align: right;
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 0.35rem;
    }

    .eb-info-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.85rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .eb-info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .eb-info-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #eef0ff;
        color: #695EEE;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .eb-info-label {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.15rem;
    }

    .eb-info-value {
        font-size: 0.88rem;
        color: #1e293b;
        font-weight: 600;
        word-break: break-all;
    }

    .eb-tip-box {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        border: 1px solid #e0e7ff;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .eb-tip-box p {
        margin: 0;
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.5;
    }

    .eb-delivery-divider {
        border-top: 1px solid #e8eaf3;
        margin: 1rem 0;
    }

    .eb-tip-box i {
        color: #695EEE;
        margin-right: 6px;
    }

    .eb-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding-top: 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px solid #f1f5f9;
    }

    .eb-send-btn {
        background: linear-gradient(135deg, #695EEE 0%, #7c6ff0 100%);
        border: none;
        color: #fff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 0.7rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(105, 94, 238, 0.28);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .eb-send-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(105, 94, 238, 0.35);
        color: #fff;
    }

    .eb-send-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .eb-permission-alert {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        font-size: 0.88rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .eb-error-text {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.35rem;
        display: block;
    }

    @media (max-width: 575px) {
        .eb-type-toggle {
            max-width: 100%;
        }
    }
</style>
