{{-- Public header: announcement bar, primary navigation with the Use Cases mega menu, mobile drawer. --}}
@php
    $isHome = request()->is('/');
    $homeUrl = route('/');
    // Features and Pricing always open their own pages, from every page (homepage included),
    // so the menu shows the same thing wherever it is clicked. Use Cases lives on the homepage only.
    $useCasesHref = $isHome ? '#use-cases' : $homeUrl . '#use-cases';
    $featuresHref = route('features');
    $pricingHref = route('membership');
    $navUseCases = config('use_cases', []);

    $navPlans = isset($price_plans) ? collect($price_plans) : \App\Models\Membership::orderBy('created_at', 'asc')->get();
    $navFreePlan = $navPlans->first(function ($p) {
        return (float) $p->price_per_year == 0.0 || in_array($p->plan_name, ['Free', 'Free Plan'], true);
    });
@endphp

<a class="skip-link" href="#main">Skip to content</a>

<div class="ds">
  <div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>

  <div class="site-top" id="siteTop">
    {{-- Promo bar on the homepage only: inner pages were laid out for a single-row header. --}}
    @if ($isHome)
    <div class="announce">
      <div class="wrap">
        <span class="dot" aria-hidden="true"></span>
        <span class="ann-long">Want to earn extra from referrals? Refer consultancies you know and earn commissions.</span>
        <span class="ann-short">Earn commissions on referrals.</span>
        <a href="{{ route('Affiliates.create') }}">Join Affiliate Program <svg class="icon" style="width:15px;height:15px"><use href="#i-arrow"/></svg></a>
      </div>
    </div>
    @endif

    <header class="site-header" id="siteHeader">
      <div class="wrap">
        <a class="brand" href="{{ $homeUrl }}" aria-label="adwiseri home">
          <img src="{{ asset('web_assets/images/Style2_violet.png') }}" alt="adwiseri" width="143" height="34" onerror="this.parentNode.classList.add('logo-fallback')">
          <span class="brand-text">adwiseri</span>
        </a>

        <nav class="site-nav" aria-label="Primary">
          <div class="dd" id="ucDropdown">
            <button type="button" class="dd-toggle" aria-expanded="false" aria-controls="megaMenu">Use Cases <svg class="icon"><use href="#i-chev"/></svg></button>
            <div class="mega" id="megaMenu">
              <div class="mega-col">
                <h6>By practice type</h6>
                @foreach ($navUseCases as $uc)
                  @if (($uc['type'] ?? '') === 'practice')
                    <a class="mega-link" href="{{ $useCasesHref }}" data-uc="practice" title="{{ $uc['definition'] }}"><span class="ic"><svg class="icon"><use href="#i-{{ $uc['icon'] ?? 'globe' }}"/></svg></span>{{ $uc['name'] }}</a>
                  @endif
                @endforeach
              </div>
              <div class="mega-col">
                <h6>By region &amp; regulation</h6>
                @foreach ($navUseCases as $uc)
                  @if (($uc['type'] ?? '') === 'region')
                    <a class="mega-link" href="{{ $useCasesHref }}" data-uc="region" title="{{ $uc['definition'] }}"><span class="ic cc">{{ $uc['code'] }}</span>{{ $uc['name'] }}</a>
                  @endif
                @endforeach
              </div>
              <div class="mega-feature">
                @if ($navFreePlan)
                  <span class="ds-eyebrow" style="color:#D9D6FF">{{ $navFreePlan->plan_name }} plan</span>
                  <strong>Try adwiseri free for {{ (int) $navFreePlan->validity }} days</strong>
                  <p>{{ $navFreePlan->client_limit }} clients, {{ $navFreePlan->no_of_users }} user license{{ (int) $navFreePlan->no_of_users === 1 ? '' : 's' }}, invoicing, analytics and a secure environment — at $0.</p>
                @else
                  <span class="ds-eyebrow" style="color:#D9D6FF">Price plans</span>
                  <strong>Simple, fixed yearly pricing</strong>
                  <p>No extra invoices for add-on features — choose the plan that fits your practice.</p>
                @endif
                <a class="link-arrow" href="{{ $pricingHref }}">See All Plans <svg class="icon"><use href="#i-arrow"/></svg></a>
              </div>
            </div>
          </div>
          <a href="{{ $featuresHref }}" @if(request()->routeIs('features')) class="active" @endif>Features</a>
          <a href="{{ $pricingHref }}" @if(request()->routeIs('membership')) class="active" @endif>Pricing</a>
          <a href="{{ route('affiliate.createLogin') }}">Affiliates</a>
          <a href="{{ route('contactus') }}" @if(request()->routeIs('contactus')) class="active" @endif>Contact Us</a>
        </nav>

        <div class="header-cta">
          @if (isset($user))
            <a class="login" href="{{ route('userprofile') }}" aria-label="My profile"><svg class="icon"><use href="#i-user"/></svg><span class="user-name">{{ $user->name }}</span></a>
            <a class="btn btn-primary btn-sm" href="{{ route('logout') }}">Logout <svg class="icon"><use href="#i-logout"/></svg></a>
          @else
            <a class="login" href="{{ route('login') }}"><svg class="icon"><use href="#i-login"/></svg><span class="login-text">Login</span></a>
            <a class="btn btn-primary btn-sm" href="{{ route('get_demo') }}">Get A Demo <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
          @endif
          <button type="button" class="burger" id="burger" aria-label="Open menu" aria-controls="drawer" aria-expanded="false"><svg class="icon"><use href="#i-menu"/></svg></button>
        </div>
      </div>
    </header>
  </div>

  {{-- Mobile drawer --}}
  <div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-backdrop" data-close></div>
    <aside class="drawer-panel" role="dialog" aria-modal="true" aria-label="Menu">
      <div class="drawer-top">
        <a class="brand" href="{{ $homeUrl }}"><img src="{{ asset('web_assets/images/Style2_violet.png') }}" alt="adwiseri" onerror="this.parentNode.classList.add('logo-fallback')"><span class="brand-text">adwiseri</span></a>
        <button type="button" class="burger" style="display:grid" data-close aria-label="Close menu"><svg class="icon"><use href="#i-x"/></svg></button>
      </div>
      <nav class="drawer-body" aria-label="Mobile">
        <div class="m-acc" id="mAcc">
          <button type="button" aria-expanded="false">Use Cases <svg class="icon"><use href="#i-chev"/></svg></button>
          <div class="m-acc-panel"><div>
            @foreach ($navUseCases as $uc)
              <a href="{{ $useCasesHref }}" data-close title="{{ $uc['definition'] }}">{{ $uc['name'] }}</a>
            @endforeach
          </div></div>
        </div>
        <a href="{{ $featuresHref }}" data-close>Features <svg class="icon"><use href="#i-arrow"/></svg></a>
        <a href="{{ $pricingHref }}" data-close>Pricing <svg class="icon"><use href="#i-arrow"/></svg></a>
        <a href="{{ route('affiliate.createLogin') }}">Affiliates <svg class="icon"><use href="#i-arrow"/></svg></a>
        <a href="{{ route('contactus') }}">Contact Us <svg class="icon"><use href="#i-arrow"/></svg></a>
        <a href="{{ $isHome ? '#faq' : route('faqs') }}" data-close>FAQ <svg class="icon"><use href="#i-arrow"/></svg></a>
      </nav>
      <div class="drawer-foot">
        @if (isset($user))
          <a class="btn btn-ghost" href="{{ route('userprofile') }}"><svg class="icon"><use href="#i-user"/></svg> {{ $user->name }}</a>
          <a class="btn btn-primary" href="{{ route('logout') }}">Logout <svg class="icon"><use href="#i-logout"/></svg></a>
        @else
          <a class="btn btn-primary" href="{{ route('get_demo') }}">Get A Demo <svg class="icon icon-arrow"><use href="#i-arrow"/></svg></a>
          <a class="btn btn-ghost" href="{{ route('login') }}"><svg class="icon"><use href="#i-login"/></svg> Login</a>
        @endif
      </div>
    </aside>
  </div>
</div>
<span id="main" tabindex="-1"></span>

<script>
  // Publish the fixed header's height before first paint so pages can keep
  // their content clear of it (adwiseri-site.js keeps it in sync on resize).
  (function () {
    var top = document.getElementById('siteTop');
    if (!top) { return; }
    document.documentElement.style.setProperty('--site-nav-height', top.offsetHeight + 'px');
    var ann = top.querySelector('.announce');
    top.style.setProperty('--ann-h', (ann ? ann.offsetHeight : 0) + 'px');
  })();
</script>
