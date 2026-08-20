@include('web.layout.header')
@if(isset($user) && empty($registration_flow))
@include('web.layout.auth_nav')
@else
@include('web.layout.nav')
@endif
  @yield('main-section')

@include('web.layout.footer')
