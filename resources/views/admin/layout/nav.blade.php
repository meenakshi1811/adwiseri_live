<!---Navbar-->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
  /* Sidebar icon wrapper (if using a span or i tag) */
.sidebar-menu-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #695EEE;
    color: white;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    margin-right: 10px;
    font-size: 14px;
    margin-top:0px;
}

/* Optional: make icons look consistent if they're inside <i> or <img> */
.sidebar-menu-icon i,
.sidebar-menu-icon img {
    max-width: 16px;
    max-height: 16px;
}

/* Align the entire menu item */
.sidebar-menu-item {
    display: flex;
    align-items: center;
    padding: 6px 10px;
    font-weight: 500;
    border-radius: 6px;
    background-color: white;
    margin-bottom: 6px;
    color: #333;
    text-decoration: none;
    transition: background 0.2s;
    
}

/* Hover effect */
.sidebar-menu-item:hover {
    background-color: #f0f0ff;
    text-decoration: none;
}

</style>


  {{-- <div class="mainav-head"> --}}
    {{-- <p class=" help-text"><img src="{{ asset('web_assets/images/callus.png') }}" width="15" height="15" alt="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Helpline number <strong>+91-9137196317 </strong> </p> --}}
    <!-- <a href="{{ route('contactus') }}" class="contact-texthead"><img src="{{ asset('web_assets/images/contactus.png') }}" width="15" height="15" alt=""> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Contact Us</a> -->
{{-- </div> --}}
<nav class="navbar navbar-expand-lg navbar-light" style="background:#695EEE;">
    <div class="container-fluid">
      <a class="navbar-brand text-white" href="{{ route('admin') }}"><img class="logo-fix" width="200" src="{{ asset('web_assets/images/Style2.png') }}" /></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-1 mb-lg-0">

          <!-- <li class="nav-item">
            <a href="{{ ('contactus') }}" class="contact-texthead li-contact"><img src="{{ asset('web_assets/images/contactus.png') }}" width="15" height="15" alt=""> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Contact Us</a>
          </li> -->
        </ul>
        <form class="log-btn" style="margin-top:auto;margin-bottom:auto;">
          @if(isset($user))
          @include('partials.topbar_notifications')
          <a href="{{ route('admin_profile') }}" class="btn btn-outline-success login-btn"><img src="{{ asset('web_assets/images/login.png') }}" width="20"
            height="20" alt=""> {{ $user->name }}</a>
          <a href="{{ route('logout') }}" class="btn btn-outline-success demo-btn"><img src="{{ asset('web_assets/images/logout.png') }}" width="20"
            height="20" alt=""> Logout</a>
          @else
          <a href="{{ route('login') }}" class="btn btn-outline-success login-btn"><img src="{{ asset('web_assets/images/login.png') }}" width="20"
            height="20" alt=""> Login</a>

          @endif
        </form>
      </div>
    </div>
  </nav>
  <!---Navbar END-->
  <div class="container-fluid dashboard-box mt-3 mb-3 px-lg-5">

    <div class="row client-row{{ ($page ?? '') === 'dashboard' ? ' dashboard-equal-cols' : '' }}">
        <div class="col-lg-2 column-dashbox">
            <div class="dash-box">
                @php
                    $isAdminStaff = (int) ($user->is_support ?? 0) === 1;
                @endphp
                {{-- Admin Staff modules: Subscribers, Activity Logs, Demo Requests only --}}
                @if(!$isAdminStaff)
                <a href="{{ route('admin_dashboard') }}" @if($page == "dashboard") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                    <i class="fas fa-home"></i> <!-- Font Awesome icon -->
                                </span>
                                Dashboard
                            </a>
                            <div class="sidebar-menu-item" id="manage_btn">
                                <a style="cursor: pointer;">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-toolbox"></i>
                                </span>Admin Panel</a>
                            </div>
                            <div class="" id="manage_box" @if($page == "contactus" or $page == "about_adwiseri" or $page == "features" or $page == "membership" or $page == "landing_discounts_offers" or $page == "demo_request" or $page == "feedbacks" or $page == "admin_staff") style="margin-left:25px;flex-direction: column;display:flex;" @else style="margin-left:25px;flex-direction: column;display:none;" @endif>
                                
                                    <a href="{{ route('manage_contactus') }}" @if($page == "contactus") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                        <i class="fa-solid fa-address-book"></i>
                                        </span>
                                        Contact Us
                                    </a>
                                
                                
                                    <a href="{{ route('manage_about_adwiseri') }}" @if($page == "about_adwiseri") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                            <i class="fas fa-info-circle"></i> <!-- Font Awesome icon -->
                                        </span>
                                        About adwiseri
                                    </a>
                                
                                
                                    <a href="{{ route('manage_features') }}" @if($page == "features") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                            <i class="fas fa-cogs"></i> <!-- Font Awesome icon -->
                                        </span>
                                        Features
                                    </a>
                                
                                
                                    <a href="{{ route('manage_membership') }}" @if($page == "membership") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                            <i class="fas fa-credit-card"></i> <!-- Font Awesome icon -->
                                        </span>
                                        Subscriptions
                                    </a>

                                    <a href="{{ route('manage_landing_discounts_offers') }}" @if($page == "landing_discounts_offers") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                            <i class="fas fa-tags"></i>
                                        </span>
                                        Discounts &amp; Offers
                                    </a>

                                    <a href="{{ route('manage_homepage_sections') }}" @if($page == "homepage_sections") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                            <i class="fas fa-home"></i>
                                        </span>
                                        Homepage Sections
                                    </a>
                                
                                
                                    <a href="{{ route('demo_requests') }}" @if($page == "demo_request") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                            <i class="fas fa-video"></i> <!-- Font Awesome icon -->
                                        </span>
                                        Demo Requests
                                    </a>
                                
                                
                                    <a href="{{ route('admin_feedback') }}" @if($page == "feedbacks") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                            <i class="fas fa-comments"></i> <!-- Font Awesome icon -->
                                        </span>
                                        Feedbacks
                                    </a>
                                
                                
                                    <a href="{{ route('admin_staff') }}" @if($page == "admin_staff") style="font-weight:700;background-color:#695EEE;color:white;" @endif class="sidebar-menu-item">
                                        <span class="sidebar-menu-icon">
                                            <i class="fas fa-user-tie"></i> <!-- Font Awesome icon -->
                                        </span>
                                        Admin Staff
                                    </a>
                                
                            </div>
                @endif

                <a href="{{ route('subscribers') }}" @if($page == "subscriber") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-subscript"></i> <!-- Font Awesome icon -->
                                </span>
                                Subscribers
                            </a>

                @if($isAdminStaff)
                <a href="{{ route('activity_log') }}" @if($page == "activity_log") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-person-circle-exclamation"></i>
                                </span>
                                Activity Logs
                            </a>
                <a href="{{ route('demo_requests') }}" @if($page == "demo_request") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                    <i class="fas fa-video"></i>
                                </span>
                                Demo Requests
                            </a>
                @endif

                @if(!$isAdminStaff)
                <a href="{{ route('email_subscribers') }}" @if($page == "email_subscribers") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-envelope"></i>
                                </span>
                                Email Subscribers
                            </a>
                <a href="{{ route('manage_clients') }}" @if($page == "clients") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fas fa-users"></i> <!-- Font Awesome icon -->
                                </span>
                                Clients
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/admin_client.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('manage_clients') }}" @if($page == "clients") style="font-weight:700;" @endif>Clients</a>
                </div> -->
                <a href="{{ route('manage_applications') }}" @if($page == "applications") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-window-restore"></i> <!-- Font Awesome icon -->
                                </span>
                                Applications
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/communication.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('manage_applications') }}" @if($page == "applications") style="font-weight:700;" @endif>Applications</a>
                </div> -->
                {{-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/user.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('manage_dependents') }}" @if($page == "applications") style="font-weight:700;" @endif>Dependents</a>
                </div> --}}
                <a href="{{ route('manage_users') }}" @if($page == "users") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fas fa-user"></i> <!-- Font Awesome icon -->
                                </span>
                                Users &nbsp;(Staff)
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/users.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('manage_users') }}" @if($page == "users") style="font-weight:700;" @endif> Users &nbsp;(Staff)</a>
                </div> -->
                <a href="{{ route('manage_invoices') }}" @if($page == "invoices") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fas fa-file"></i> <!-- Font Awesome icon -->
                                </span>
                                Invoices
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/invoices.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('manage_invoices') }}" @if($page == "invoices") style="font-weight:700;" @endif>Invoices</a>
                </div> -->
                <a href="{{ route('admin_messaging') }}" @if($page == "communication") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-comment"></i> <!-- Font Awesome icon -->
                                </span>
                                Communications
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/applications.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('admin_messaging') }}" @if($page == "communication") style="font-weight:700;" @endif>Communications</a>
                </div> -->
                <a href="{{ route('payments') }}" @if($page == "payments") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fas fa-dollar"></i> <!-- Font Awesome icon -->
                                </span>
                                Payments
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/payments.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('payments') }}" @if($page == "payments") style="font-weight:700;" @endif>Payments</a>
                </div> -->
                <a href="{{ route('admin_referral') }}" @if($page == "wallet") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-asterisk"></i> <!-- Font Awesome icon -->
                                </span>
                                Referrals & Wallets
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/documents.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('admin_referral') }}" @if($page == "wallet") style="font-weight:700;" @endif>Referrals & Wallets</a>
                </div> -->
                <a href="{{ route('analytics') }}" @if($page == "analytics") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-chart-simple"></i> <!-- Font Awesome icon -->
                                </span>
                                Analytics
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/analytics.png') }}" width="30" height="30" alt="">
                    <a href="{{route('analytics')}}" @if($page == "analytics") style="font-weight:700;" @endif >Analytics</a>
                </div> -->
                <a href="{{ route('reports') }}" @if($page == "reports") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-flag"></i> <!-- Font Awesome icon -->
                                </span>
                                Reports
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/reports.png') }}" width="30" height="30" alt="">
                    <a href="{{ route('reports') }}" @if($page == "reports") style="font-weight:700;" @endif>Reports</a>
                </div> -->
                <a href="{{ route('affiliates') }}" @if($page == "affiliates") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-bell"></i> <!-- Font Awesome icon -->
                                </span>
                                Affiliates
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/affiliates.png') }}" width="30" height="30" alt="">
                    <a href="{{route('affiliates')}}"  @if($page == "affiliates") style="font-weight:700;" @endif>Affiliates</a>
                </div> -->
                <a href="{{ route('activity_log') }}" @if($page == "activity_log") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-person-circle-exclamation"></i>
                                </span>
                                Activity Logs
                            </a>
                <a href="{{ route('error_log') }}" @if($page == "error_log") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                </span>
                                Error Log
                            </a>
                <!-- <div class="dashbox-btn d-flex">
                    <img src="{{ asset('web_assets/images/activity_log.png') }}" width="30" height="30">
                    <a href="{{ route('activity_log') }}" @if($page == "activity_log") style="font-weight:700;" @endif>Activity Logs</a>
                </div> -->
                <a href="{{ route('settings') }}" @if($page == "settings") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-gear"></i> <!-- Font Awesome icon -->
                                </span>
                                Settings
                            </a>
                <a href="{{ route('manage_support') }}" @if($page == "support") style="font-weight:700;background-color:#695EEE;color:white" @endif class="sidebar-menu-item">
                                <span class="sidebar-menu-icon">
                                <i class="fa-solid fa-circle-info"></i>
                                </span>
                                Support
                            </a>
                @endif
            </div>
        </div>
