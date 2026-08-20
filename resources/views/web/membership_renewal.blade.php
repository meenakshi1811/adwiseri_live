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
            font-size:20px !important;
            /* font-weight: 500; */
        }
    </style>
  <div class="membership-page">
        <img src="{{ asset('web_assets/images/membership.png') }}" width="1519" height="440" alt="">
        <h1>Subscription Plans</h1>
    </div>

    <div class="container-fluid member-mainbox mt-5 mb-5">
        <h1 class="text-center mb-4">Price Plans</h1>
        <p class="text-center text-muted mb-4">Renew, upgrade, or downgrade at checkout — choose a 1, 2, 3, 4, or 5 year term with multi-year savings. Downgrades are only available during renewal.</p>
        <div class="owl-carousel owl-theme" id="subscription-plan">
        @php
            $today = \Carbon\Carbon::now();
            $expiry = \Carbon\Carbon::parse($subscriber->membership_expiry_date ?? null);
            $renewStart = $expiry->copy()->subDays(60);
            $renewEnd = $expiry->copy()->addDays(30);
        @endphp

        @foreach($membership as $plan)
            @php
                $showPlan = true;
                $isCurrentPlan = isset($myplan) && $plan->plan_name === $myplan->plan_name;
                $isHigherPlan = isset($myplan) && \App\Services\SubscriptionTermPricing::isUpgradePlan($myplan, $plan);
                $isLowerPlan = isset($myplan) && \App\Services\SubscriptionTermPricing::isDowngradePlan($myplan, $plan);
                $inRenewalWindow = isset($subscriber) && \App\Services\SubscriptionTermPricing::isRenewalWindowOpen($subscriber, $myplan);
            @endphp

            @if($isCurrentPlan)
                @if(isset($subscriber) && \App\Services\SubscriptionTermPricing::isSubscriptionLapsed($subscriber))
                    <div class="plan-card">
                        <h3 class="plan-title">{{ $plan->plan_name }}</h3>
                        <ul class="plan-features">
                            <li>Client Limit: {{ $plan->client_limit }}</li>
                            <li>User License: {{ $plan->no_of_users }}</li>
                            <li>Messages: {{ $plan->messaging }}</li>
                            <li>Reports: {{ $plan->reports }}</li>
                            <li>Invoicing: {!! $plan->invoicing === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Analytics: {!! $plan->analytics === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Multi-Device Support: {!! $plan->multi_device_support === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Secure Environment: {!! $plan->secure_environment === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Multi-Currency Support: {!! $plan->multi_currency_support === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Validity: {{ $plan->validity }} Days</li>
                        </ul>
                        <h5 class="plan-price">{{ $plan->price_per_year != 0 ? 'USD '.$plan->price_per_year : 'Free' }}</h5>
                        <button class="subscribe-btn subscribe-btn--active" type="button">Lapsed</button>
                    </div>
                @elseif($today->between($renewStart, $renewEnd))
                    {{-- Show current plan with "Renew" button only in renewal window --}}
                    <div class="plan-card">
                        <h3 class="plan-title">{{ $plan->plan_name }}</h3>
                        <ul class="plan-features">
                            <li>Client Limit: {{ $plan->client_limit }}</li>
                            <li>User License: {{ $plan->no_of_users }}</li>
                            <li>Messages: {{ $plan->messaging }}</li>
                            <li>Reports: {{ $plan->reports }}</li>
                            <li>Invoicing: {!! $plan->invoicing === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Analytics: {!! $plan->analytics === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Multi-Device Support: {!! $plan->multi_device_support === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Secure Environment: {!! $plan->secure_environment === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Multi-Currency Support: {!! $plan->multi_currency_support === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                            <li>Validity: {{ $plan->validity }} Days</li>
                        </ul>
                        <h5 class="plan-price">{{ $plan->price_per_year != 0 ? 'USD '.$plan->price_per_year : 'Free' }}</h5>
                        <button class="subscribe-btn" onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';">Renew</button>
                    </div>
                @endif
            @elseif($isHigherPlan)
                {{-- Show upgrade plans that are more expensive than current --}}
                <div class="plan-card">
                    <h3 class="plan-title">{{ $plan->plan_name }}</h3>
                    <ul class="plan-features">
                        <li>Client Limit: {{ $plan->client_limit }}</li>
                        <li>User License: {{ $plan->no_of_users }}</li>
                        <li>Messages: {{ $plan->messaging }}</li>
                        <li>Reports: {{ $plan->reports }}</li>
                        <li>Invoicing: {!! $plan->invoicing === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Analytics: {!! $plan->analytics === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Multi-Device Support: {!! $plan->multi_device_support === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Secure Environment: {!! $plan->secure_environment === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Multi-Currency Support: {!! $plan->multi_currency_support === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Validity: {{ $plan->validity }} Days</li>
                    </ul>
                    <h5 class="plan-price">{{ $plan->price_per_year != 0 ? 'USD '.$plan->price_per_year : 'Free' }}</h5>
                    <button class="subscribe-btn" onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';">Upgrade</button>
                </div>
            @elseif($isLowerPlan && $inRenewalWindow && $plan->plan_name !== 'Free')
                <div class="plan-card">
                    <h3 class="plan-title">{{ $plan->plan_name }}</h3>
                    <ul class="plan-features">
                        <li>Client Limit: {{ $plan->client_limit }}</li>
                        <li>User License: {{ $plan->no_of_users }}</li>
                        <li>Messages: {{ $plan->messaging }}</li>
                        <li>Reports: {{ $plan->reports }}</li>
                        <li>Invoicing: {!! $plan->invoicing === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Analytics: {!! $plan->analytics === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Multi-Device Support: {!! $plan->multi_device_support === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Secure Environment: {!! $plan->secure_environment === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Multi-Currency Support: {!! $plan->multi_currency_support === 'Yes' ? '<i class="fa fa-check plan-check"></i>' : '<i class="fa fa-times text-danger"></i>' !!}</li>
                        <li>Validity: {{ $plan->validity }} Days</li>
                    </ul>
                    <h5 class="plan-price">{{ $plan->price_per_year != 0 ? 'USD '.$plan->price_per_year : 'Free' }}</h5>
                    @if(count($total_users) > $plan->no_of_users || count($total_clients) > $plan->client_limit)
                        <button class="subscribe-btn" onclick="Swal.fire({ icon: 'warning', title: 'User/Client Limit', text: 'This plan\'s user/client limit is lower than your current number of users and clients.' });">Downgrade</button>
                    @else
                        <button class="subscribe-btn" onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';">Downgrade</button>
                    @endif
                </div>
            @endif
        @endforeach

        </div>
    </div>


      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
      @if(isset($user))
      function downgrade_plan(name){
        var myformData = new FormData();
        myformData.append('id', "{{ $user->id }}");
        myformData.append('plan_name', name);
        myformData.append('_token', "{{ csrf_token() }}");
        console.log(myformData);
        $.ajax({
            url: "{{ route('downgrade_plan') }}",
            method: 'POST',
            data: myformData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data) {
                if(data == "success"){
                  window.location.reload();
                }
            }
        });
    }
    @endif
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
