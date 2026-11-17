@extends('web.layout.main')

@section('main-section')
@php
    // ---- Section visibility (Admin → Homepage Sections) ----
    $homepageSections = $homepageSectionVisibility ?? \App\Models\HomepageSectionSetting::defaultVisibility();
    $showHomepageSection = function (string $key) use ($homepageSections): bool {
        return \App\Models\HomepageSectionSetting::castVisibility($homepageSections[$key] ?? true, true);
    };
    // Numbered eyebrows ("// 01 · …") follow whichever sections are shown.
    $sectionNo = 0;
    $nextNo = function () use (&$sectionNo) { return sprintf('%02d', ++$sectionNo); };

    // ---- Data ----
    $plans = collect($price_plans ?? []);
    $featureList = collect($features ?? []);
    $faqList = collect($faqs ?? []);
    $useCases = config('use_cases', []);
    $contactEmail = $supportEmail ?? 'care@adwiseri.com';
    $num = function ($v) { return is_numeric($v) ? number_format((float) $v) : $v; };
    $isFree = function ($p) { return (float) $p->price_per_year == 0.0 || in_array($p->plan_name, ['Free', 'Free Plan'], true); };

    $freePlan = $plans->first($isFree);
    $largestPlan = $plans->filter(function ($p) { return is_numeric($p->client_limit); })->sortByDesc(function ($p) { return (float) $p->client_limit; })->first();
    $paidPlans = $plans->reject($isFree)->values();
    $cheapestPaid = $paidPlans->sortBy(function ($p) { return (float) $p->price_per_year; })->first();
    $mockPlan = $paidPlans->count() > 1 ? $paidPlans->sortByDesc(function ($p) { return (float) $p->price_per_year; })->values()->get(1) : $paidPlans->first();
    $mockLimit = $mockPlan && is_numeric($mockPlan->client_limit) ? (int) $mockPlan->client_limit : 5000;
    $userCounts = $plans->pluck('no_of_users')->filter(function ($v) { return is_numeric($v); })->map(function ($v) { return (int) $v; });

    // Short "who it's for" lines for the known plans; anything new describes itself from its limits.
    $planBlurbs = [
        'free' => 'Explore adwiseri with your first clients.',
        'solo' => 'For independent advisors and consultants.',
        'adwiseri' => 'For small teams growing their caseload.',
        'adwiseri+' => 'For established consultancies and multi-advisor teams.',
        'enterprise' => 'For law firms and multi-branch networks.',
    ];

    // Key features: pick a line icon (and an optional illustration) from the feature's name.
    $featureStyles = [
        ['secur', 'shield', null], ['case', 'briefcase', 'case'], ['application', 'briefcase', 'case'],
        ['document', 'folder', null], ['storage', 'folder', null], ['cloud', 'folder', null],
        ['report', 'chart', null], ['analytic', 'chart', null],
        ['unlimited', 'infinity', 'data'], ['data', 'infinity', 'data'],
        ['pric', 'tag', 'pricing'], ['invoic', 'receipt', 'invoice'], ['payment', 'receipt', 'invoice'],
        ['user', 'users', null], ['access', 'users', null], ['team', 'users', null],
        ['messag', 'message', null], ['communic', 'message', null], ['device', 'devices', null], ['currenc', 'globe', null],
    ];
    $featureStyle = function ($name) use ($featureStyles) {
        $n = strtolower((string) $name);
        foreach ($featureStyles as [$needle, $icon, $visual]) {
            if (\Illuminate\Support\Str::contains($n, $needle)) { return [$icon, $visual]; }
        }
        return [null, null];
    };

    $aboutUsCopy = 'We care about your business, so tried our best to offer you a solution to manage your daily tasks with efficiency & security leaving you time to focus on the core.';
@endphp

{{-- Signed-in visitors get the app top bar (not fixed), so the hero needs no header offset. --}}
<main class="ds ds-home" @if(isset($user) && empty($registration_flow)) style="--site-nav-height:0px" @endif>

{{-- =========================================================
     HERO + TRUST BADGES
     ========================================================= --}}
