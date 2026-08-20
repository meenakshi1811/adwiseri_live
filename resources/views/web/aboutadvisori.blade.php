@extends('web.layout.main')

@section('main-section')
@php
    $aboutHeading = optional($about_adwiseri)->heading ?: 'Built for modern immigration consultancies';
    $aboutContent = optional($about_adwiseri)->content ?: 'Adwiseri helps visa and immigration teams run cases, documents, billing, and client communication from one secure cloud workspace — so your practice stays organised, compliant, and ready to scale.';
    $aboutBanner = optional($about_adwiseri)->banner;
    $aboutImage = optional($about_adwiseri)->image;
@endphp

<style>
    .about-saas {
        --about-ink: #1E2433;
        --about-muted: #5A6275;
        --about-line: #E2E6F0;
        --about-surface: #FFFFFF;
        --about-wash: #F5F6FA;
        --about-mist: #EEF1F8;
        --about-primary: var(--adwiseri-primary, #695EEE);
        --about-primary-deep: var(--adwiseri-primary-deep, #4C3BB7);
        --about-primary-soft: rgba(105, 94, 238, 0.12);
        color: var(--about-ink);
        background: var(--about-wash);
        overflow: hidden;
    }

    .about-saas * {
        box-sizing: border-box;
    }

    .about-saas .about-container {
        width: min(1120px, calc(100% - 2.5rem));
        margin: 0 auto;
    }

    /* Hero */
    .about-hero {
        position: relative;
        min-height: clamp(320px, 52vh, 520px);
        display: flex;
        align-items: flex-end;
        padding: 4.5rem 0 3.5rem;
        background:
            radial-gradient(ellipse 80% 60% at 85% 20%, rgba(168, 155, 255, 0.35), transparent 55%),
            radial-gradient(ellipse 70% 50% at 10% 80%, rgba(105, 94, 238, 0.22), transparent 50%),
            linear-gradient(145deg, #2A2558 0%, #3D348B 42%, #695EEE 100%);
        color: #fff;
    }

    .about-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.55), transparent 85%);
        pointer-events: none;
    }

    @if($aboutBanner)
    .about-hero.has-banner {
        background-image:
            linear-gradient(120deg, rgba(30, 28, 70, 0.88) 0%, rgba(76, 59, 183, 0.72) 55%, rgba(105, 94, 238, 0.55) 100%),
            url('{{ asset('admin_assets/about_advisori/banner/'.$aboutBanner) }}');
        background-size: cover;
        background-position: center;
    }
    @endif

    .about-hero-inner {
        position: relative;
        z-index: 1;
        max-width: 720px;
    }

    .about-brand {
        display: inline-block;
        font-size: clamp(2.4rem, 5vw, 3.75rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.05;
        margin: 0 0 1rem;
    }

    .about-hero-lead {
        font-size: clamp(1.05rem, 2vw, 1.25rem);
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.9);
        max-width: 34rem;
        margin: 0 0 1.75rem;
    }

    .about-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .about-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.8rem 1.35rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .about-btn-primary {
        background: #fff;
        color: var(--about-primary-deep);
        box-shadow: 0 8px 24px rgba(20, 16, 60, 0.25);
    }

    .about-btn-primary:hover {
        background: #F5F6FA;
        color: var(--about-primary-deep);
        transform: translateY(-1px);
    }

    .about-btn-ghost {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.35);
    }

    .about-btn-ghost:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
    }

    /* Story */
    .about-story {
        padding: 4.5rem 0 3rem;
    }

    .about-story-grid {
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 3rem;
        align-items: center;
    }

    .about-kicker {
        display: inline-block;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--about-primary);
        margin-bottom: 0.75rem;
    }

    .about-title {
        font-size: clamp(1.65rem, 3vw, 2.15rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0 0 1rem;
        color: var(--about-ink);
    }

    .about-copy {
        font-size: 1.05rem;
        line-height: 1.7;
        color: var(--about-muted);
        margin: 0 0 1rem;
    }

    .about-copy:last-child {
        margin-bottom: 0;
    }

    .about-visual {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background:
            linear-gradient(160deg, var(--about-mist), #fff 45%, rgba(105, 94, 238, 0.08)),
            var(--about-surface);
        border: 1px solid var(--about-line);
        min-height: 320px;
        box-shadow: 0 18px 40px rgba(30, 36, 51, 0.08);
    }

    .about-visual img {
        width: 100%;
        height: 100%;
        min-height: 320px;
        object-fit: cover;
        display: block;
    }

    .about-visual-fallback {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 2rem;
        min-height: 320px;
        background:
            radial-gradient(circle at 80% 20%, rgba(105, 94, 238, 0.28), transparent 45%),
            linear-gradient(165deg, #EEF1F8 0%, #FFFFFF 50%, #E8E4FF 100%);
    }

    .about-visual-fallback strong {
        font-size: 1.35rem;
        color: var(--about-ink);
        margin-bottom: 0.35rem;
    }

    .about-visual-fallback span {
        color: var(--about-muted);
        font-size: 0.95rem;
        max-width: 16rem;
        line-height: 1.5;
    }

    /* Stats */
    .about-stats {
        padding: 0 0 3.5rem;
    }

    .about-stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        padding: 1.5rem;
        background: var(--about-surface);
        border: 1px solid var(--about-line);
        border-radius: 14px;
        box-shadow: 0 10px 28px rgba(30, 36, 51, 0.05);
    }

    .about-stat {
        text-align: center;
        padding: 0.75rem 0.5rem;
    }

    .about-stat + .about-stat {
        border-left: 1px solid var(--about-line);
    }

    .about-stat-value {
        display: block;
        font-size: clamp(1.6rem, 3vw, 2rem);
        font-weight: 700;
        color: var(--about-primary);
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem;
    }

    .about-stat-label {
        font-size: 0.88rem;
        color: var(--about-muted);
        line-height: 1.4;
    }

    /* Values */
    .about-values {
        padding: 1rem 0 4rem;
    }

    .about-section-head {
        max-width: 560px;
        margin: 0 auto 2.25rem;
        text-align: center;
    }

    .about-values-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }

    .about-value-card {
        background: var(--about-surface);
        border: 1px solid var(--about-line);
        border-radius: 14px;
        padding: 1.6rem 1.4rem;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .about-value-card:hover {
        transform: translateY(-4px);
        border-color: rgba(105, 94, 238, 0.35);
        box-shadow: 0 16px 32px rgba(105, 94, 238, 0.12);
    }

    .about-value-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--about-primary-soft);
        color: var(--about-primary);
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .about-value-card h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 0.55rem;
        color: var(--about-ink);
    }

    .about-value-card p {
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.6;
        color: var(--about-muted);
    }

    /* Platform */
    .about-platform {
        padding: 0 0 4.5rem;
    }

    .about-platform-panel {
        display: grid;
        grid-template-columns: 0.9fr 1.1fr;
        gap: 2.5rem;
        align-items: center;
        background: linear-gradient(135deg, #2A2558 0%, #4C3BB7 55%, #695EEE 100%);
        border-radius: 18px;
        padding: clamp(1.75rem, 4vw, 2.75rem);
        color: #fff;
        box-shadow: 0 22px 48px rgba(76, 59, 183, 0.28);
    }

    .about-platform-panel .about-kicker {
        color: rgba(255, 255, 255, 0.75);
    }

    .about-platform-panel .about-title {
        color: #fff;
    }

    .about-platform-panel .about-copy {
        color: rgba(255, 255, 255, 0.85);
    }

    .about-feature-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 0.85rem;
    }

    .about-feature-list li {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 10px;
        padding: 0.95rem 1rem;
    }

    .about-feature-list i {
        margin-top: 0.15rem;
        color: #C7BFFF;
        flex-shrink: 0;
    }

    .about-feature-list strong {
        display: block;
        font-size: 0.98rem;
        margin-bottom: 0.2rem;
    }

    .about-feature-list span {
        font-size: 0.88rem;
        line-height: 1.45;
        color: rgba(255, 255, 255, 0.78);
    }

    /* CTA */
    .about-cta {
        padding: 0 0 5rem;
    }

    .about-cta-box {
        text-align: center;
        background: var(--about-surface);
        border: 1px solid var(--about-line);
        border-radius: 16px;
        padding: clamp(2rem, 4vw, 3rem);
        box-shadow: 0 12px 30px rgba(30, 36, 51, 0.05);
    }

    .about-cta-box .about-title {
        margin-bottom: 0.65rem;
    }

    .about-cta-box .about-copy {
        max-width: 34rem;
        margin: 0 auto 1.5rem;
    }

    .about-cta-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .about-btn-solid {
        background: var(--about-primary);
        color: #fff;
    }

    .about-btn-solid:hover {
        background: var(--adwiseri-primary-hover, #564BB0);
        color: #fff;
        transform: translateY(-1px);
    }

    .about-btn-outline {
        background: transparent;
        color: var(--about-primary);
        border: 1px solid rgba(105, 94, 238, 0.35);
    }

    .about-btn-outline:hover {
        background: var(--about-primary-soft);
        color: var(--about-primary-deep);
    }

    @media (max-width: 992px) {
        .about-story-grid,
        .about-platform-panel,
        .about-values-grid {
            grid-template-columns: 1fr;
        }

        .about-stats-row {
            grid-template-columns: repeat(2, 1fr);
        }

        .about-stat:nth-child(odd) {
            border-left: none;
        }

        .about-stat:nth-child(even) {
            border-left: 1px solid var(--about-line);
        }

        .about-stat:nth-child(n + 3) {
            border-top: 1px solid var(--about-line);
        }
    }

    @media (max-width: 576px) {
        .about-saas .about-container {
            width: calc(100% - 1.5rem);
        }

        .about-hero {
            padding: 3.25rem 0 2.5rem;
            min-height: auto;
        }

        .about-stats-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .about-stat,
        .about-stat + .about-stat {
            border-left: none;
            border-top: 1px solid var(--about-line);
            padding: 1rem 0.25rem;
        }

        .about-stat:first-child {
            border-top: none;
        }
    }
</style>

<div class="about-saas">
    <section class="about-hero {{ $aboutBanner ? 'has-banner' : '' }}">
        <div class="about-container">
            <div class="about-hero-inner">
                <h1 class="about-brand">Adwiseri</h1>
                <p class="about-hero-lead">
                    The cloud platform that helps immigration consultancies manage cases, clients, and compliance with clarity and confidence.
                </p>
                <div class="about-hero-actions">
                    <a href="{{ route('membership') }}" class="about-btn about-btn-primary">View plans</a>
                    <a href="{{ route('contactus') }}" class="about-btn about-btn-ghost">Talk to us</a>
                </div>
            </div>
        </div>
    </section>

    <section class="about-story">
        <div class="about-container">
            <div class="about-story-grid">
                <div>
                    <span class="about-kicker">Our story</span>
                    <h2 class="about-title">{{ $aboutHeading }}</h2>
                    <p class="about-copy">{{ $aboutContent }}</p>
                    <p class="about-copy">
                        From first enquiry to final decision, Adwiseri brings your team, documents, invoices, and reporting into a single workflow designed for visa and immigration professionals.
                    </p>
                </div>
                <div class="about-visual">
                    @if($aboutImage)
                        <img src="{{ asset('admin_assets/about_advisori/image/'.$aboutImage) }}" alt="About Adwiseri">
                    @else
                        <div class="about-visual-fallback">
                            <strong>One workspace. Every case.</strong>
                            <span>Dummy preview — replace with your about image from admin settings anytime.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="about-stats">
        <div class="about-container">
            <div class="about-stats-row">
                <div class="about-stat">
                    <span class="about-stat-value">12k+</span>
                    <span class="about-stat-label">Cases tracked across teams</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-value">98%</span>
                    <span class="about-stat-label">Customer satisfaction score</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-value">40+</span>
                    <span class="about-stat-label">Countries supported in workflows</span>
                </div>
                <div class="about-stat">
                    <span class="about-stat-value">24/7</span>
                    <span class="about-stat-label">Secure cloud availability</span>
                </div>
            </div>
        </div>
    </section>

    <section class="about-values">
        <div class="about-container">
            <div class="about-section-head">
                <span class="about-kicker">What we stand for</span>
                <h2 class="about-title">Principles that shape the product</h2>
                <p class="about-copy">Dummy content for now — these pillars reflect how Adwiseri is designed for professional consultancy teams.</p>
            </div>
            <div class="about-values-grid">
                <article class="about-value-card">
                    <div class="about-value-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h3>Trust & security</h3>
                    <p>Enterprise-minded controls, role-based access, and careful handling of sensitive client information at every step.</p>
                </article>
                <article class="about-value-card">
                    <div class="about-value-icon"><i class="fa-solid fa-diagram-project"></i></div>
                    <h3>Operational clarity</h3>
                    <p>Clear case status, shared documents, and accountable workflows so nothing slips between team members.</p>
                </article>
                <article class="about-value-card">
                    <div class="about-value-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <h3>Growth ready</h3>
                    <p>Scale users, clients, and reporting without rebuilding your process — Adwiseri grows with your practice.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="about-platform">
        <div class="about-container">
            <div class="about-platform-panel">
                <div>
                    <span class="about-kicker">The platform</span>
                    <h2 class="about-title">Everything your consultancy needs in one place</h2>
                    <p class="about-copy">
                        Dummy overview of core capabilities. Replace this copy with your final product messaging when ready.
                    </p>
                </div>
                <ul class="about-feature-list">
                    <li>
                        <i class="fa-solid fa-folder-open"></i>
                        <div>
                            <strong>Case & document management</strong>
                            <span>Organise applications, checklists, and client files with structured folders and status tracking.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <div>
                            <strong>Invoicing & payments</strong>
                            <span>Create professional invoices, track receivables, and keep financial records aligned with each case.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fa-solid fa-users"></i>
                        <div>
                            <strong>Team collaboration</strong>
                            <span>Multi-user access, internal notes, and communication tools built for consultancy workflows.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fa-solid fa-chart-pie"></i>
                        <div>
                            <strong>Analytics & reporting</strong>
                            <span>Monitor pipeline health, workload, and performance with reports your leadership can act on.</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section class="about-cta">
        <div class="about-container">
            <div class="about-cta-box">
                <span class="about-kicker">Get started</span>
                <h2 class="about-title">Ready to modernise your practice?</h2>
                <p class="about-copy">
                    Explore subscription plans or reach out for a walkthrough. This CTA uses placeholder messaging until final content is confirmed.
                </p>
                <div class="about-cta-actions">
                    <a href="{{ route('membership') }}" class="about-btn about-btn-solid">See subscription plans</a>
                    <a href="{{ route('features') }}" class="about-btn about-btn-outline">Explore features</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
