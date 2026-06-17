<style>
    .comm-nav-wrap {
        background: #f8f9fc;
        border: 1px solid #e8eaf3;
        border-radius: 14px;
        padding: 6px;
        margin-bottom: 1.25rem;
    }

    .comm-nav-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0;
    }

    .comm-nav-tab {
        flex: 1 1 calc(25% - 6px);
        min-width: 120px;
        text-align: center;
        padding: 11px 10px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.82rem;
        color: #64748b;
        background: transparent;
        border: none;
        transition: all 0.2s ease;
        line-height: 1.3;
    }

    .comm-nav-tab i {
        display: block;
        font-size: 1rem;
        margin-bottom: 4px;
        opacity: 0.85;
    }

    .comm-nav-tab:hover {
        background: #eef0ff;
        color: #695EEE;
    }

    .comm-nav-tab.active {
        background: linear-gradient(135deg, #695EEE 0%, #8b7ff5 100%);
        color: #fff;
        box-shadow: 0 6px 18px rgba(105, 94, 238, 0.28);
    }

    .comm-nav-tab.active i {
        opacity: 1;
    }

    @media (max-width: 991px) {
        .comm-nav-tab {
            flex: 1 1 calc(50% - 6px);
        }
    }

    @media (max-width: 575px) {
        .comm-nav-tab {
            flex: 1 1 100%;
        }
    }
</style>