@if($showHomepageSection('banner') || $showHomepageSection('about_highlights'))
<section class="hero" id="top">
  <div class="hero-bg" aria-hidden="true"><span class="orb o1"></span><span class="orb o2"></span><span class="orb o3"></span></div>
  <div class="wrap">
    @if($showHomepageSection('banner'))
    <div class="hero-copy">
      <a class="pill" href="{{ $showHomepageSection('price_plans') ? '#pricing' : route('membership') }}" data-reveal>
        <span class="tag">NEW</span> Fixed yearly pricing — no extra invoices for add-ons
        <svg class="icon"><use href="#i-arrow"/></svg>
      </a>
      <h1 data-reveal style="--d:.08s">The one-stop solution for <span class="ds-grad-text">Visa &amp; Immigration</span> Consultancies</h1>
      <p class="ds-lead" data-reveal style="--d:.16s">Cloud Storage, Users &amp; Case Management, Analytics, Reports, Invoicing and much more — everything your advisory needs, in one secure platform.</p>
      <div class="hero-ctas" data-reveal style="--d:.24s">
        <a class="btn btn-primary" href="{{ route('get_demo') }}">Get A Demo <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
        @if($freePlan)
          <a class="btn btn-ghost" href="{{ route('user_register_plan', $freePlan->plan_name) }}">Start free for {{ (int) $freePlan->validity }} days</a>
        @else
          <a class="btn btn-ghost" href="{{ route('membership') }}">View price plans</a>
        @endif
      </div>
      <div class="hero-note" data-reveal style="--d:.32s">
        <span><svg class="icon"><use href="#i-check"/></svg>Instant activation</span>
        <span><svg class="icon"><use href="#i-check"/></svg>Unlimited data</span>
        <span><svg class="icon"><use href="#i-check"/></svg>Upgrade anytime</span>
      </div>
    </div>

    {{-- Product illustration --}}
    <div class="mock-wrap" data-reveal="zoom" style="--d:.3s" id="mockWrap">
      <div class="mock-glow" aria-hidden="true"></div>

      <div class="floater f1" aria-hidden="true">
        <span class="fi"><svg class="icon"><use href="#i-users"/></svg></span>
        <span><b>Case assigned</b><small>Student Visa — UK → 2 advisors</small></span>
      </div>
      <div class="floater f2" aria-hidden="true">
        <span class="fi"><svg class="icon"><use href="#i-receipt"/></svg></span>
        <span><b>Invoice paid · $350.00</b><small>Payment collected in USD</small></span>
      </div>
      <div class="floater f3" aria-hidden="true">
        <span class="fi"><svg class="icon"><use href="#i-folder"/></svg></span>
        <span><b>14 documents labelled<span class="typing"><i></i><i></i><i></i></span></b><small>Identity · Financial · Education</small></span>
      </div>

      <div class="mock" id="mock" role="img" aria-label="Illustration of the adwiseri dashboard showing cases, clients, documents and revenue">
        <div class="mock-bar">
          <div class="mock-dots"><i></i><i></i><i></i></div>
          <div class="mock-url"><svg class="icon"><use href="#i-lock"/></svg>adwiseri.com/dashboard</div>
          <div style="width:47px"></div>
        </div>
        <div class="mock-body">
          <aside class="mock-side">
            <img class="logo-sm" src="{{ asset('web_assets/images/Style2.png') }}" alt="" onerror="this.style.display='none'">
            <span class="mi on"><svg class="icon"><use href="#i-grid"/></svg>Dashboard</span>
            <span class="mi"><svg class="icon"><use href="#i-users"/></svg>Clients</span>
            <span class="mi"><svg class="icon"><use href="#i-briefcase"/></svg>Applications</span>
            <span class="mi"><svg class="icon"><use href="#i-folder"/></svg>Documents</span>
            <span class="mi"><svg class="icon"><use href="#i-receipt"/></svg>Invoices</span>
            <span class="mi"><svg class="icon"><use href="#i-message"/></svg>Messages</span>
            <span class="mi"><svg class="icon"><use href="#i-chart"/></svg>Reports</span>
            <div class="side-card"><b>{{ $mockPlan->plan_name ?? 'Adwiseri+' }} plan</b>{{ number_format((int) round($mockLimit * 0.62)) }} of {{ number_format($mockLimit) }} clients<div class="meter"><i></i></div></div>
          </aside>
          <div class="mock-main">
            <div class="mock-head">
              <div><small>Good morning 👋</small><h4>Consultancy overview</h4></div>
              <span class="mock-btn"><svg class="icon"><use href="#i-plus"/></svg>New case</span>
            </div>
            <div class="kpis">
              <div class="kpi"><small>Active cases</small><b>248</b><em>▲ 12% this month</em></div>
              <div class="kpi"><small>Clients</small><b>3,110</b><em>▲ 86 new</em></div>
              <div class="kpi"><small>Docs pending</small><b>36</b><em style="color:#B45309">● 9 due today</em></div>
              <div class="kpi"><small>Collected</small><b>$42.6k</b><em>▲ 18% vs last</em></div>
            </div>
            <div class="mock-grid">
              <div class="panel chart">
                <div class="panel-title">Applications by month <span>Last 6 months</span></div>
                <svg viewBox="0 0 400 150" preserveAspectRatio="none" aria-hidden="true">
                  <defs><linearGradient id="ga" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#695EEE" stop-opacity=".32"/><stop offset="1" stop-color="#695EEE" stop-opacity="0"/></linearGradient></defs>
                  <g stroke="#EEF1F8" stroke-width="1"><line x1="0" y1="30" x2="400" y2="30"/><line x1="0" y1="70" x2="400" y2="70"/><line x1="0" y1="110" x2="400" y2="110"/></g>
                  <path d="M0 118 C40 110 60 92 100 96 S160 70 200 74 S260 40 300 46 S360 18 400 22 L400 150 L0 150Z" fill="url(#ga)"/>
                  <path class="line" d="M0 118 C40 110 60 92 100 96 S160 70 200 74 S260 40 300 46 S360 18 400 22" fill="none" stroke="#695EEE" stroke-width="3" stroke-linecap="round"/>
                  <path d="M0 132 C50 128 80 120 120 122 S190 104 230 108 S300 86 340 90 S380 76 400 78" fill="none" stroke="#D946EF" stroke-width="2" stroke-dasharray="5 6" opacity=".7"/>
                  <circle cx="300" cy="46" r="5" fill="#fff" stroke="#695EEE" stroke-width="3"/>
                </svg>
              </div>
              <div class="panel">
                <div class="panel-title">Recent applications <span>View all</span></div>
                <div class="rows">
                  <div class="mrow"><span class="av">PS</span><span><b>Priya S.</b><small>Student Visa · UK</small></span><span class="st rev">In review</span></div>
                  <div class="mrow"><span class="av" style="background:#D946EF">DO</span><span><b>Daniel O.</b><small>Work Permit · Canada</small></span><span class="st pend">Docs pending</span></div>
                  <div class="mrow"><span class="av" style="background:#06B6D4">ML</span><span><b>Mei L.</b><small>Visitor Visa · Australia</small></span><span class="st ok">Approved</span></div>
                  <div class="mrow"><span class="av" style="background:#F59E0B">OF</span><span><b>Omar F.</b><small>CBI Application</small></span><span class="st sub">Submitted</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

    @if($showHomepageSection('about_highlights'))
    <div class="tbs" @unless($showHomepageSection('banner')) style="margin-top:0" @endunless>
      <div class="tb" data-reveal><span class="bi"><svg class="icon"><use href="#i-zap"/></svg></span><span><b>Instant Activation</b><small>Start working in minutes</small></span></div>
      <div class="tb" data-reveal style="--d:.08s"><span class="bi"><svg class="icon"><use href="#i-lock"/></svg></span><span><b>Secure Data</b><small>Enterprise-grade security</small></span></div>
      <div class="tb" data-reveal style="--d:.16s"><span class="bi"><svg class="icon"><use href="#i-smile"/></svg></span><span><b>Easy to Use</b><small>One simple dashboard</small></span></div>
      <div class="tb" data-reveal style="--d:.24s"><span class="bi"><svg class="icon"><use href="#i-headset"/></svg></span><span><b>Dedicated Support</b><small>Mon–Fri · 10am–6pm GMT</small></span></div>
    </div>
    @endif
  </div>
