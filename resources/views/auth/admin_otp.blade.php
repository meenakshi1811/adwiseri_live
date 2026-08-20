@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6">
            <div class="p-4" style="border:1.27184px solid #695EEE;box-shadow:0px 0px 6.35922px 2.54369px rgba(0,0,0,0.15);border-radius:8px;background:#fff;">

                <div class="text-center mb-4">
                    <div style="width:64px;height:64px;margin:0 auto 14px;border-radius:50%;background:#f3f5fb;display:flex;align-items:center;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#695EEE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h3 class="mb-1" style="font-weight:700;">Two-Factor Verification</h3>
                    <p class="text-muted mb-0" style="font-size:14px;">
                        Enter the 6-digit code we sent to
                        @if(!empty($email))
                            <br><strong>{{ $email }}</strong>
                        @else
                            your registered email.
                        @endif
                    </p>
                </div>

                @if (session('resent'))
                    <div class="alert alert-success text-center py-2" role="alert" style="font-size:14px;">
                        {{ session('resent') }}
                    </div>
                @endif

                @error('otp')
                    <div class="alert alert-danger text-center py-2" role="alert" style="font-size:14px;">
                        {{ $message }}
                    </div>
                @enderror

                <form method="POST" action="{{ route('admin.2fa.verify') }}" id="otp-form">
                    @csrf
                    <input type="hidden" name="otp" id="otp-hidden">

                    <div class="d-flex justify-content-between mb-3" id="otp-inputs" style="gap:8px;">
                        @for ($i = 0; $i < 6; $i++)
                            <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code"
                                class="form-control text-center otp-box"
                                style="height:52px;font-size:22px;font-weight:600;border-radius:8px;" {{ $i === 0 ? 'autofocus' : '' }}>
                        @endfor
                    </div>

                    <div class="text-center mb-3" style="font-size:13px;color:#6b7280;">
                        This code expires in <span id="otp-timer" style="font-weight:700;color:#695EEE;">05:00</span>
                    </div>

                    <button type="submit" class="btn btn-primary form-control" style="height:46px;font-weight:600;">Verify &amp; Continue</button>
                </form>

                <div class="text-center mt-3" style="font-size:14px;">
                    <span class="text-muted">Didn't receive the code?</span>
                    <form method="POST" action="{{ route('admin.2fa.resend') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 align-baseline" style="text-decoration:none;font-weight:600;">Resend OTP</button>
                    </form>
                </div>

                <div class="text-center mt-2">
                    <a href="{{ route('login') }}" style="text-decoration:none;font-size:13px;color:#6b7280;">&larr; Back to login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
        var hidden = document.getElementById('otp-hidden');
        var form = document.getElementById('otp-form');

        function sync() {
            hidden.value = boxes.map(function (b) { return b.value; }).join('');
        }

        boxes.forEach(function (box, idx) {
            box.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);
                if (this.value && idx < boxes.length - 1) {
                    boxes[idx + 1].focus();
                }
                sync();
            });
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    boxes[idx - 1].focus();
                }
            });
            box.addEventListener('paste', function (e) {
                e.preventDefault();
                var digits = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6).split('');
                boxes.forEach(function (b, i) { b.value = digits[i] || ''; });
                sync();
                var next = Math.min(digits.length, boxes.length - 1);
                boxes[next].focus();
            });
        });

        form.addEventListener('submit', sync);

        // Countdown timer based on server-provided expiry (seconds since epoch).
        var expiresAt = {{ (int) ($expiresAt ?? 0) }};
        var timerEl = document.getElementById('otp-timer');
        if (expiresAt > 0) {
            var tick = function () {
                var remaining = expiresAt - Math.floor(Date.now() / 1000);
                if (remaining <= 0) {
                    timerEl.textContent = 'expired';
                    return;
                }
                var m = Math.floor(remaining / 60);
                var s = remaining % 60;
                timerEl.textContent = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                setTimeout(tick, 1000);
            };
            tick();
        }
    })();
</script>
@endsection
