@extends('web.layout.main')

@section('main-section')
<style>
    .do-page {
        --do-ink: #1E2433;
        --do-muted: #5A6275;
        --do-line: #E2E6F0;
        --do-surface: #FFFFFF;
        --do-wash: #F5F6FA;
        --do-primary: #695EEE;
        --do-primary-deep: #4C3BB7;
        --do-primary-soft: rgba(105, 94, 238, 0.1);
        color: var(--do-ink);
        background: var(--do-wash);
        overflow: hidden;
    }

    .do-page * {
        box-sizing: border-box;
    }

    .do-container {
        width: min(1120px, calc(100% - 2.5rem));
        margin: 0 auto;
    }

    .do-hero {
        position: relative;
        min-height: clamp(280px, 44vh, 420px);
        display: flex;
        align-items: center;
        padding: 4.5rem 0 3.25rem;
        background:
            linear-gradient(120deg, rgba(30, 28, 70, 0.9) 0%, rgba(76, 59, 183, 0.78) 52%, rgba(105, 94, 238, 0.72) 100%),
            url('{{ asset('web_assets/images/membership.png') }}') center / cover no-repeat;
        color: #fff;
    }

    .do-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
        background-size: 44px 44px;
        mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.45), transparent 88%);
        pointer-events: none;
    }

    .do-hero-inner {
        position: relative;
        z-index: 1;
        max-width: 680px;
    }

    .do-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    .do-hero-title {
        font-size: clamp(2rem, 4.5vw, 3rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.1;
        margin: 0 0 0.85rem;
    }

    .do-hero-lead {
        font-size: clamp(1rem, 2vw, 1.15rem);
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.9);
        max-width: 36rem;
        margin: 0;
    }

    .do-content {
        padding: 3.5rem 0 4.5rem;
    }

    .do-intro {
        text-align: center;
        max-width: 640px;
        margin: 0 auto 2.5rem;
    }

    .do-intro h2 {
        font-size: clamp(1.5rem, 3vw, 1.85rem);
        font-weight: 700;
        margin: 0 0 0.65rem;
        letter-spacing: -0.02em;
    }

    .do-intro p {
        margin: 0;
        color: var(--do-muted);
        font-size: 1.02rem;
        line-height: 1.65;
    }

    .do-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.5rem;
        align-items: stretch;
    }

    .do-card {
        background: var(--do-surface);
        border: 1px solid var(--do-line);
        border-radius: 16px;
        box-shadow: 0 14px 36px rgba(30, 36, 51, 0.06);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .do-card-head {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1.35rem 1.5rem;
        border-bottom: 1px solid var(--do-line);
        background: linear-gradient(180deg, #fff 0%, #FAFBFD 100%);
    }

    .do-card-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--do-primary-soft);
        color: var(--do-primary);
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .do-card-head h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .do-card-head p {
        margin: 0.15rem 0 0;
        font-size: 0.88rem;
        color: var(--do-muted);
    }

    .do-table-wrap {
        padding: 0.35rem 1.25rem 0.5rem;
        flex: 1;
    }

    .do-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
    }

    .do-table thead th {
        text-align: left;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--do-muted);
        padding: 0.85rem 0.75rem;
        border-bottom: 1px solid var(--do-line);
    }

    .do-table tbody tr {
        transition: background 0.18s ease;
    }

    .do-table tbody tr:hover {
        background: rgba(105, 94, 238, 0.04);
    }

    .do-table tbody td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #EEF1F6;
        vertical-align: middle;
        font-size: 0.98rem;
    }

    .do-table tbody tr:last-child td {
        border-bottom: none;
    }

    .do-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 4.25rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--do-primary) 0%, var(--do-primary-deep) 100%);
        color: #fff;
        font-weight: 700;
        font-size: 0.92rem;
        letter-spacing: -0.01em;
        box-shadow: 0 6px 16px rgba(105, 94, 238, 0.22);
    }

    .do-plan {
        font-weight: 600;
        color: var(--do-ink);
    }

    .do-term {
        color: var(--do-muted);
    }

    .do-note {
        margin: 0;
        padding: 1rem 1.5rem 1.35rem;
        font-size: 0.86rem;
        line-height: 1.55;
        color: #7A8194;
        border-top: 1px dashed var(--do-line);
        background: #FAFBFD;
    }

    .do-note strong {
        color: var(--do-primary-deep);
        font-weight: 700;
    }

    .do-cta {
        padding: 0 0 4.5rem;
    }

    .do-cta-box {
        text-align: center;
        background: linear-gradient(135deg, #2A2558 0%, #4C3BB7 55%, #695EEE 100%);
        border-radius: 16px;
        padding: clamp(2rem, 4vw, 2.75rem);
        color: #fff;
        box-shadow: 0 20px 44px rgba(76, 59, 183, 0.24);
    }

    .do-cta-box h2 {
        font-size: clamp(1.35rem, 3vw, 1.75rem);
        font-weight: 700;
        margin: 0 0 0.55rem;
    }

    .do-cta-box p {
        margin: 0 auto 1.35rem;
        max-width: 34rem;
        color: rgba(255, 255, 255, 0.88);
        line-height: 1.6;
    }

    .do-cta-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .do-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.78rem 1.35rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    .do-btn-primary {
        background: #fff;
        color: var(--do-primary-deep);
    }

    .do-btn-primary:hover {
        background: #F5F6FA;
        color: var(--do-primary-deep);
        transform: translateY(-1px);
    }

    .do-btn-ghost {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.32);
    }

    .do-btn-ghost:hover {
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
    }

    @media (max-width: 992px) {
        .do-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .do-container {
            width: calc(100% - 1.5rem);
        }

        .do-hero {
            padding: 3.25rem 0 2.5rem;
            min-height: auto;
        }

        .do-table thead {
            display: none;
        }

        .do-table tbody tr {
            display: block;
            padding: 0.35rem 0;
        }

        .do-table tbody td {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: none;
            padding: 0.55rem 0.75rem;
        }

        .do-table tbody td::before {
            content: attr(data-label);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--do-muted);
        }

        .do-table tbody tr + tr {
            border-top: 1px solid var(--do-line);
        }
    }