</section>
@endif

{{-- =========================================================
     PRACTICE TYPES STRIP
     ========================================================= --}}
@if($showHomepageSection('practice_strip') && count($useCases))
<section class="strip" aria-label="Practices adwiseri is built for">
  <p class="strip-title">Purpose-built for every kind of visa &amp; immigration practice</p>
  <div class="marquee">
    <div class="marquee-track" id="marqueeTrack">
      @foreach($useCases as $uc)
        @if(!empty($uc['code']))
          <span class="chip"><span class="cc">{{ $uc['code'] }}</span>{{ $uc['short'] ?? $uc['name'] }}</span>
        @else
          <span class="chip"><svg class="icon"><use href="#i-{{ $uc['icon'] ?? 'globe' }}"/></svg>{{ $uc['short'] ?? $uc['name'] }}</span>
        @endif
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     KEY FEATURES — BENTO (Admin → Features)
     ========================================================= --}}
@if($showHomepageSection('key_features') && $featureList->count())
<section class="section" id="features">
  <div class="wrap">
    <div class="section-head">
      <span class="ds-eyebrow" data-reveal>// {{ $nextNo() }} · Key Features</span>
      <h2 class="ds-h2" data-reveal style="--d:.06s">Everything your consultancy runs on, <span class="ds-grad-text">in one place</span></h2>
      <p class="ds-lead" data-reveal style="--d:.12s">From the first client conversation to the final invoice — adwiseri brings your cases, documents, team and finances together so you can focus on advising.</p>
    </div>

    <div class="bento">
      @foreach($featureList as $i => $feature)
        @php
            [$fIcon, $fVisual] = $featureStyle($feature->name);
            $fDelay = number_format(($i % 3) * 0.08, 2);
        @endphp
        <article class="fcard {{ $fVisual ? 'span2' : '' }}" data-reveal style="--d:{{ $fDelay }}s">
          <span class="num">{{ sprintf('%02d', $i + 1) }}</span>
          @if($fVisual)<div class="card-flex"><div>@endif
            <span class="ci">
              @if($fIcon)
                <svg class="icon"><use href="#i-{{ $fIcon }}"/></svg>
              @elseif(!empty($feature->icon))
                <img src="{{ asset('admin_assets/features/icon/'.$feature->icon) }}" alt="">
              @else
                <svg class="icon"><use href="#i-sparkle"/></svg>
              @endif
            </span>
            <h3>{{ $feature->name }}</h3>
            <p>{{ \Illuminate\Support\Str::limit((string) $feature->content, 230) }}</p>
          @if($fVisual)
            </div>
            @if($fVisual === 'case')
              <div class="mini" aria-hidden="true">
                <div class="mini-row"><span>Student Visa · UK</span><span class="avatars"><i style="background:#695EEE">RK</i><i style="background:#D946EF">AJ</i></span></div>
                <div class="mini-row"><span>Skilled Worker · CA</span><span class="avatars"><i style="background:#06B6D4">SM</i></span></div>
                <div class="mini-row"><span>Partner Visa · AU</span><span class="avatars"><i style="background:#F59E0B">LT</i><i style="background:#695EEE">RK</i><i style="background:#16A34A">+1</i></span></div>
              </div>
            @elseif($fVisual === 'data')
              <div class="price-lock" aria-hidden="true">
                <svg class="icon" style="width:30px;height:30px;color:var(--ds-primary)"><use href="#i-infinity"/></svg>
                <b>No data cap</b>
                <small>Clients · Cases · Documents</small>
              </div>
            @elseif($fVisual === 'pricing')
              <div class="mini" aria-hidden="true">
                <div class="mini-row"><span>Annual plan</span><span style="color:var(--ds-primary-700)">Fixed</span></div>
                <div class="mini-row"><span>Add-on invoices</span><span style="color:var(--ds-green)">$0</span></div>
                <div class="mini-row"><span>Hidden fees</span><span style="color:var(--ds-green)">None</span></div>
              </div>
            @elseif($fVisual === 'invoice')
              <div class="mini" aria-hidden="true">
                <div class="mini-row"><span>INV-1042 · $350</span><span class="st ok">Paid</span></div>
                <div class="mini-row"><span>INV-1043 · £220</span><span class="st rev">Sent</span></div>
                <div class="mini-row"><span>INV-1044 · A$480</span><span class="st pend">Due</span></div>
              </div>
            @endif
            </div>
          @endif
        </article>
      @endforeach
    </div>

    <div class="section-foot" data-reveal>
      <a class="btn btn-ghost" href="{{ route('features') }}">Explore all features <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     ABOUT US / HOW ADWISERI WORKS — ZIG-ZAG
     ========================================================= --}}
