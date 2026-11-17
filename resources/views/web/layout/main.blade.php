@include('web.layout.header')
@php
    // Public pages use the fixed site header; signed-in pages use the app top bar (in normal flow).
    $usesPublicHeader = !(isset($user) && empty($registration_flow));
@endphp
@if($usesPublicHeader)
@include('web.layout.nav')
@else
@include('web.layout.auth_nav')
@endif
@if($usesPublicHeader)
{{-- Keeps every public page clear of the fixed header (the homepage hero handles its own offset). --}}
<div class="site-page{{ request()->is('/') ? ' is-home' : '' }}">
@endif
  @yield('main-section')
@if($usesPublicHeader)
</div>
@endif

@include('web.layout.footer')
