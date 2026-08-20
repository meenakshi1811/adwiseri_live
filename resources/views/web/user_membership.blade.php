@extends('web.layout.main')

@push('css')
<link rel="stylesheet" href="{{ asset('web_assets/css/subscription-module.css') }}">
@endpush

@section('main-section')
@php

use App\Models\UserRoles;
$client_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Clients')->first();
$application_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Applications')->first();
$communication_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Communication')->first();
$invoice_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Invoices')->first();
$payment_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Payments')->first();
$report_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Reports')->first();
$subscription_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Subscription')->first();
$setting_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Settings')->first();
$support_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Support')->first();

$subscriptionStart = !empty($subscriber->membership_start_date)
    ? \Carbon\Carbon::parse($subscriber->membership_start_date)->startOfDay()
    : null;
$planValidityDays = max(1, (int) (optional($myplan)->validity ?? 30));
$calculatedExpiryDate = $subscriptionStart
    ? $subscriptionStart->copy()->addDays($planValidityDays)
    : null;
$validityYears = (int) floor($planValidityDays / 365);

if ($validityYears >= 1 && ($planValidityDays % 365) === 0) {
    $validityLabel = $validityYears . ' ' . ($validityYears === 1 ? 'Year' : 'Years');
} else {
    $validityLabel = $planValidityDays . ' Days';
}

if ($validityYears === 1) {
    $plan_amount = \App\Services\SubscriptionTermPricing::calculate((float) (optional($myplan)->price_per_year ?? 0), 1);
} elseif (\App\Services\SubscriptionTermPricing::isAllowedDuration($validityYears)) {
    $plan_amount = \App\Services\SubscriptionTermPricing::calculate((float) (optional($myplan)->price_per_year ?? 0), $validityYears);
} else {
    $plan_amount = optional($myplan)->price_per_year ?? 0;
}