@if($showHomepageSection('about_us'))
<section class="section deep" id="product">
  <div class="wrap">
    <div class="section-head">
      <span class="ds-eyebrow" data-reveal>// {{ $nextNo() }} · About Us</span>
      <h2 class="ds-h2" data-reveal style="--d:.06s">Say goodbye to spreadsheets, <br class="hide-sm">scattered files and manual follow-ups</h2>
      <p class="ds-lead" data-reveal style="--d:.12s">{{ $aboutUsCopy }}</p>
    </div>

    <div class="zz">
      <div class="zz-copy" data-reveal="left">
        <span class="ds-eyebrow">Case Management</span>
        <h3>Manage every application on the go</h3>
        <p class="ds-lead">Assign cases to the right people, see where every application stands and keep your whole team moving from one dashboard.</p>
        <ul class="ticks">
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Assign cases to one or multiple advisors/consultants</li>
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Track application &amp; user performance from a single dashboard</li>
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Work anytime, anywhere, on any device</li>
        </ul>
        <div class="zz-actions">
          <a class="btn btn-primary" href="{{ route('get_demo') }}">Get A Demo <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
          <a class="link-arrow" href="{{ route('features') }}">Read more <svg class="icon"><use href="#i-arrow"/></svg></a>
        </div>
      </div>
      <div class="zz-media" data-reveal="right">
        <div class="frame" aria-hidden="true">
          <div class="frame-head"><b>Applications board</b><small>248 active</small></div>
          <div class="kanban">
            <div class="kcol"><h5>New <span>12</span></h5>
              <div class="kcard"><span class="lbl l1">Student</span><b>Aarav M.</b><div class="meta"><span>UK</span><span class="avatars"><i style="background:#695EEE">RK</i></span></div></div>
              <div class="kcard"><span class="lbl l3">Visit</span><b>Sofia R.</b><div class="meta"><span>Schengen</span><span class="avatars"><i style="background:#06B6D4">SM</i></span></div></div>
            </div>
            <div class="kcol"><h5>In progress <span>31</span></h5>
              <div class="kcard"><span class="lbl l2">Work</span><b>Daniel O.</b><div class="meta"><span>Canada</span><span class="avatars"><i style="background:#D946EF">AJ</i><i style="background:#695EEE">RK</i></span></div></div>
              <div class="kcard"><span class="lbl l5">PR</span><b>Hana K.</b><div class="meta"><span>Australia</span><span class="avatars"><i style="background:#F59E0B">LT</i></span></div></div>
            </div>
            <div class="kcol"><h5>Decision <span>8</span></h5>
              <div class="kcard"><span class="lbl l4">Approved</span><b>Mei L.</b><div class="meta"><span>Australia</span><span class="avatars"><i style="background:#16A34A">TS</i></span></div></div>
              <div class="kcard"><span class="lbl l1">CBI</span><b>Omar F.</b><div class="meta"><span>Submitted</span><span class="avatars"><i style="background:#695EEE">RK</i></span></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="zz reverse">
      <div class="zz-copy" data-reveal="right">
        <span class="ds-eyebrow">Document Storage</span>
        <h3>Every document, filed where it belongs</h3>
        <p class="ds-lead">Store application and case documents against each application for future reference — no more hunting through inboxes and shared drives.</p>
        <ul class="ticks">
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Documents attached directly to each application/case</li>
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Pre-identified categories with suggested labels</li>
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Unlimited data with secure cloud storage</li>
        </ul>
        <div class="zz-actions">
          <a class="btn btn-primary" href="{{ route('get_demo') }}">Get A Demo <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
          <a class="link-arrow" href="{{ route('features') }}">Read more <svg class="icon"><use href="#i-arrow"/></svg></a>
        </div>
      </div>
      <div class="zz-media" data-reveal="left">
        <div class="frame" aria-hidden="true">
          <div class="frame-head"><b>Priya S. · Student Visa (UK)</b><small>14 files</small></div>
          <div class="docs">
            <div class="docrow"><span class="fi"><svg class="icon"><use href="#i-passport"/></svg></span><span><b>Passport.pdf</b><small>Uploaded 2 days ago · 1.2 MB</small></span><span class="lbl l1" style="margin:0">Identity</span></div>
            <div class="docrow"><span class="fi"><svg class="icon"><use href="#i-file"/></svg></span><span><b>Bank_statement_6m.pdf</b><small>Uploaded yesterday · 3.4 MB</small></span><span class="lbl l4" style="margin:0">Financial</span></div>
            <div class="docrow"><span class="fi"><svg class="icon"><use href="#i-cap"/></svg></span><span><b>Offer_letter_CAS.pdf</b><small>Uploaded today · 480 KB</small></span><span class="lbl l2" style="margin:0">Education</span></div>
          </div>
          <div class="upload"><svg class="icon"><use href="#i-upload"/></svg>IELTS_result.pdf<span class="bar"><i></i></span>78%</div>
        </div>
      </div>
    </div>

    <div class="zz">
      <div class="zz-copy" data-reveal="left">
        <span class="ds-eyebrow">Reports &amp; Analytics</span>
        <h3>Know how your team and business are performing</h3>
        <p class="ds-lead">View your data from different attributes and measure the performance of staff and your business as a whole.</p>
        <ul class="ticks">
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Graphical charts on the click of a button</li>
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Downloadable reports — basic or advanced, by plan</li>
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Staff and business-wide performance views</li>
        </ul>
        <div class="zz-actions">
          <a class="btn btn-primary" href="{{ route('get_demo') }}">Get A Demo <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
          <a class="link-arrow" href="{{ route('features') }}">Read more <svg class="icon"><use href="#i-arrow"/></svg></a>
        </div>
      </div>
      <div class="zz-media" data-reveal="right">
        <div class="frame" aria-hidden="true">
          <div class="frame-head"><b>Cases closed by advisor</b><small>This quarter</small></div>
          <div class="bars">
            <div class="b"><i style="--h:62%;--d:.0s"></i></div>
            <div class="b"><i style="--h:44%;--d:.08s"></i></div>
            <div class="b"><i style="--h:86%;--d:.16s"></i></div>
            <div class="b"><i style="--h:58%;--d:.24s"></i></div>
            <div class="b"><i style="--h:72%;--d:.32s"></i></div>
            <div class="b"><i style="--h:38%;--d:.40s"></i></div>
          </div>
          <div class="bars-x"><span>RK</span><span>AJ</span><span>SM</span><span>LT</span><span>TS</span><span>NP</span></div>
          <div class="rep-foot">
            <div class="legend"><span><i></i>Approved</span><span><i></i>Submitted</span></div>
            <span class="dl"><svg class="icon"><use href="#i-download"/></svg>Download report</span>
          </div>
        </div>
      </div>
    </div>

    <div class="zz reverse">
      <div class="zz-copy" data-reveal="right">
        <span class="ds-eyebrow">Invoices &amp; Payments</span>
        <h3>Bill clients and get paid, in any currency</h3>
        <p class="ds-lead">In-built invoice creation and payment collection make your advisory's life easier — with a secure payment system and multi-currency support.</p>
        <ul class="ticks">
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Create invoices in seconds from any case</li>
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Collect payments through a secure payment system</li>
          <li><span class="tk"><svg class="icon"><use href="#i-check"/></svg></span>Multi-currency for international clients</li>
        </ul>
        <div class="zz-actions">
          <a class="btn btn-primary" href="{{ route('get_demo') }}">Get A Demo <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
          <a class="link-arrow" href="{{ route('features') }}">Read more <svg class="icon"><use href="#i-arrow"/></svg></a>
        </div>
      </div>
      <div class="zz-media" data-reveal="left">
        <div class="frame" aria-hidden="true">
          <div class="frame-head"><b>Invoice INV-1042</b><span class="tag-soon"><svg class="icon" style="width:13px;height:13px"><use href="#i-bell"/></svg>Auto-reminders</span></div>
          <div class="invoice">
            <div class="inv-top"><span><b>Daniel O.</b><small>Work Permit · Canada</small></span><span class="st ok">Paid</span></div>
            <div class="inv-lines">
              <div class="inv-line"><span>Consultation &amp; eligibility review</span><span>$120.00</span></div>
              <div class="inv-line"><span>Application preparation</span><span>$230.00</span></div>
              <div class="inv-line"><span>Total</span><span>$350.00</span></div>
            </div>
          </div>
          <div class="currencies" id="currencies"><span class="on">USD</span><span>GBP</span><span>CAD</span><span>AUD</span><span>EUR</span><span>INR</span></div>
        </div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     WHY ADWISERI — DARK
     ========================================================= --}}
