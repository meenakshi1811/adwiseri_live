@extends('web.layout.main')

@section('main-section')
    @php
        $homepageSections = $homepageSectionVisibility ?? \App\Models\HomepageSectionSetting::defaultVisibility();
        $showHomepageSection = function (string $key) use ($homepageSections): bool {
            return \App\Models\HomepageSectionSetting::castVisibility($homepageSections[$key] ?? true, true);
        };
        $img = asset('web_assets/images/havedemo.png');
    @endphp
    @if($showHomepageSection('banner'))
    <!---Banner-->
    {{-- <div class="main-banner" style="background-image: url('{{ $img }}');"> --}}
        {{-- <p>One stop solution for <br>
            Visas & Immigration
            adwiseries <br> to manage cases, storing<br> documents,<br>
            Analytics & Reports and much<br> more...
        </p> --}}
        {{-- <p class="col-lg-5">One-stop solution for Visa & Immigration Consultancies with features like Cloud Storage, Users & Case Management, Analytics, Reports and much more...</p>
        <form class="Signup__form" id="newsletter" method="POST" action="{{route('email_subscription')}}">
            @csrf
            @error('email')
                <script>
                    Swal.fire({
                        icon: 'warning',
                        customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: '{{$message}}'
                    })
                </script>
            @enderror

            <input required id="email" name="email" minlength="3" maxlength="100" type="email" placeholder="Enter Your email id">
           &nbsp; &nbsp; &nbsp; &nbsp;  <button form="newsletter" type="submit" class="Signup__button">Subscribe</button><br>
            <span style="font-size:14px;margin:0px;color:grey;">Subscribe to our newsletter to get the latest news, updates and advice.</span>
        </form>

    </div> --}}
    <div class="main-banner text-center" style="background-image: url('{{ $img }}');">
        <p class="col-lg-9">
            One-stop solution for Visa & Immigration Consultancies with features like Cloud Storage, Users & Case Management, Analytics, Reports, and much more...
        </p>
        <!-- <form class="Signup__form mt-4" id="newsletter" method="POST" action="{{route('email_subscription')}}" style="max-width: 500px; width: 100%; margin-top: 20px;">
            @csrf
            @error('email')
                <script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops!',
                        text: '{{$message}}'
                    })
                </script>
            @enderror

            <div class="input-container">
                <input required id="email" name="email" type="email" placeholder="Enter Your email address">
                <button type="submit">Subscribe</button>
            </div>
            <span>Subscribe our newsletters to get latest news, updates and offers.</span>
        </form> -->
       <form class="Signup__form mt-4" id="homepage-newsletter-form" method="POST" 
            action="{{ route('email_subscription') }}" 
            style="max-width: 500px; width: 100%; margin-top: 20px;" novalidate>
            @csrf

            <div class="input-container">
                <input id="homepage-newsletter-email" name="email" type="email" placeholder="Enter your email address" autocomplete="email" inputmode="email">
                <button type="button" id="homepage-newsletter-btn" class="newsletter-subscribe-btn">Subscribe</button>
            </div>
            <span>Subscribe our newsletters to get latest news, updates and offers.</span>
        </form>

        <style>
        /* Make popup smaller */
        .small-swal-popup {
            transform: translateY(-60px) !important;
        }

        #homepage-newsletter-form.Signup__form input.newsletter-input-invalid {
            box-shadow: 0 0 0 2px #ff6b6b inset !important;
        }

        /* Mobile-safe newsletter row: button stays glued to the input height */
        #homepage-newsletter-form.Signup__form .input-container {
            position: relative !important;
            display: block !important;
            width: 100% !important;
            height: 48px !important;
            overflow: hidden;
            border-radius: 10px;
        }
        #homepage-newsletter-form.Signup__form .input-container input[type="email"] {
            height: 48px !important;
            margin: 0 !important;
            padding: 0 118px 0 16px !important;
            box-sizing: border-box !important;
        }
        #homepage-newsletter-form.Signup__form .input-container .newsletter-subscribe-btn {
            position: absolute !important;
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            left: auto !important;
            height: auto !important;
            margin: 0 !important;
            transform: none !important;
            border-radius: 0 10px 10px 0 !important;
            z-index: 3 !important;
        }
        @media (max-width: 767px) {
            #homepage-newsletter-form.Signup__form .input-container,
            #homepage-newsletter-form.Signup__form .input-container input[type="email"] {
                height: 46px !important;
            }
            #homepage-newsletter-form.Signup__form .input-container input[type="email"] {
                padding: 0 108px 0 14px !important;
                font-size: 14px !important;
            }
        }
        @media (max-width: 560px) {
            #homepage-newsletter-form.Signup__form .input-container,
            #homepage-newsletter-form.Signup__form .input-container input[type="email"] {
                height: 44px !important;
            }
            #homepage-newsletter-form.Signup__form .input-container input[type="email"] {
                padding: 0 100px 0 12px !important;
            }
            #homepage-newsletter-form.Signup__form .input-container .newsletter-subscribe-btn {
                font-size: 12px !important;
                padding: 0 10px !important;
            }
        }
        </style>

    </div>
    @endif



    @if($showHomepageSection('about_highlights'))
    <!---About us-->
    <div class="container about-container mb-5">
        <div class="row about-row">
            <div class="col-3">
                <img src="{{ asset('web_assets/images/demo.png') }}" width="100" height="100" alt="">
                <p>Instant Activation</p>
            </div>
            <div class="col-3">
                <img src="{{ asset('web_assets/images/dat.png') }}" width="110" height="100" alt="">
                <p>Secure Data</p>
            </div>
            <div class="col-3">
                <img src="{{ asset('web_assets/images/easy.png') }}" width="80" height="100" alt="">
                <p>Easy to Use</p>
            </div>
            <div class="col-3">
                <img src="{{ asset('web_assets/images/call_support.png') }}" width="100" height="100" alt="">
                <p>Dedicated support</p>
            </div>
        </div>
    </div>
    @endif

    @if(
        $showHomepageSection('key_features')
        || $showHomepageSection('about_us')
        || $showHomepageSection('price_plans')
        || $showHomepageSection('discounts_offers')
    )
    <!---Top features--->
    <div class="container-fluid top-feature mb-5">
        @if($showHomepageSection('key_features'))
        <div class="container">
            <div class="row feature-row mb-3">
                <div class="col-sm-12 col-lg-4 feature-img" style="overflow: hidden;">
                    <img src="{{ asset('web_assets/images/feature.png') }}" width="auto" height="330" alt="">
                </div>
                <div class="col-sm-12 col-lg-8 feature-text">
                    <h1>Key Features</h1>
                    <div class="row">
                        @foreach($features as $feature)
                        <h4 class="col-sm-12 col-md-6"> <img src="{{ asset('admin_assets/features/icon/'.$feature->icon) }}" width="30"
                                height="30" alt=""> {{ $feature->name }}</h4>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($showHomepageSection('about_us'))
        <!--- Have a demo --->

        <div class="container-fluid have-demo pt-3 pb-3">
            <div class="row demo-row">
                <div class="demo-row-text col-sm-12 col-md-6 ps-lg-5 pt-3">
                    <h4>About Us</h4>
                    <p class="mt-3">We care about your business, so tried our best to offer you a solution to manage your daily tasks with efficiency & security leaving you time to focus on the core.</p>
                </div>
                <div class="demo-row-img col-sm-12 col-md-6">
                    {{-- <h2 class="mt-4">Book Demo</h2> --}}
                    <img src="{{ asset('web_assets/images/havedemo.png') }}" width="100%"
                        alt="">
                </div>
            </div>
        </div>
        @endif


        @if($showHomepageSection('price_plans'))
        <!--- Membership --->

        {{-- <div class="container-fluid member-mainbox mt-5 mb-5">
            <h1>Price Plans</h1>

            <div class="row bbx mt-3">
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 member-first mt-3">
                    <h4>Plan</h4>
                    <div class="box-extra fss">
                        <ul class="p-0">
                            <!--<li>Data Limit</li>-->
                            <li class="text-center">Client Limit</li>
                            <li class="text-center">User License </li>
                            <li class="text-center">Messages</li>
                            <li class="text-center">Invoicing</li>
                            <li class="text-center">Reports</li>
                            <li class="text-center">Analytics</li>
                            <!--<li>No. of Branches</li>-->
                            <li class="text-center">Multi-Device Support</li>
                            <li class="text-center">Secure Environment</li>
                            <li class="text-center">Multi-Currency Support</li>
                            <li class="text-center">Validity (Days)</li>
                        </ul>
                    </div>
                    <h5>Price</h5>
                </div>
                @foreach($price_plans as $plan)
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 member-first mt-3">
                    <h4>{{ $plan->plan_name }}</h4>
                    <div class="box-extra ms">
                        <ul class="p-0">
                            <!--<li>{{ $plan->data_limit }}</li>-->
                            <li class="text-center">{{ $plan->client_limit}}</li>
                            <li class="text-center">{{ $plan->no_of_users }}</li>
                            <li class="text-center">{{ $plan->messaging }}</li>
                            <li class="text-center">{{ $plan->invoicing }}</li>
                            <li class="text-center">{{ $plan->reports }}</li>
                            <li class="text-center">{{ $plan->analytics }}</li>
                            <!--<li>{{ $plan->no_of_branches }}</li>-->
                            <li class="text-center">{{ $plan->multi_device_support }}</li>
                            <li class="text-center">{{ $plan->secure_environment }}</li>
                            <li class="text-center">{{ $plan->multi_currency_support }}</li>
                            <li class="text-center">{{ $plan->validity }} Days</li>
                        </ul>
                    </div>
                    <h5>US ${{ $plan->price_per_year }}</h5>
                    <button @if(isset($user)) onclick="window.location.href = '{{ route('membership') }}';" @else onclick="window.location.href = '{{ route('user_register_plan',$plan->plan_name) }}';" @endif>Subscribe</button>
                </div>
                @endforeach
            </div>

        </div> --}}
        <div class="container-fluid member-mainbox plans-section mt-5 mb-5">
            <div class="plans-section-head text-center mb-4">
                <h1>Price Plans</h1>
                <p class="plans-section-sub">Choose the plan that fits your practice — upgrade anytime.</p>
            </div>
            <div class="owl-carousel owl-theme" id="subscription-plan">
                @foreach($price_plans as $plan)
                @php
                    $isFreePlan = ($plan->price_per_year == 0) || in_array($plan->plan_name, ['Free', 'Free Plan'], true);
                    $isPopular = stripos((string) $plan->plan_name, '+') !== false
                        || strcasecmp((string) $plan->plan_name, 'Adwiseri+') === 0;
                @endphp
                @if(empty($myplan) )
                <div class="plan-card{{ $isPopular ? ' plan-card--popular' : '' }}">
                    @if($isPopular)
                        <span class="plan-badge">Most Popular</span>
                    @endif
                    <h4 class="plan-title">{{ $plan->plan_name }}</h4>
                    <div class="plan-price-block">
                        @if($isFreePlan)
                            <h5 class="plan-price">$0/{{ $plan->validity ?? 30 }} Days</h5>
                        @else
                            <h5 class="plan-price">${{ $plan->price_per_year }}/Year</h5>
                        @endif
                    </div>
                    <ul class="plan-features">
                        <li><span class="plan-feat-label">Client Limit</span><span class="plan-feat-value">{{ $plan->client_limit }}</span></li>
                        <li><span class="plan-feat-label">User License</span><span class="plan-feat-value">{{ $plan->no_of_users }}</span></li>
                        <li><span class="plan-feat-label">Messages</span><span class="plan-feat-value">{{ $plan->messaging }}</span></li>
                        <li><span class="plan-feat-label">Reports</span><span class="plan-feat-value">{{ $plan->reports }}</span></li>
                        <li>
                            <span class="plan-feat-label">Invoicing</span>
                            <span class="plan-feat-value">
                            @if($plan->invoicing == 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                        <li>
                            <span class="plan-feat-label">Analytics</span>
                            <span class="plan-feat-value">
                            @if($plan->analytics === 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                        <li>
                            <span class="plan-feat-label">Multi-Device</span>
                            <span class="plan-feat-value">
                            @if($plan->multi_device_support === 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                        <li>
                            <span class="plan-feat-label">Secure Environment</span>
                            <span class="plan-feat-value">
                            @if($plan->secure_environment === 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                        <li>
                            <span class="plan-feat-label">Multi-Currency</span>
                            <span class="plan-feat-value">
                            @if($plan->multi_currency_support === 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                    </ul>
                    <button class="subscribe-btn" onclick="window.location.href = '{{ route('user_register_plan',$plan->plan_name) }}';">Subscribe</button>
                </div>
                @elseif( $plan->plan_name != 'Free')
                @php
                    $isCurrentPlan = isset($myplan) && $plan->plan_name === $myplan->plan_name;
                    $isHigherPlan = isset($myplan) && \App\Services\SubscriptionTermPricing::isUpgradePlan($myplan, $plan);
                @endphp
                @if(isset($myplan) && !$isCurrentPlan && !$isHigherPlan)
                    @continue
                @endif
                <div class="plan-card{{ $isPopular ? ' plan-card--popular' : '' }}">
                    @if($isPopular)
                        <span class="plan-badge">Most Popular</span>
                    @endif
                    <h4 class="plan-title">{{ $plan->plan_name }}</h4>
                    <div class="plan-price-block">
                        @if($isFreePlan)
                            <h5 class="plan-price">$0/{{ $plan->validity ?? 30 }} Days</h5>
                        @else
                            <h5 class="plan-price">${{ $plan->price_per_year }}/Year</h5>
                        @endif
                    </div>
                    <ul class="plan-features">
                        <li><span class="plan-feat-label">Client Limit</span><span class="plan-feat-value">{{ $plan->client_limit }}</span></li>
                        <li><span class="plan-feat-label">User License</span><span class="plan-feat-value">{{ $plan->no_of_users }}</span></li>
                        <li><span class="plan-feat-label">Messages</span><span class="plan-feat-value">{{ $plan->messaging }}</span></li>
                        <li><span class="plan-feat-label">Reports</span><span class="plan-feat-value">{{ $plan->reports }}</span></li>
                        <li>
                            <span class="plan-feat-label">Invoicing</span>
                            <span class="plan-feat-value">
                            @if($plan->invoicing == 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                        <li>
                            <span class="plan-feat-label">Analytics</span>
                            <span class="plan-feat-value">
                            @if($plan->analytics === 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                        <li>
                            <span class="plan-feat-label">Multi-Device</span>
                            <span class="plan-feat-value">
                            @if($plan->multi_device_support === 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                        <li>
                            <span class="plan-feat-label">Secure Environment</span>
                            <span class="plan-feat-value">
                            @if($plan->secure_environment === 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                        <li>
                            <span class="plan-feat-label">Multi-Currency</span>
                            <span class="plan-feat-value">
                            @if($plan->multi_currency_support === 'Yes')
                                <i class="fa fa-check icon-circle plan-check"></i>
                            @else
                                <i class="fa fa-times icon-circle text-danger"></i>
                            @endif
                            </span>
                        </li>
                    </ul>
                    @if(isset($user))
                    @if($isCurrentPlan)
                      @if(isset($subscriber) && \App\Services\SubscriptionTermPricing::isSubscriptionLapsed($subscriber))
                        <button class="subscribe-btn subscribe-btn--active" type="button">Lapsed</button>
                      @elseif((new DateTime("now")) > (new DateTime($subscriber->membership_expiry_date)))
                      <button class="subscribe-btn" @if($user->user_type == "Subscriber") onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';" @endif>Renew</button>
                      @else
                        @if($user->membership_type == "Trial")
                        <button class="subscribe-btn" @if($user->user_type == "Subscriber") onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';" @endif>Active</button>
                        @else
                        <button class="subscribe-btn subscribe-btn--active" type="button">Current Plan</button>
                        @endif
                      @endif
                    @elseif($isHigherPlan)
                        <button class="subscribe-btn" @if($user->user_type == "Subscriber") onclick="window.location.href = '{{ route('upgrade_membership', $plan->plan_name) }}';" @endif>
                          Upgrade
                        </button>
                    @endif
                  @else
                    <button class="subscribe-btn" onclick="window.location.href = '{{ route('user_register_plan',$plan->plan_name) }}';">Subscribe</button>
                  @endif
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        @if($showHomepageSection('discounts_offers') && $landingPromoSettings && ($landingDiscountItems->count() || $landingOfferItems->count()))
        <section class="landing-promo-section" aria-labelledby="landing-promo-heading">
            <style>
                .landing-promo-section {
                    --lp-ink: #1E2433;
                    --lp-muted: #5A6275;
                    --lp-line: #E2E6F0;
                    --lp-primary: #695EEE;
                    --lp-deep: #4C3BB7;
                    --lp-soft: rgba(105, 94, 238, 0.12);
                    --lp-surface: #F6F7FB;
                    position: relative;
                    padding: 3.25rem 0 3.75rem;
                    margin: 2.5rem 0 1.5rem;
                    background:
                        radial-gradient(ellipse 80% 50% at 10% 0%, rgba(105, 94, 238, 0.08), transparent 55%),
                        radial-gradient(ellipse 70% 45% at 90% 100%, rgba(76, 59, 183, 0.07), transparent 50%),
                        linear-gradient(180deg, #FBFBFE 0%, #F4F5FA 100%);
                    overflow: hidden;
                }
                .landing-promo-section::before {
                    content: "";
                    position: absolute;
                    inset: 0 0 auto 0;
                    height: 1px;
                    background: linear-gradient(90deg, transparent, rgba(105, 94, 238, 0.25), transparent);
                }
                .landing-promo-section .lp-inner {
                    position: relative;
                    z-index: 1;
                }
                .landing-promo-section .lp-head {
                    text-align: center;
                    max-width: 640px;
                    margin: 0 auto 1.85rem;
                }
                .landing-promo-section .lp-eyebrow {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.4rem;
                    margin-bottom: 0.7rem;
                    padding: 0.28rem 0.85rem;
                    border-radius: 999px;
                    background: var(--lp-soft);
                    color: var(--lp-deep);
                    font-size: 0.75rem;
                    font-weight: 700;
                    letter-spacing: 0.06em;
                    text-transform: uppercase;
                }
                .landing-promo-section .lp-head h1 {
                    font-size: clamp(1.7rem, 3.2vw, 2.35rem);
                    font-weight: 700;
                    color: var(--lp-deep);
                    margin: 0;
                    letter-spacing: -0.02em;
                    line-height: 1.2;
                }
                .landing-promo-section .lp-grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 1.5rem;
                    align-items: stretch;
                }
                .landing-promo-section .lp-card {
                    display: flex;
                    flex-direction: column;
                    border: 1px solid rgba(226, 230, 240, 0.95);
                    border-radius: 16px;
                    background: #fff;
                    overflow: hidden;
                    box-shadow: 0 10px 28px rgba(30, 36, 51, 0.06);
                    transition: transform 0.25s ease, box-shadow 0.25s ease;
                }
                .landing-promo-section .lp-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 16px 36px rgba(30, 36, 51, 0.1);
                }
                .landing-promo-section .lp-card-head {
                    display: block;
                    padding: 11px 16px 12px;
                    border-bottom: none;
                    background-color: var(--lp-primary);
                    text-align: center;
                }
                .landing-promo-section .lp-card-icon {
                    display: none;
                }
                .landing-promo-section .lp-card-head h3 {
                    margin: 0;
                    font-size: 15px;
                    font-weight: 700;
                    color: #fff;
                    letter-spacing: 0.02em;
                }
                .landing-promo-section .lp-card-head span.lp-card-sub {
                    display: block;
                    margin-top: 0.2rem;
                    font-size: 0.75rem;
                    color: rgba(255, 255, 255, 0.88);
                    font-weight: 500;
                }
                .landing-promo-section .lp-rows {
                    list-style: none;
                    margin: 0;
                    padding: 0.35rem 0;
                    flex: 1;
                }
                .landing-promo-section .lp-row {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 1rem 1.35rem;
                    border-bottom: 1px solid #F0F2F7;
                }
                .landing-promo-section .lp-row:last-child {
                    border-bottom: none;
                }
                .landing-promo-section .lp-badge {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 4.5rem;
                    padding: 0.45rem 0.9rem;
                    border-radius: 10px;
                    background: linear-gradient(135deg, var(--lp-primary) 0%, var(--lp-deep) 100%);
                    color: #fff;
                    font-weight: 800;
                    font-size: 0.95rem;
                    letter-spacing: 0.01em;
                    white-space: nowrap;
                    box-shadow: 0 4px 12px rgba(105, 94, 238, 0.28);
                }
                .landing-promo-section .lp-row-detail {
                    flex: 1;
                    text-align: right;
                    font-size: 0.98rem;
                    font-weight: 600;
                    color: var(--lp-ink);
                    line-height: 1.35;
                }
                .landing-promo-section .lp-row-label {
                    display: block;
                    margin-bottom: 0.15rem;
                    font-size: 0.7rem;
                    font-weight: 700;
                    letter-spacing: 0.05em;
                    text-transform: uppercase;
                    color: #8A93A8;
                }
                .landing-promo-section .lp-note {
                    margin: 0;
                    padding: 0.95rem 1.35rem 1.15rem;
                    font-size: 0.8rem;
                    line-height: 1.55;
                    color: #7A8194;
                    border-top: 1px dashed var(--lp-line);
                    background: var(--lp-surface);
                }
                .landing-promo-section .lp-note strong {
                    color: var(--lp-primary);
                }
                @media (max-width: 768px) {
                    .landing-promo-section {
                        padding: 2.5rem 0 3rem;
                    }
                    .landing-promo-section .lp-grid {
                        grid-template-columns: 1fr;
                    }
                    .landing-promo-section .lp-row {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 0.55rem;
                    }
                    .landing-promo-section .lp-row-detail {
                        text-align: left;
                    }
                }
            </style>
            <div class="container lp-inner">
                <div class="lp-head">
                    <span class="lp-eyebrow"><i class="fa-solid fa-tags" aria-hidden="true"></i> Exclusive deals</span>
                    <h1 id="landing-promo-heading">{{ $landingPromoSettings->heading }}</h1>
                </div>
                <div class="lp-grid">
                    @if($landingDiscountItems->count())
                    <article class="lp-card">
                        <div class="lp-card-head">
                            <span class="lp-card-icon"><i class="fa-solid fa-percent" aria-hidden="true"></i></span>
                            <div>
                                <h3>Discounts</h3>
                                <span class="lp-card-sub">Multi-year subscription savings</span>
                            </div>
                        </div>
                        <ul class="lp-rows">
                            @foreach($landingDiscountItems as $item)
                            <li class="lp-row">
                                <span class="lp-badge">{{ $item->benefit }}</span>
                                <div class="lp-row-detail">
                                    <span class="lp-row-label">Subscription term</span>
                                    {{ $item->detail }}
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @if($landingPromoSettings->discount_note)
                        <p class="lp-note"><strong>*</strong> {{ $landingPromoSettings->discount_note }}</p>
                        @endif
                    </article>
                    @endif

                    @if($landingOfferItems->count())
                    <article class="lp-card">
                        <div class="lp-card-head">
                            <span class="lp-card-icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span>
                            <div>
                                <h3>Offers</h3>
                                <span class="lp-card-sub">Cashback on selected plans</span>
                            </div>
                        </div>
                        <ul class="lp-rows">
                            @foreach($landingOfferItems as $item)
                            <li class="lp-row">
                                <span class="lp-badge">{{ $item->benefit }}</span>
                                <div class="lp-row-detail">
                                    <span class="lp-row-label">Plan</span>
                                    {{ $item->detail }}
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @if($landingPromoSettings->offer_note)
                        <p class="lp-note"><strong>*</strong> {{ $landingPromoSettings->offer_note }}</p>
                        @endif
                    </article>
                    @endif
                </div>
            </div>
        </section>
        @endif


        <!--- testimonials --->
        {{-- <div class="container-fluid testimonial-mainhead mb-5" >
            <div class="container review-head">
                <div class="row pt-5">
                    <h1 class="">Client Review</h1>
                </div>
                <div class="row owl-carousel owl-theme mt-5" id="testimonials">
                    <div class="col-4 card" style="width:400px;">

                        <div class="text-img d-flex">
                            <div class="test-image">
                                <img src="{{ asset('web_assets/images/sid.png') }}" alt="">
                            </div>
                            <div class="test-text">
                                <p>Siddharth Roy <br> <span>Tech Lead - Visa adwiseri</span> </p>
                            </div>
                        </div>


                        <div class="card-body">
                            <p class="card-text"><i class="fa-solid fa-quote-left"></i>This is a good platform. I was
                                searching for
                                a complete <br> immigration data solution. Where
                                adwiseri helped me. <i class="fa-solid fa-quote-right"></i></p>

                        </div>
                    </div>
                    <div class="col-4 card" style="width:400px;">

                        <div class="text-img d-flex">
                            <div class="test-image">
                                <img src="{{ asset('web_assets/images/cella.png') }}" alt="">
                            </div>
                            <div class="test-text">
                                <p>Cella Almeda <br> <span>Secretory - IBM</span> </p>
                            </div>
                        </div>


                        <div class="card-body">
                            <p class="card-text"><i class="fa-solid fa-quote-left"></i>This is a good platform. I was
                                searching for
                                a complete <br> immigration data solution. Where
                                adwiseri helped me. <i class="fa-solid fa-quote-right"></i></p>

                        </div>
                    </div>
                    <div class="col-4 card" style="width:400px;">

                        <div class="text-img d-flex">
                            <div class="test-image">
                                <img src="{{ asset('web_assets/images/vaishali.png') }}" alt="">
                            </div>
                            <div class="test-text">
                                <p>Vaishali Birla <br> <span>Chief Accountant </span> </p>
                            </div>
                        </div>


                        <div class="card-body">
                            <p class="card-text"><i class="fa-solid fa-quote-left"></i>This is a good platform. I was
                                searching for
                                a complete <br> immigration data solution. Where
                                adwiseri helped me. <i class="fa-solid fa-quote-right"></i></p>

                        </div>
                    </div>
                    <div class="col-4 card" style="width:400px;">

                        <div class="text-img d-flex">
                            <div class="test-image">
                                <img src="{{ asset('web_assets/images/cella.png') }}" alt="">
                            </div>
                            <div class="test-text">
                                <p>Cella Almeda <br> <span>Secretory - IBM</span> </p>
                            </div>
                        </div>


                        <div class="card-body">
                            <p class="card-text"><i class="fa-solid fa-quote-left"></i>This is a good platform. I was
                                searching for
                                a complete <br> immigration data solution. Where
                                adwiseri helped me. <i class="fa-solid fa-quote-right"></i></p>

                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    @endif





        @if($showHomepageSection('why_adwiseri'))
        <!--- why adwiseri --->
        <section class="why-advi why-advi-section mb-5" aria-labelledby="why-adwiseri-heading">
            <div class="container">
                <div class="why-advi-head text-center">
                    <h1 id="why-adwiseri-heading">Why adwiseri?</h1>
                </div>

                <div class="row advi-img-row why-advi-grid g-4 mt-4">
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <article class="why-advi-card">
                            <div class="why-advi-icon">
                                <img src="{{ asset('web_assets/images/datasecurity.png') }}" alt="Data Security">
                            </div>
                            <h3>Data Security</h3>
                        </article>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <article class="why-advi-card">
                            <div class="why-advi-icon">
                                <img src="{{ asset('web_assets/images/100client.png') }}" alt="Dedicated Support">
                            </div>
                            <h3>Dedicated Support</h3>
                        </article>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <article class="why-advi-card">
                            <div class="why-advi-icon">
                                <img src="{{ asset('web_assets/images/securepayment.png') }}" alt="Secure payment system">
                            </div>
                            <h3>Secure payment <br>system</h3>
                        </article>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <article class="why-advi-card">
                            <div class="why-advi-icon">
                                <img src="{{ asset('web_assets/images/50count.png') }}" alt="Available in multiple regions">
                            </div>
                            <h3>Available in multiple regions</h3>
                        </article>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- <div class="container-fluid testimonial-mainhead mb-5" >
            <div class="container review-head">
            <h1 class="text-center mb-4">Legacy discounts carousel (retired)</h1>
            </div>
        </div> -->
    @if($showHomepageSection('affiliates'))
    <div class="collab-box" id="affiliates">
        <section class="Affiliates-banner" aria-label="Affiliate program">
            <div class="affiliate-banner-glow" aria-hidden="true"></div>
            <div class="affiliate-banner-inner">
                <span class="affiliate-eyebrow">Affiliate Program</span>
                <h2 class="affiliate-title">Want to earn extra from referrals?</h2>
                <p class="affiliate-subtitle">
                    Refer consultancies you know and earn commissions when they subscribe.
                </p>
                <a href="{{ url('/') }}/Affiliates_Reg" class="affiliate-cta-btn">
                    Join Affiliate Program
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </section>
    </div>
    @endif


        {{-- <div class="container faq-section my-5">
            <h1 class="text-center mb-4">Frequently Asked Questions</h1>
            <div class="accordion" id="faqAccordion">
                <!-- Question 1 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading1">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                            What is your refund policy?
                            <i class="ms-2 fas fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Learn about refund eligibility"></i>
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We offer a full refund within 30 days of purchase, no questions asked.
                        </div>
                    </div>
                </div>
                <!-- Question 2 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                            How do I contact support?
                            <i class="ms-2 fas fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Support is available 24/7"></i>
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can reach out to us via email at support@example.com or call our hotline at (123) 456-7890.
                        </div>
                    </div>
                </div>
                <!-- Question 3 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                            Is there a free trial available?
                            <i class="ms-2 fas fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Wover trial options"></i>
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, we offer a 14-day free trial with access to all features.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="contact-us-section ">
            <div class="container ">
                <h1 class="text-center">Send Message</h1>
                <div class="row">

                    <!-- Left Column (Text) -->
                    <div class="col-md-6 contact-text">
                        <div class="contact-background" style="background-image: url('{{ asset('web_assets/images/banner.png') }}');">
                            <div class="overlay"></div>
                            <div class="content">
                                <h2>CONTACT US</h2>
                                <p>Let's talk about your problem. We're here to help!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Form) -->
                    <div class="col-md-6 contact-form">

                        <div class="form-wrapper">
                            <form action="#" method="post">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="name" placeholder="Your Name" required>
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control" id="email" placeholder="Your Email" required>
                                </div>
                                <div class="form-group">
                                    <input type="tel" class="form-control" id="phone" placeholder="Your Phone Number" required>
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" id="message" rows="4" placeholder="Your Message" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section> --}}








@if (session()->has('subscribed'))
<script>
    if (window.AdwiseriAlert) {
        AdwiseriAlert.success('Subscription successful. Thank you!');
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'success', title: 'Success', text: 'Subscription successful. Thank you!' });
    }
</script>
@endif

@push('scripts')
<script>
(function () {
    function showNewsletterValidationError(message) {
        var emailInput = document.getElementById('homepage-newsletter-email');

        if (emailInput) {
            emailInput.focus();
        }

        if (window.AdwiseriAlert && typeof window.AdwiseriAlert.oops === 'function') {
            window.AdwiseriAlert.oops(message);
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#695EEE',
                customClass: { icon: 'adwiseri-oops-icon', popup: 'small-swal-popup' }
            });
            return;
        }
        window.alert(message);
    }

    function initHomepageNewsletterValidation() {
        var form = document.getElementById('homepage-newsletter-form');
        var emailInput = document.getElementById('homepage-newsletter-email');
        var submitBtn = document.getElementById('homepage-newsletter-btn');

        if (!form || !emailInput || !submitBtn) {
            return;
        }

        function validateAndSubmit() {
            if (form.dataset.newsletterSubmitting === '1') {
                return true;
            }

            var value = (emailInput.value || '').trim();
            var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!value) {
                showNewsletterValidationError('This field is required.');
                return false;
            }
            if (!regex.test(value)) {
                showNewsletterValidationError('Please enter a valid email address.');
                return false;
            }

            form.dataset.newsletterSubmitting = '1';
            HTMLFormElement.prototype.submit.call(form);
            return true;
        }

        submitBtn.addEventListener('click', function (event) {
            event.preventDefault();
            validateAndSubmit();
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            validateAndSubmit();
        });

        emailInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                validateAndSubmit();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHomepageNewsletterValidation);
    } else {
        initHomepageNewsletterValidation();
    }

    @if ($errors->has('email'))
    showNewsletterValidationError(@json($errors->first('email')));
    @endif
})();
</script>
@endpush

    @endsection()