$subscriptionTerm = $subscriptionTerm ?? app(\App\Services\OfferBenefitService::class)->subscriptionTermForDisplay($subscriber, $myplan);
$formattedSubscriptionStart = $subscriptionTerm['start'];
$formattedSubscriptionEnd = $subscriptionTerm['end'];
$formattedHeaderExpiry = $subscriptionTerm['header_expires_on'];
$effectiveLimits = $effectiveLimits ?? app(\App\Services\OfferBenefitService::class)->effectiveLimitsForDisplay($subscriber);
@endphp

        <div class="col-lg-10 column-client">
            <div class="client-dashboard subscription-module">
                <div class="client-btn subscription-module-header d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3 px-2">
                    <div class="subscription-module-title-wrap">
                        <h3 class="text-primary m-0">Subscription :: {{ $myplan->plan_name }} Plan @if((new DateTime("now")) > (new DateTime($subscriber->membership_expiry_date))) Plan Expired @endif</h3>
                        <span class="subscription-expiry-line p-0 m-0">@if((new DateTime("now")) > (new DateTime($subscriber->membership_expiry_date))) Plan Expired @else Expires @endif on : {{ $formattedHeaderExpiry }}</span>
                    </div>
                    <div class="subscription-header-actions d-flex flex-wrap align-items-center justify-content-end gap-2">
                        @if($myplan->plan_name != "Enterprise")
                            <a @if($user->user_type == "Subscriber") href="{{ route('membership') }}" @else href="#" @endif class="btn btn-primary">Upgrade Plan</a>
                        @endif
                        <!-- <a @if($user->user_type == "Subscriber") href="{{ route('membership') }}" @else href="#" @endif class="btn btn-primary" style="height: fit-content">Renew Subscription</a> -->
                        @php
                            $showRenewButton = false;
                            $today = \Carbon\Carbon::now();
                            $expiryDate = \Carbon\Carbon::parse($subscriber->membership_expiry_date);
                            $isPaidPlan = strtoupper(trim($myplan->plan_name ?? '')) !== 'FREE';

                            $daysBeforeExpiry = $today->diffInDays($expiryDate, false); // Negative = expired

                            if ($isPaidPlan && $daysBeforeExpiry <= 60 && $daysBeforeExpiry >= -30) {
                                $showRenewButton = true;
                            }
                        @endphp

                        @if($user->user_type == "Subscriber")
                            @if($showRenewButton)
                                <a href="{{ route('membership_renewal', ['renew' => true]) }}" class="btn btn-primary">
                                    Renew Subscription
                                </a>
                            @endif
                        @endif

                        @if($user->user_type == "Subscriber")
                            <button type="button"
                                class="btn btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#subscriptionHistoryModal"
                                onclick="if(typeof initSubscriptionHistoryModal === 'function'){ initSubscriptionHistoryModal(); }">
                                Subscription History
                            </button>
                            <button type="button"
                                class="btn btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#discountOfferHistoryModal"
                                onclick="if(typeof initDiscountOfferHistoryModal === 'function'){ initDiscountOfferHistoryModal(); }">
                                Discounts/Offers
                            </button>
                            <a href="{{ route('download_all_data') }}"
                               class="btn btn-outline-primary subscription-download-data-btn"
                               data-bs-toggle="popover"
                               data-bs-trigger="hover focus"
                               data-bs-placement="bottom"
                               data-bs-html="true"
                               data-bs-custom-class="download-data-popover"
                               title="Data Retention"
                               data-bs-content="After subscription expiry or termination, subscriber data remains available for up to <strong>60 days</strong>. During this retention period, you can use <strong>Download All Data</strong> to download a compressed archive containing Clients, Applications, Users (Staff), Invoices, Payments, Communications, and all uploaded Documents. Documents are organized as <strong>ClientName - Application</strong> folders. After 60 days without renewal, related subscriber data and documents are permanently deleted.">
                                Download All Data
                            </a>
                        @endif

                    </div>
                </div>

                <div class="profile-detail">
                    <div class="col-12 profile-data" style="border: 1px solid lightgrey;">
                        {{-- <div class="row">
                            <div class="col-11"></div>
                            <div class="col-1 editss">
                                <img style="cursor: pointer;" onclick="document.getElementById('update_box').style.display='flex';" src="{{ asset('web_assets/images/edit.png') }}"width="20" height="20" alt="">
                            </div>
                        </div> --}}
                        @if($myplan == null)
                        @else
                        <div class="row det-row">
                            <div class="col-12">
                                <div class="subscription-detail-list">
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Plan Name</span>
                                        <span class="subscription-detail-value">{{ $myplan->plan_name }}</span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Client Limit</span>
                                        <span class="subscription-detail-value">
                                            @include('partials.subscription_limit_display', ['display' => $effectiveLimits['client_limit_display']])
                                        </span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">User License</span>
                                        <span class="subscription-detail-value">
                                            @include('partials.subscription_limit_display', ['display' => $effectiveLimits['user_limit_display']])
                                        </span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Associates Limit</span>
                                        <span class="subscription-detail-value">
                                            @include('partials.subscription_limit_display', ['display' => $effectiveLimits['associate_limit_display']])
                                        </span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Messages</span>
                                        <span class="subscription-detail-value">
                                            @include('partials.subscription_limit_display', ['display' => $effectiveLimits['message_limit_display']])
                                        </span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Invoicing</span>
                                        <span class="subscription-detail-value">{{ $myplan->invoicing }}</span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Reports</span>
                                        <span class="subscription-detail-value">{{ $myplan->reports }}</span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Analytics</span>
                                        <span class="subscription-detail-value">
                                            @include('partials.subscription_limit_display', ['display' => $effectiveLimits['analytics_display']])
                                        </span>
                                    </div>
                                    @if(!empty($effectiveLimits['active_offer_labels']) && !empty($effectiveLimits['subscription_active']))
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Active Offers</span>
                                        <span class="subscription-detail-value">{{ implode(', ', $effectiveLimits['active_offer_labels']) }}</span>
                                    </div>
                                    @endif
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Price (USD)</span>
                                        <span class="subscription-detail-value">{{ $plan_amount }}</span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Validity</span>
                                        <span class="subscription-detail-value">
                                            @include('partials.subscription_limit_display', ['display' => $subscriptionTerm['validity_display']])
                                        </span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Multi-Device Support</span>
                                        <span class="subscription-detail-value">{{ $myplan->multi_device_support }}</span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Secure Environment</span>
                                        <span class="subscription-detail-value">{{ $myplan->secure_environment }}</span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Multi-Currency Support</span>
                                        <span class="subscription-detail-value">{{ $myplan->multi_currency_support }}</span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Subscription Start Date</span>
                                        <span class="subscription-detail-value">{{ $formattedSubscriptionStart }}</span>
                                    </div>
                                    <div class="subscription-detail-row">
                                        <span class="subscription-detail-label">Subscription End Date</span>
                                        <span class="subscription-detail-value">
                                            @include('partials.subscription_limit_display', ['display' => $subscriptionTerm['end_display']])
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    {{-- <div class="col-lg-4 profile-pic" style="border: 1px solid lightgrey;">
                        <div class="row">
                            <div class="col-10"></div>
                            <div class="col-2">
                                <img onclick="document.getElementById('update_img_box').style.display='flex';" src="{{ asset('web_assets/images/edit.png') }}"width="20" height="20" alt="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-7 profilepic-row">
                                @if($siteuser->profile_img != "")
                                <img src="{{ asset('web_assets/users/user'.$siteuser->id.'/'.$siteuser->profile_img) }}" width="200" height="200" alt="">
                                @else
                                <img src="{{ asset('web_assets/images/profile.jpg') }}" width="200" height="200" alt="">
                                @endif
                            </div>
                            <div class="col-lg-5"></div>
                        </div>
                    </div> --}}
                </div>

                {{-- <div class="table-btn">
                    <button>Previous</button>
                    <button>1</button>
                    <button>2</button>
                    <button>3</button>
                    <button>Next</button>
                </div> --}}
            </div>
        </div>

    </div>

</div>
<script>

</script>

@if(session()->has('deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'User deleted successfully.'
    })
  </script>

@endif
@if(session()->has('price_plan_expiry'))
  <script>
    Swal.fire({
      icon: 'warning',
      title: 'Your subscription plan has expired',
      html: 'Please <a @if($user->user_type == "Subscriber") href="{{ route('membership') }}" @else href="#" @endif>renew or upgrade</a> your plan to continue.'
    })
  </script>

@endif
@if(session()->has('payment_success'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: @json(session('payment_success'))
    })
  </script>

@endif
<style>
  .download-data-popover {
    max-width: 360px;
  }
  .download-data-popover .popover-body {
    font-size: 13px;
    line-height: 1.45;
  }
</style>

@include('admin.partials.subscription_history_modal', [
    'showSubscriberFilter' => false,
    'historyDataUrl' => route('subscription_history_data'),
])
@include('admin.partials.discount_offer_history_modal', [
    'showSubscriberFilter' => false,
    'historyDataUrl' => route('discount_offer_history_data'),
])

@endsection

@push('scripts')
<script>
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
      new bootstrap.Popover(el, {
        html: true,
        trigger: 'hover focus',
        container: 'body',
        sanitize: false
      });
    }
  });
</script>
@if(session()->has('download_error'))
<script>
  Swal.fire({
    icon: 'error',
    title: 'Download Failed',
    text: @json(session('download_error'))
  });
</script>
@endif
@endpush