@if($showHomepageSection('why_adwiseri'))
<section class="section why" id="why" aria-labelledby="why-adwiseri-heading">
  <div class="wrap">
    <div class="section-head">
      <span class="ds-eyebrow" data-reveal>// {{ $nextNo() }} · Why adwiseri?</span>
      <h2 class="ds-h2" id="why-adwiseri-heading" data-reveal style="--d:.06s">Built on trust, security and real support</h2>
      <p class="ds-lead" data-reveal style="--d:.12s">We put our users' priorities ahead of our own — so your consultancy can grow with confidence.</p>
    </div>
    <div class="why-grid">
      <div class="why-card" data-reveal><span class="wi"><svg class="icon"><use href="#i-shield"/></svg></span><h3>Data Security</h3><p>Enterprise-grade protection using the best in the industry tools &amp; solutions for every case, client and document.</p></div>
      <div class="why-card" data-reveal style="--d:.08s"><span class="wi"><svg class="icon"><use href="#i-headset"/></svg></span><h3>Dedicated Support</h3><p>Our Support team is available Monday – Friday, 10am – 6pm GMT. Email us at <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.</p></div>
      <div class="why-card" data-reveal style="--d:.16s"><span class="wi"><svg class="icon"><use href="#i-card"/></svg></span><h3>Secure Payment System</h3><p>Built-in invoicing and secure payment collection, with multi-currency support for your international clients.</p></div>
      <div class="why-card" data-reveal style="--d:.24s"><span class="wi"><svg class="icon"><use href="#i-globe"/></svg></span><h3>Available in Multiple Regions</h3><p>Serving advisors across the USA, UK, Australia, Canada and beyond — on any device, anytime, anywhere.</p></div>
    </div>
    <div class="stats" data-reveal>
      {{-- Final values are rendered here; adwiseri-site.js only animates them up when motion is allowed. --}}
      <div class="stat"><b><span data-count="{{ $featureList->count() }}">{{ $featureList->count() }}</span></b><span>Core features in one platform</span></div>
      <div class="stat"><b><span data-count="{{ count($useCases) }}">{{ count($useCases) }}</span></b><span>Practice types supported</span></div>
      @if($largestPlan)
        @php
            $capacity = (int) $largestPlan->client_limit;
            $capacityLabel = $capacity >= 1000 ? rtrim(rtrim(number_format($capacity / 1000, 1), '0'), '.') . 'k' : (string) $capacity;
        @endphp
        <div class="stat"><b><span data-count="{{ $capacity }}" @if($capacity >= 1000) data-format="k" @endif>{{ $capacityLabel }}</span></b><span>Client capacity on {{ $largestPlan->plan_name }}</span></div>
      @else
        <div class="stat"><b>∞</b><span>Unlimited data</span></div>
      @endif
      @if($freePlan)
        <div class="stat"><b>$<span data-count="0">0</span></b><span>To start — {{ (int) $freePlan->validity }}-day {{ $freePlan->plan_name }} plan</span></div>
      @elseif($cheapestPaid)
        <div class="stat"><b>$<span data-count="{{ (int) $cheapestPaid->price_per_year }}">{{ number_format((int) $cheapestPaid->price_per_year) }}</span></b><span>Plans from, per year</span></div>
      @else
        <div class="stat"><b>24/5</b><span>Dedicated support</span></div>
      @endif
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     USE CASES
     ========================================================= --}}
