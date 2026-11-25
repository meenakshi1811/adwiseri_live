@extends('web.layout.main')

@section('main-section')
    <style>
        body {
            background-color: #F5F5F5;
        }
    </style>
    <div class="membership-page">
        <img src="{{ asset('web_assets/images/membership.png') }}" width="1519" height="440" alt="">
        <h1>Upgrade Subscription</h1>
    </div>
    <br>
    <div class="container mt-5 pt-5 mb-5">
        <h1 class="text-primary text-center">Complete Payment to Upgrade Subscription</h1>
        <div class="col mt-4">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-8 loginouter-box">
                    <form id="loginform" class="login-box" method="POST" action="{{ route('make_payment') }}">
                        @csrf
                        <input type="hidden" name="local_time" class="localtime" />
                        <input type="hidden" name="id" value="{{ $user->id }}" />
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <p class="text-dark" style="font-size: 16px;">Subscription Plan</p>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="plan_name" value="{{ $membership->plan_name }}" />
                                <p class="text-dark" style="font-size: 16px;">{{ $membership->plan_name }}</p>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <p class="text-dark" style="font-size: 16px;">Duration</p>
                            </div>
                            <div class="col-md-6">
                                @include('partials.subscription_duration_select', [
                                    'name' => 'plan_duration',
                                    'id' => 'plan_duration',
                                    'selected' => old('plan_duration', 1),
                                    'class' => 'form-select',
                                    'required' => true,
                                ])
                                <small class="text-muted">Long-term signup available — 1, 2, 3, 4, or 5 years with built-in savings.</small>
                            </div>
                        </div>
                        @php
                            $firstQuote = $quotes[1] ?? reset($quotes);
                            $hasCredit = collect($quotes)->contains(fn ($q) => $q['credit_amount'] > 0);
                            $hasWallet = collect($quotes)->contains(fn ($q) => $q['wallet_applied'] > 0);
                        @endphp
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <p class="text-dark" style="font-size: 16px;">Amount (USD)</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-dark" id="plan_amt" style="font-size: 16px;">$ {{ number_format((float) $firstQuote['plan_amount'], 2) }}</p>
                            </div>
                        </div>
                        @if($hasCredit)
                        {{-- The unused part of the current plan, credited towards this upgrade. --}}
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <p class="text-dark mb-0" style="font-size: 16px;">Unused Plan Credit (USD)</p>
                                <small class="text-muted">{{ $firstQuote['credit_days'] }} day(s) left on your {{ strtoupper((string) $firstQuote['credit_plan']) }} plan</small>
                            </div>
                            <div class="col-md-6">
                                <p class="text-dark" id="credit_amt" style="font-size: 16px;">- $ {{ number_format((float) $firstQuote['credit_amount'], 2) }}</p>
                                <small class="text-muted" id="credit_excess_note" @if(($firstQuote['credit_excess'] ?? 0) <= 0) style="display:none" @endif>The remaining $ <span id="credit_excess_amt">{{ number_format((float) ($firstQuote['credit_excess'] ?? 0), 2) }}</span> goes to your wallet.</small>
                            </div>
                        </div>
                        @endif
                        @if($hasWallet)
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <p class="text-dark" style="font-size: 16px;">Wallet Credit (USD)</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-dark" style="font-size: 16px;" id="wallet_amt">- $ {{ number_format((float) $firstQuote['wallet_applied'], 2) }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <p class="text-dark fw-bold" style="font-size: 16px;">Payable (USD)</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-dark fw-bold" id="payable_amt" style="font-size: 16px;">$ {{ number_format((float) $firstQuote['payable'], 2) }}</p>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6">
                            </div>
                            <div class="col-md-6">
                                {{-- Nothing to pay when credits cover the whole amount: the upgrade is confirmed straight away. --}}
                                <button type="submit" name="submit" id="checkout_btn" class="btn btn-primary btn-fit"
                                    data-pay-label="Pay Securely" data-confirm-label="Confirm Upgrade">{{ $firstQuote['payable'] > 0 ? 'Pay Securely' : 'Confirm Upgrade' }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2"></div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        @if (isset($user))
            function upgrade_plan(name) {
                var myformData = new FormData();
                myformData.append('id', "{{ $user->id }}");
                myformData.append('plan_name', name);
                myformData.append('_token', "{{ csrf_token() }}");
                console.log(myformData);
                $.ajax({
                    url: "{{ route('upgrade_membership') }}",
                    method: 'POST',
                    data: myformData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        if (data == "success") {
                            window.location.reload();
                        }
                    }
                });
            }
        @endif
        $(document).ready(function() {
            // Figures for each term come from the server; the page only shows them.
            var quotes = @json($quotes);

            function formatUsd(amount) {
                return '$ ' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function showQuote(duration) {
                var quote = quotes[duration] || quotes[1];
                if (!quote) { return; }
                $("#plan_amt").text(formatUsd(quote.plan_amount));
                $("#credit_amt").text('- ' + formatUsd(quote.credit_amount));
                $("#credit_excess_amt").text(Number(quote.credit_excess || 0).toFixed(2));
                $("#credit_excess_note").toggle((quote.credit_excess || 0) > 0);
                $("#wallet_amt").text('- ' + formatUsd(quote.wallet_applied));
                $("#payable_amt").text(formatUsd(quote.payable));
                var btn = $("#checkout_btn");
                btn.text(quote.payable > 0 ? btn.data('pay-label') : btn.data('confirm-label'));
            }

            showQuote($("#plan_duration").val() || 1);

            $("#plan_duration").change(function() {
                showQuote($(this).val());
            });
        });
    </script>

    {{-- @if (session()->has('error'))
        <script>
            Swal.fire({
                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Invalid referral code.'
            })
        </script>
    @endif --}}

@endsection()