</style>

<div class="do-page">
    <section class="do-hero" aria-label="Discounts and Offers">
        <div class="do-container">
            <div class="do-hero-inner">
                <span class="do-eyebrow"><i class="fa-solid fa-tags" aria-hidden="true"></i> Savings &amp; rewards</span>
                <h1 class="do-hero-title">Discounts &amp; Offers</h1>
                <p class="do-hero-lead">
                    Save on multi-year subscriptions or earn wallet cashback when you join Adwiseri on a paid plan.
                </p>
            </div>
        </div>
    </section>

    <section class="do-content">
        <div class="do-container">
            <div class="do-intro">
                <h2>Choose the benefit that fits your subscription</h2>
                <p>Long-term subscription discounts and new-subscriber cashback offers are listed below. Terms apply to each category.</p>
            </div>

            <div class="do-grid">
                <article class="do-card">
                    <div class="do-card-head">
                        <span class="do-card-icon"><i class="fa-solid fa-percent" aria-hidden="true"></i></span>
                        <div>
                            <h3>Discounts</h3>
                            <p>Multi-year subscription savings</p>
                        </div>
                    </div>
                    <div class="do-table-wrap">
                        <table class="do-table">
                            <thead>
                                <tr>
                                    <th scope="col">Discount</th>
                                    <th scope="col">Subscription term</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-label="Discount"><span class="do-badge">10%</span></td>
                                    <td data-label="Subscription term"><span class="do-term">2 years subscription</span></td>
                                </tr>
                                <tr>
                                    <td data-label="Discount"><span class="do-badge">20%</span></td>
                                    <td data-label="Subscription term"><span class="do-term">3 years subscription</span></td>
                                </tr>
                                <tr>
                                    <td data-label="Discount"><span class="do-badge">50%</span></td>
                                    <td data-label="Subscription term"><span class="do-term">5 years subscription</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="do-note"><strong>*</strong> Discounts cannot be combined with any existing or newly introduced offer(s).</p>
                </article>

                <article class="do-card">
                    <div class="do-card-head">
                        <span class="do-card-icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span>
                        <div>
                            <h3>Offers</h3>
                            <p>New subscriber wallet cashback</p>
                        </div>
                    </div>
                    <div class="do-table-wrap">
                        <table class="do-table">
                            <thead>
                                <tr>
                                    <th scope="col">Cashback</th>
                                    <th scope="col">Plan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-label="Cashback"><span class="do-badge">25%</span></td>
                                    <td data-label="Plan"><span class="do-plan">Solo</span></td>
                                </tr>
                                <tr>
                                    <td data-label="Cashback"><span class="do-badge">50%</span></td>
                                    <td data-label="Plan"><span class="do-plan">Adwiseri</span></td>
                                </tr>
                                <tr>
                                    <td data-label="Cashback"><span class="do-badge">75%</span></td>
                                    <td data-label="Plan"><span class="do-plan">Adwiseri+</span></td>
                                </tr>
                                <tr>
                                    <td data-label="Cashback"><span class="do-badge">100%</span></td>
                                    <td data-label="Plan"><span class="do-plan">Enterprises</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="do-note"><strong>*</strong> For new subscribers only. Cashbacks are rewarded in the form of wallet credits.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="do-cta">
        <div class="do-container">
            <div class="do-cta-box">
                <h2>Ready to get started?</h2>
                <p>Compare subscription plans or register today to see which discount or cashback applies to your consultancy.</p>
                <div class="do-cta-actions">
                    <a href="{{ route('membership') }}" class="do-btn do-btn-primary">View pricing plans</a>
                    <a href="{{ route('user_register') }}" class="do-btn do-btn-ghost">Register now</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