@if($showHomepageSection('use_cases') && count($useCases))
<section class="section" id="use-cases">
  <div class="wrap">
    <div class="section-head">
      <span class="ds-eyebrow" data-reveal>// {{ $nextNo() }} · Use Cases</span>
      <h2 class="ds-h2" data-reveal style="--d:.06s">Made for advisors like you</h2>
      <p class="ds-lead" data-reveal style="--d:.12s">Whether you're a solo advisor or an immigration law firm, adwiseri adapts to the way your practice works.</p>
      <div class="uc-filter" role="tablist" aria-label="Filter use cases" data-reveal style="--d:.18s">
        <button type="button" class="on" role="tab" aria-selected="true" data-filter="all">All</button>
        <button type="button" role="tab" aria-selected="false" data-filter="practice">By practice type</button>
        <button type="button" role="tab" aria-selected="false" data-filter="region">By region</button>
      </div>
    </div>
    <div class="uc-grid" id="ucGrid">
      @foreach($useCases as $i => $uc)
        <a class="uc" data-type="{{ $uc['type'] }}" href="{{ route('get_demo') }}" title="{{ $uc['definition'] }}" data-reveal style="--d:{{ number_format(($i % 4) * 0.05, 2) }}s">
          @if(!empty($uc['code']))
            <span class="ui cc">{{ $uc['code'] }}</span>
          @else
            <span class="ui"><svg class="icon"><use href="#i-{{ $uc['icon'] ?? 'globe' }}"/></svg></span>
          @endif
          <b>{{ $uc['name'] }}</b>
          <span class="go">Book a demo <svg class="icon"><use href="#i-arrow"/></svg></span>
        </a>
      @endforeach
      <a class="uc cta" data-type="all" href="{{ route('contactus') }}" data-reveal style="--d:.15s"><span class="ui"><svg class="icon"><use href="#i-message"/></svg></span><b>Don't see your practice?</b><p>Tell us how you work and we'll show you how adwiseri fits.</p><span class="go">Contact us <svg class="icon"><use href="#i-arrow"/></svg></span></a>
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     PRICING (Admin → Subscriptions)
     ========================================================= --}}
@if($showHomepageSection('price_plans') && $plans->count())
@php
    // "Most Popular" is hidden until there are real sign-up numbers; set to true to show it again.
    $showPopularBadge = false;
    $visiblePlans = $plans->filter(function ($plan) use ($myplan) {
        if (empty($myplan)) { return true; }
        if ($plan->plan_name == 'Free') { return false; }
        $isCurrentPlan = $plan->plan_name === $myplan->plan_name;
        $isHigherPlan = \App\Services\SubscriptionTermPricing::isUpgradePlan($myplan, $plan);
        return $isCurrentPlan || $isHigherPlan;
    })->values();
    $planCount = $visiblePlans->count();
@endphp
<section class="section pricing" id="pricing">
  <div class="wrap">
    <div class="section-head">
      <span class="ds-eyebrow" data-reveal>// {{ $nextNo() }} · Price Plans</span>
      <h2 class="ds-h2" data-reveal style="--d:.06s">Simple, fixed pricing. <span class="ds-grad-text">No surprises.</span></h2>
      <p class="ds-lead" data-reveal style="--d:.12s">Choose the plan that fits your practice — upgrade anytime.</p>
    </div>

    <div class="plans-wrap">
      <div class="plans {{ $planCount <= 3 ? 'is-few' : '' }}" id="plans" @if($planCount > 3) style="grid-template-columns:repeat({{ $planCount }},minmax(0,1fr))" @endif>
        @foreach($visiblePlans as $i => $plan)
          @php
              $free = $isFree($plan);
              $isPopular = $showPopularBadge && (stripos((string) $plan->plan_name, '+') !== false || strcasecmp((string) $plan->plan_name, 'Adwiseri+') === 0);
              $price = $plan->price_per_year + 0;
              $blurb = $planBlurbs[strtolower(trim((string) $plan->plan_name))]
                  ?? ('Up to ' . $num($plan->client_limit) . ' clients and ' . $num($plan->no_of_users) . ' user licence' . ((int) $plan->no_of_users === 1 ? '' : 's') . '.');
              $emails = (int) ($plan->email_broadcast_limit ?? 0) > 0 ? number_format((int) $plan->email_broadcast_limit) . ' / Year' : '0';
              $inclusions = [
                  'Invoicing' => $plan->invoicing == 'Yes',
                  'Analytics' => $plan->analytics === 'Yes',
                  'Multi-Device' => $plan->multi_device_support === 'Yes',
                  'Secure Environment' => $plan->secure_environment === 'Yes',
                  'Multi-Currency' => $plan->multi_currency_support === 'Yes',
              ];
              $isCurrentPlan = isset($myplan) && $myplan && $plan->plan_name === $myplan->plan_name;
              $isHigherPlan = isset($myplan) && $myplan && \App\Services\SubscriptionTermPricing::isUpgradePlan($myplan, $plan);
              $btnClass = $isPopular ? 'btn btn-primary' : 'btn btn-ghost';
          @endphp
          <article class="plan {{ $isPopular ? 'popular' : '' }}" data-reveal style="--d:{{ number_format($i * 0.06, 2) }}s">
            @if($isPopular)<span class="ribbon">★ MOST POPULAR</span>@endif
            <h3>{{ $plan->plan_name }}</h3>
            <p class="for">{{ $blurb }}</p>
            <div class="price"><b>${{ $price }}</b><span>/ {{ $free ? ((int) $plan->validity . ' days') : 'year' }}</span></div>
            <div class="permo">
              @if($free)
                Free for your first {{ (int) $plan->validity }} days
              @elseif($price > 0)
                ≈ ${{ number_format($price / 12, 2) }}/mo, billed yearly
              @endif
            </div>

            {{-- Same actions as before the redesign: guests subscribe; signed-in users see renew / upgrade / current-plan states. --}}
            @if(empty($myplan))
              <a class="{{ $btnClass }}" href="{{ route('user_register_plan', $plan->plan_name) }}">{{ $free ? 'Start Free' : 'Subscribe' }}</a>
            @elseif(isset($user))
              @if($isCurrentPlan)
                @if(isset($subscriber) && \App\Services\SubscriptionTermPricing::isSubscriptionLapsed($subscriber))
                  <button type="button" class="{{ $btnClass }} is-current" disabled>Lapsed</button>
                @elseif((new DateTime("now")) > (new DateTime($subscriber->membership_expiry_date)) || ($user->membership_type != "Trial" && \App\Services\SubscriptionTermPricing::isRenewalWindowOpen($subscriber)))
                  <a class="btn btn-primary" @if($user->user_type == "Subscriber") href="{{ route('upgrade_membership', $plan->plan_name) }}" @else href="javascript:void(0)" @endif>Renew Subscription</a>
                @elseif($user->membership_type == "Trial")
                  <a class="{{ $btnClass }}" @if($user->user_type == "Subscriber") href="{{ route('upgrade_membership', $plan->plan_name) }}" @else href="javascript:void(0)" @endif>Active</a>
                @else
                  <button type="button" class="{{ $btnClass }} is-current" disabled>Current Plan</button>
                @endif
              @elseif($isHigherPlan)
                <a class="btn btn-primary" @if($user->user_type == "Subscriber") href="{{ route('upgrade_membership', $plan->plan_name) }}" @else href="javascript:void(0)" @endif>Upgrade <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
              @endif
            @else
              <a class="{{ $btnClass }}" href="{{ route('user_register_plan', $plan->plan_name) }}">Subscribe</a>
            @endif

            <ul class="limits">
              <li><span>Client limit</span><b>{{ $num($plan->client_limit) }}</b></li>
              <li><span>User licence</span><b>{{ $num($plan->no_of_users) }}</b></li>
              <li><span>Messages</span><b>{{ $num($plan->messaging) }}</b></li>
              <li><span>Emails</span><b>{{ $emails }}</b></li>
              <li><span>Reports</span><b>{{ $plan->reports }}</b></li>
            </ul>
            <ul class="incl">
              @foreach($inclusions as $label => $included)
                <li class="{{ $included ? '' : 'no' }}"><svg class="icon"><use href="#i-{{ $included ? 'check' : 'x' }}"/></svg>{{ $label }}<span class="sr-only">{{ $included ? ' — included' : ' — not included' }}</span></li>
              @endforeach
            </ul>
          </article>
        @endforeach
      </div>
      @if($planCount > 3)
      <div class="plans-nav">
        <button type="button" id="plansPrev" aria-label="Previous plans"><svg class="icon"><use href="#i-left"/></svg></button>
        <button type="button" id="plansNext" aria-label="Next plans"><svg class="icon"><use href="#i-arrow"/></svg></button>
      </div>
      @endif
    </div>

    <div class="pricing-note" data-reveal>
      <span><svg class="icon"><use href="#i-tag"/></svg>Flat yearly pricing</span>
      <span><svg class="icon"><use href="#i-infinity"/></svg>Unlimited data</span>
      <span><svg class="icon"><use href="#i-zap"/></svg>Instant activation</span>
      <a class="link-arrow" href="{{ route('membership') }}">Compare plans in detail <svg class="icon"><use href="#i-arrow"/></svg></a>
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     DISCOUNTS & OFFERS (Admin → Discounts & Offers)
     ========================================================= --}}
