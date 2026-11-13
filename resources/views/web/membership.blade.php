@extends('web.layout.main')

@section('main-section')
@php
use App\Models\UserRoles;
if(isset($user)){
$client_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Clients')->first();
$application_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Applications')->first();
$communication_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Communication')->first();
$invoice_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Invoices')->first();
$payment_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Payments')->first();
$report_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Reports')->first();
$subscription_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Subscription')->first();
$setting_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Settings')->first();
$support_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Support')->first();
}
@endphp
<style>
        body{
            background-color: #F5F5F5;
        }
        .plan-title {
            font-size:20px;
            /* font-weight: 500; */
        }
    </style>
  <div class="membership-page">
        <img src="{{ asset('web_assets/images/membership.png') }}" width="1519" height="440" alt="">
        <h1>Subscription</h1>
    </div>

    <div class="container-fluid member-mainbox mt-5 mb-5">
        <div class="plans-section-head text-center mb-4">
            <h1>@if(isset($user) && isset($myplan)) Upgrade Plan @else Price Plans @endif</h1>
            @if(!(isset($user) && isset($myplan)))
            <p class="plans-section-sub">Choose the plan that fits your practice — upgrade anytime.</p>
            @endif
        </div>
        @if(isset($user) && isset($myplan))
        <p class="text-center text-muted mb-4">Your current plan and available upgrades. Downgrades are only available at renewal.</p>
        @endif
        <div class="owl-carousel owl-theme" id="subscription-plan">
            @foreach($membership as $plan)
            @if(empty($myplan))
            <div class="plan-card">
                <h3 class="plan-title">{{ $plan->plan_name }}<span class="plan-title-price">${{ $plan->price_per_year + 0 }}</span></h3>
                <ul class="plan-features">
                    <li><span class="plan-feat-label">Client Limit</span><span class="plan-feat-value">{{ $plan->client_limit }}</span></li>
                    <li><span class="plan-feat-label">User License</span><span class="plan-feat-value">{{ $plan->no_of_users }}</span></li>
                    <li><span class="plan-feat-label">Messages</span><span class="plan-feat-value">{{ $plan->messaging }}</span></li>
                    <li><span class="plan-feat-label">Emails</span><span class="plan-feat-value">{{ (int) ($plan->email_broadcast_limit ?? 0) > 0 ? number_format((int) $plan->email_broadcast_limit) . " / Year" : "0" }}</span></li>
                    <li><span class="plan-feat-label">Reports</span><span class="plan-feat-value">{{ $plan->reports }}</span></li>
                    <li><span class="plan-feat-label">Invoicing</span><span class="plan-feat-value">@if($plan->invoicing == 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Analytics</span><span class="plan-feat-value">@if($plan->analytics === 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Multi-Device Support</span><span class="plan-feat-value">@if($plan->multi_device_support === 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Secure Environment</span><span class="plan-feat-value">@if($plan->secure_environment === 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Multi-Currency Support</span><span class="plan-feat-value">@if($plan->multi_currency_support === 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Validity</span><span class="plan-feat-value">{{ $plan->validity }} Days</span></li>


                </ul>
                {{-- <button class="subscribe-btn"
                    @if(isset($user))
                        onclick="window.location.href = '{{ route('membership') }}';"
                    @else
                        onclick="window.location.href = '{{ route('user_register_plan', $plan->plan_name) }}';"
                    @endif>
                    Subscribe
                </button> --}}

                <button  class="subscribe-btn" onclick="window.location.href = '{{ route('user_register_plan',$plan->plan_name) }}';">Subscribe</button>

            </div>
            @elseif($plan->plan_name != 'Free')
            @php
                $isCurrentPlan = isset($myplan) && $plan->plan_name === $myplan->plan_name;
                $isHigherPlan = isset($myplan) && \App\Services\SubscriptionTermPricing::isUpgradePlan($myplan, $plan);
            @endphp

            @if(!$isCurrentPlan && !$isHigherPlan)
                @continue
            @endif

            <div class="plan-card">
                <h3 class="plan-title">{{ $plan->plan_name }}<span class="plan-title-price">${{ $plan->price_per_year + 0 }}</span></h3>
                <ul class="plan-features">
                    <li><span class="plan-feat-label">Client Limit</span><span class="plan-feat-value">{{ $plan->client_limit }}</span></li>
                    <li><span class="plan-feat-label">User License</span><span class="plan-feat-value">{{ $plan->no_of_users }}</span></li>
                    <li><span class="plan-feat-label">Messages</span><span class="plan-feat-value">{{ $plan->messaging }}</span></li>
                    <li><span class="plan-feat-label">Emails</span><span class="plan-feat-value">{{ (int) ($plan->email_broadcast_limit ?? 0) > 0 ? number_format((int) $plan->email_broadcast_limit) . " / Year" : "0" }}</span></li>
                    <li><span class="plan-feat-label">Reports</span><span class="plan-feat-value">{{ $plan->reports }}</span></li>
                    <li><span class="plan-feat-label">Invoicing</span><span class="plan-feat-value">@if($plan->invoicing == 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Analytics</span><span class="plan-feat-value">@if($plan->analytics === 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Multi-Device Support</span><span class="plan-feat-value">@if($plan->multi_device_support === 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Secure Environment</span><span class="plan-feat-value">@if($plan->secure_environment === 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Multi-Currency Support</span><span class="plan-feat-value">@if($plan->multi_currency_support === 'Yes')
                            <i class="fa fa-check icon-circle plan-check"></i>
                        @else
                            <i class="fa fa-times icon-circle text-danger"></i>
                        @endif</span></li>
                    <li><span class="plan-feat-label">Validity</span><span class="plan-feat-value">{{ $plan->validity }} Days</span></li>


                </ul>
                {{-- <button class="subscribe-btn"
                    @if(isset($user))
                        onclick="window.location.href = '{{ route('membership') }}';"
                    @else
                        onclick="window.location.href = '{{ route('user_register_plan', $plan->plan_name) }}';"
                    @endif>
                    Subscribe
                </button> --}}
                @if(isset($user))
                @if($isCurrentPlan)
                  @if(isset($subscriber) && \App\Services\SubscriptionTermPricing::isSubscriptionLapsed($subscriber))
                    <button class="subscribe-btn subscribe-btn--active" type="button">Lapsed</button>
                  {{-- Expired, or within the last 60 days before expiry (SubscriptionTermPricing renewal window) --}}
                  @elseif((new DateTime("now")) > (new DateTime($subscriber->membership_expiry_date)) || ($user->membership_type != "Trial" && \App\Services\SubscriptionTermPricing::isRenewalWindowOpen($subscriber)))
                  <button class="subscribe-btn" @if($user->user_type == "Subscriber") onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';" @endif>Renew Subscription</button>
                  @else
                    @if($user->membership_type == "Trial")
                    <button class="subscribe-btn" @if($user->user_type == "Subscriber") onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';" @endif>Active</button>
                    @else
                    <button class="subscribe-btn subscribe-btn--active" type="button">Current Plan</button>
                    @endif
                  @endif
                @elseif($isHigherPlan)
                    <button class="subscribe-btn" @if($user->user_type == "Subscriber") onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';" @endif >
                      Upgrade
                    </button>
                @endif
              @else
                <button  class="subscribe-btn" onclick="window.location.href = '{{ route('user_register_plan',$plan->plan_name) }}';">Subscribe</button>
              @endif

            </div>
            @endif

            @endforeach
        </div>
    </div>


      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {


        });
    </script>

@if (session()->has('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Congratulations',
        text: 'Profile updated successfully.'
    })
</script>
@endif
@if(session()->has('membership_expiry'))
    <script>
      Swal.fire({
        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
        title: 'Membership expired',
        text: 'Please renew or upgrade your membership.'
      })
    </script>

@endif

@endsection()
