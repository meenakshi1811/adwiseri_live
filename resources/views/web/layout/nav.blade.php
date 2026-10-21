<!---Navbar-->


  {{-- <div class="mainav-head">
    <p class=" help-text"><img src="{{ asset('web_assets/images/callus.png') }}" width="15" height="15" alt="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Helpline number <strong>+91-9137196317</strong> </p>
    <a href="{{ route('contactus') }}" class="contact-texthead"><img src="{{ asset('web_assets/images/contactus.png') }}" width="15" height="15" alt=""> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Contact Us</a>
</div> --}}
{{-- <nav class="navbar navbar-expand-lg navbar-light fixed-top" style="background:#695EEE;">
    <div class="container-fluid">
      <a class="navbar-brand text-white" href="{{ route('/') }}">adwiseri</a>

      <div class="collapse navbar-collapse" id="navbarSupportedContent" style="display:flex !important;">
        <ul class="navbar-nav me-auto mb-1 mb-lg-0">

          <li class="nav-item">
            <a class="nav-link active" href="{{ route('/') }}">Home</a>
          </li>

          <li class="nav-item">
            <a class="nav-link active" href="{{ route('aboutadvisori') }}">About Us</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('features') }}">Features</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('membership') }}">Pricing</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('/')}}#affiliates">Affiliates</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('contactus') }}">Contact Us</a>
          </li>
        </ul>
        <form class="log-btn">
          @if(isset($user))
          <a href="{{ route('userprofile') }}" class="btn btn-outline-success login-btn"><img src="{{ asset('web_assets/images/login.png') }}" width="20"
            height="20" alt=""> {{ $user->name }}</a>
          <a href="{{ route('logout') }}" class="btn btn-outline-success demo-btn"><img src="{{ asset('web_assets/images/logout.png') }}" width="20"
            height="20" alt=""> Logout</a>
          @else
          <a href="{{ route('login') }}" class="btn btn-outline-success login-btn"><img src="{{ asset('web_assets/images/login.png') }}" width="20"
            height="20" alt=""> Login</a>
            <a href="{{ route('get_demo') }}" class="btn btn-outline-success demo-btn">Get A Demo</a>
          @endif
        </form>
      </div>
    </div>
  </nav> --}}
  <!---Navbar END-->
  <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top" style="background:#695EEE;">
    <div class="container-fluid">
      <!-- Brand -->
      <a class="navbar-brand text-white" href="{{ route('/') }}"><img class="logo-fix navbar-logo" src="{{ asset('web_assets/images/Style2.png') }}" width="170" alt="adwiseri" /></a>

      <!-- Hamburger Menu Button -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Collapsible Content -->
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          {{-- <li class="nav-item">
            <a class="nav-link active text-white" href="{{ route('/') }}">Home</a>
          </li> --}}
          <li class="nav-item dropdown">
                <a class="nav-link active dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Use Cases</a>
                @php
                    // Subscriber categories, each with a one-line definition shown on hover.
                    $useCases = [
                        'General (All countries) Visa Consultants' => 'A Visa/Immigration Consultant, dealing in almost all countries and across all visa categories.',
                        'Study Abroad (Student) Visa Consultants' => 'A consultant helping students with university/college admissions and student visas for studying abroad.',
                        'Business / Work Visa Specialists' => 'A specialist in business, work-permit and employer-sponsored visas for professionals and skilled workers.',
                        'PR / Immigration Advisories' => 'An advisory focused on permanent residency (PR), settlement and long-term immigration pathways.',
                        'USA - Immigration Attorneys' => 'A licensed US immigration attorney handling US visas, green cards and related legal matters.',
                        'UK - OISC (IAA) Advisors' => 'An immigration adviser regulated by the OISC, now the Immigration Advice Authority (IAA), advising on UK visas and immigration.',
                        'Australia - MARA Advisors' => 'A registered migration agent (MARA) advising on Australian visas and migration.',
                        'Canada - ICCRC Advisors' => 'A licensed Canadian immigration consultant (RCIC), regulated by the ICCRC, now the CICC, advising on Canadian visas and immigration.',
                        'CBI - Citizenship By Investment Consultants' => 'A consultant helping investors obtain second citizenship or residency through government investment programs.',
                        'Travel Agents (Visit Visa Specialists)' => 'A travel agency that also arranges visit/tourist visas alongside travel bookings.',
                        'Immigration Law Firms' => 'A law firm whose lawyers provide legal advice and representation on immigration and visa matters.',
                    ];
                @endphp
                <ul class="dropdown-menu use-cases-menu">
                    @foreach ($useCases as $useCase => $definition)
                        <li><p class="dropdown-item mb-0" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $definition }}">{{ $useCase }}</p></li>
                    @endforeach
                </ul>
          </li>
          {{-- <li class="nav-item">
            <a class="nav-link active text-white" href="{{ route('aboutadvisori') }}">About Us</a>
          </li> --}}
          <li class="nav-item">
            <a class="nav-link active text-white" href="{{ route('features') }}">Features</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active text-white" href="{{ route('membership') }}">Pricing</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active text-white" href="{{ route('affiliate.createLogin')}}">Affiliates</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active text-white" href="{{ route('contactus') }}">Contact Us</a>
          </li>
        </ul>
        <!-- Login and Demo Buttons -->
        <form class="d-flex">
          @if(isset($user))
          <a href="{{ route('userprofile') }}" class="btn btn-outline-light login-btn me-2">
            <img src="{{ asset('web_assets/images/login.png') }}" width="20" height="20" alt=""> {{ $user->name }}
          </a>
          <a href="{{ route('logout') }}" class="btn btn-outline-light demo-btn">
            <img src="{{ asset('web_assets/images/logout.png') }}" width="20" height="20" alt=""> Logout
          </a>
          @else
          <a href="{{ route('login') }}" class="btn btn-outline-light login-btn me-2">
            <img src="{{ asset('web_assets/images/login.png') }}" width="20" height="20" alt=""> Login
          </a>
          <a href="{{ route('get_demo') }}" class="btn btn-outline-light demo-btn">Get A Demo</a>
          @endif
        </form>
      </div>
    </div>
  </nav>
  <!-- Navbar END -->

  <style>
    .use-case-tooltip .tooltip-inner {
      max-width: 280px;
      text-align: left;
      font-size: 13px;
    }
  </style>
  <script>
    // "Use Cases" menu: show each category's definition as a tooltip. Without Bootstrap's JS the
    // browser still shows the plain title text.
    document.addEventListener('DOMContentLoaded', function () {
      if (!window.bootstrap || !window.bootstrap.Tooltip) {
        return;
      }

      var tooltips = Array.prototype.map.call(
        document.querySelectorAll('.use-cases-menu [data-bs-toggle="tooltip"]'),
        function (item) {
          return new bootstrap.Tooltip(item, {
            container: 'body',
            trigger: 'hover focus',
            customClass: 'use-case-tooltip',
          });
        }
      );

      document.querySelectorAll('.use-cases-menu').forEach(function (menu) {
        var dropdown = menu.closest('.dropdown');
        if (dropdown) {
          dropdown.addEventListener('hide.bs.dropdown', function () {
            tooltips.forEach(function (tooltip) { tooltip.hide(); });
          });
        }
      });
    });
  </script>