@if($showHomepageSection('discounts_offers') && $landingPromoSettings && ($landingDiscountItems->count() || $landingOfferItems->count()))
<section class="section" id="offers" aria-labelledby="landing-promo-heading" style="padding-top:{{ $showHomepageSection('price_plans') ? '0' : '120px' }}">
  <div class="wrap">
    <div class="section-head">
      <span class="ds-eyebrow" data-reveal>Exclusive deals</span>
      <h2 class="ds-h2" id="landing-promo-heading" data-reveal style="--d:.06s">{{ $landingPromoSettings->heading }}</h2>
    </div>
    <div class="promo-grid">
      @if($landingDiscountItems->count())
      <article class="promo" data-reveal>
        <div class="promo-head">
          <span class="pi"><svg class="icon"><use href="#i-percent"/></svg></span>
          <span><b>Discounts</b><small>Multi-year subscription savings</small></span>
        </div>
        <ul class="promo-rows">
          @foreach($landingDiscountItems as $item)
          <li class="promo-row">
            <span class="amt">{{ $item->benefit }}</span>
            <span class="det"><small>Subscription term</small>{{ $item->detail }}</span>
          </li>
          @endforeach
        </ul>
        @if($landingPromoSettings->discount_note)
        <p class="promo-note"><strong>*</strong> {{ $landingPromoSettings->discount_note }}</p>
        @endif
      </article>
      @endif

      @if($landingOfferItems->count())
      <article class="promo" data-reveal style="--d:.08s">
        <div class="promo-head">
          <span class="pi"><svg class="icon"><use href="#i-wallet"/></svg></span>
          <span><b>Offers</b><small>Cashback on selected plans</small></span>
        </div>
        <ul class="promo-rows">
          @foreach($landingOfferItems as $item)
          <li class="promo-row">
            <span class="amt">{{ $item->benefit }}</span>
            <span class="det"><small>Plan</small>{{ $item->detail }}</span>
          </li>
          @endforeach
        </ul>
        @if($landingPromoSettings->offer_note)
        <p class="promo-note"><strong>*</strong> {{ $landingPromoSettings->offer_note }}</p>
        @endif
      </article>
      @endif
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     AFFILIATE PROGRAM
     ========================================================= --}}
@if($showHomepageSection('affiliates'))
<section class="section" id="affiliates" style="padding-bottom:40px">
  <div class="wrap">
    <div class="aff" data-reveal="zoom">
      <div class="aff-copy">
        <span class="ds-eyebrow">// {{ $nextNo() }} · Affiliate Program</span>
        <h2>Want to earn extra from <span class="ds-grad-text">referrals?</span></h2>
        <p class="ds-lead">Refer consultancies you know and earn commissions when they subscribe.</p>
        <div class="aff-actions">
          <a class="btn btn-primary" href="{{ route('Affiliates.create') }}">Join Affiliate Program <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
          <a class="link-arrow" href="{{ route('affiliate.createLogin') }}">Affiliate login <svg class="icon"><use href="#i-arrow"/></svg></a>
        </div>
      </div>
      <div class="aff-visual" aria-hidden="true">
        <div class="ref"><span class="step">1</span><span><b>Join the program</b><small>Register as an adwiseri affiliate</small></span></div>
        <div class="ref"><span class="step">2</span><span><b>Refer consultancies</b><small>Share adwiseri with advisors you know</small></span></div>
        <div class="ref"><span class="step">3</span><span><b>Earn commissions</b><small>When your referrals subscribe</small></span></div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     FAQ (Admin → FAQs; falls back to plan-derived answers)
     ========================================================= --}}
@if($showHomepageSection('faq'))
@php
    if ($faqList->count()) {
        $faqItems = $faqList->map(function ($f) { return ['q' => $f->question, 'a' => $f->answer]; })->all();
    } else {
        $faqItems = [];
        if ($freePlan) {
            $faqItems[] = ['q' => 'Is there a free plan?', 'a' => 'Yes. The ' . $freePlan->plan_name . ' plan costs $0 for ' . (int) $freePlan->validity . ' days and includes up to ' . $num($freePlan->client_limit) . ' clients, ' . $num($freePlan->no_of_users) . ' user licence' . ((int) $freePlan->no_of_users === 1 ? '' : 's') . ', ' . $num($freePlan->messaging) . ' messages and ' . strtolower((string) $freePlan->reports) . ' reports.'];
        }
        $faqItems[] = ['q' => 'Will I receive extra invoices for add-on features?', 'a' => "No. adwiseri follows a flat pricing model, so you know exactly what you'll pay over the year. Most of our plans are inclusive of all needed features — there are no surprise invoices for add-ons."];
        $faqItems[] = ['q' => 'Is there a limit on how much data I can store?', 'a' => 'No cap has been imposed on data usage. You pay one-time fixed fees per year and get unlimited data as an in-built facility. Plans differ by client limit, user licences, messages and report level.'];
        $faqItems[] = ['q' => 'Can my whole team work on cases together?', 'a' => 'Yes. With Multi-User Access your registered users can work anytime, anywhere, and cases can be assigned to one or multiple advisors/consultants.' . ($userCounts->count() ? ' User licences range from ' . $userCounts->min() . ' to ' . number_format($userCounts->max()) . ', depending on the plan.' : '')];
        $faqItems[] = ['q' => 'Which consultancies is adwiseri built for?', 'a' => 'adwiseri serves ' . collect($useCases)->pluck('name')->implode(', ') . '.'];
        $faqItems[] = ['q' => 'Can I upgrade my plan later?', 'a' => 'Yes — choose the plan that fits your practice today and upgrade anytime as your client base and team grow.'];
    }
@endphp
<section class="section" id="faq">
  <div class="wrap faq-wrap">
    <div class="faq-side">
      <span class="ds-eyebrow" data-reveal>// {{ $nextNo() }} · FAQ</span>
      <h2 class="ds-h2" data-reveal style="--d:.06s">Questions, answered</h2>
      <p class="ds-lead" data-reveal style="--d:.12s">Everything you need to know before getting started with adwiseri.</p>
      <div class="support-card" data-reveal style="--d:.18s">
        <span class="si"><svg class="icon"><use href="#i-headset"/></svg></span>
        <div><b>Still have questions?</b><p>Our Support team is available Monday – Friday, 10am – 6pm GMT. Email us at <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> or <a href="{{ route('contactus') }}">contact us</a>.</p></div>
      </div>
    </div>
    <div class="faq" id="faqList">
      @foreach($faqItems as $i => $item)
        <div class="qa {{ $i === 0 ? 'open' : '' }}" data-reveal style="--d:{{ number_format(min($i, 6) * 0.05, 2) }}s">
          <button type="button" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faq-a-{{ $i }}">{{ $item['q'] }} <span class="pm"><svg class="icon"><use href="#i-plus"/></svg></span></button>
          <div class="ans" id="faq-a-{{ $i }}"><div><p>{{ $item['a'] }}</p></div></div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- =========================================================
     FINAL CTA + NEWSLETTER (posts to email_subscription)
     ========================================================= --}}
@if($showHomepageSection('get_started'))
<section class="wrap" id="get-started">
  <div class="cta-band" data-reveal="zoom">
    <span class="ring" aria-hidden="true"></span><span class="ring r2" aria-hidden="true"></span><span class="ring r3" aria-hidden="true"></span>
    <span class="ds-eyebrow" style="color:#D9D6FF;justify-content:center">Ready when you are</span>
    <h2 style="margin-top:14px">Run your entire consultancy from one secure platform</h2>
    <p>See how adwiseri brings your cases, documents, team, reports and invoices together — with a personal walkthrough from our team.</p>
    <div class="hero-ctas">
      <a class="btn btn-light" href="{{ route('get_demo') }}">Get A Demo <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
      <a class="btn btn-outline-light" href="{{ $showHomepageSection('price_plans') ? '#pricing' : route('membership') }}">View price plans</a>
    </div>

    <form class="newsletter" id="homepage-newsletter-form" action="{{ route('email_subscription') }}" method="POST" novalidate
          @if($errors->has('email')) data-error="{{ $errors->first('email') }}" @endif>
      @csrf
      <label for="homepage-newsletter-email">Subscribe to our newsletter</label>
      <small>Get the latest news, updates and offers from adwiseri.</small>
      <div class="nl-row" id="nlRow">
        <input id="homepage-newsletter-email" type="email" name="email" placeholder="Enter your email address" autocomplete="email" inputmode="email" maxlength="255" value="{{ old('email') }}">
        <button class="btn btn-light" type="submit" id="homepage-newsletter-btn">Subscribe</button>
      </div>
    </form>
  </div>
</section>
@endif

</main>

@if (session()->has('subscribed') || session()->has('subscription_error'))
@push('scripts')
<script>
  (function () {
    var ok = @json(session('subscribed'));
    var err = @json(session('subscription_error'));
    if (ok) {
      if (window.AdwiseriAlert) { AdwiseriAlert.success(ok); }
      else if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'success', title: 'Success', text: ok }); }
    } else if (err) {
      if (window.AdwiseriAlert && AdwiseriAlert.oops) { AdwiseriAlert.oops(err); }
      else if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'warning', title: 'Oops!', text: err }); }
    }
  })();
</script>
@endpush
@endif
@endsection
