@extends('web.layout.main')

@section('main-section')
<style>
    .contact-page {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 40%, #f8fafc 100%);
    }

    .contact-hero {
        position: relative;
        min-height: 360px;
        border-radius: 0 0 32px 32px;
        overflow: hidden;
    }

    .contact-hero img {
        width: 100%;
        height: 100%;
        min-height: 360px;
        object-fit: cover;
        filter: brightness(0.45);
    }

    .contact-hero-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
    }

    .contact-grid {
        margin-top: -70px;
        position: relative;
        z-index: 3;
    }

    .contact-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        border: 1px solid #e2e8f0;
    }

    .info-tile {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 10px;
        background: #f8fafc;
    }

    .info-tile small {
        color: #64748b;
        display: block;
    }

    .form-control,
    .form-select,
    textarea.form-control {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 12px;
    }

    .submit-btn {
        background: #1d4ed8;
        border: none;
        border-radius: 10px;
        padding: 12px 20px;
    }
</style>

<div class="contact-page pb-5">
    <section class="contact-hero">
        <img src="{{ asset('admin_assets/contactus/' . $contact->banner) }}" alt="Contact banner">
        <div class="contact-hero-overlay">
            <div class="container text-white">
                <h1 class="display-6 fw-bold mb-2">Let’s Connect</h1>
                <p class="mb-0">Share your query and we’ll get back to you quickly.</p>
            </div>
        </div>
    </section>

    <div class="container contact-grid">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="contact-card p-4 h-100">
                    <h4 class="mb-3">Contact Details</h4>
                    <div class="info-tile">
                        <small>Primary Number</small>
                        <strong>{{ $contact->contact_no ?: '---' }}</strong>
                    </div>
                    <div class="info-tile">
                        <small>Alternate Number</small>
                        <strong>{{ $contact->alternate_no ?: '---' }}</strong>
                    </div>
                    <div class="info-tile">
                        <small>Address</small>
                        <strong>{{ $contact->location ?: '---' }}</strong>
                    </div>
                    <div class="info-tile">
                        <small>Email</small>
                        <strong>{{ $contact->email ?: '---' }}</strong>
                    </div>
                    <div class="info-tile mb-0">
                        <small>Website</small>
                        <strong>{{ $contact->website ?: '---' }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="contact-card p-4 p-md-5">
                    <h3 class="mb-4">Contact Us</h3>
                    <form method="post" action="{{ route('post_contact') }}" id="contact_us">
                        @csrf
                        <input type="hidden" name="local_time" class="localtime" />

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <input type="text" minlength="3" maxlength="100" name="name" @if($user) value="{{$user->name}}" @endif class="form-control" placeholder="Enter Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="phone" pattern="\d*" minlength="9" maxlength="12" @if($user) value="{{$user->phone}}" @endif class="form-control" placeholder="Enter Your Phone No." required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <select name="country" class="form-control form-select" required>
                                    <option value="">Select Your Country</option>
                                    @foreach($countries as $country)
                                        <option @if($user) {{($user->country == $country->country_name) ? 'selected':''}} @endif value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" minlength="3" maxlength="100" name="city" @if($user) value="{{$user->city}}" @endif class="form-control" placeholder="Enter Your City" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <input type="email" minlength="3" maxlength="100" name="email" @if($user) value="{{$user->email}}" @endif class="form-control" placeholder="Enter a Valid Email Address" required>
                            </div>
                            <div class="col-md-6">
                                <textarea class="form-control" name="message" rows="4" placeholder="Enter Your Message" required></textarea>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            {!! NoCaptcha::renderJs() !!}
                            {!! NoCaptcha::display() !!}
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input @error('terms') is-invalid @enderror" name="terms" type="checkbox" data-error="Please check this box to proceed." id="flexCheckDefault">
                            <p class="register-box t small text-dark mb-1">
                                Yes, I understand and agree to the
                                <a href="{{ route('terms_use') }}" target="_blank">Adwiseri Terms of Service</a>,
                                including the <a href="{{ route('terms_use') }}" target="_blank">User Agreement</a> and
                                <a href="{{ route('privacy_policy') }}" target="_blank">Privacy Policy</a>.
                            </p>
                            @error('terms')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary submit-btn w-100">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session()->has('g-recaptcha-response'))
<script>
Swal.fire({icon:'error',title:'Oops!',text:'Please complete the reCAPTCHA to proceed.'});
</script>
@endif

@if(session()->has('message_sent'))
<script>
Swal.fire({icon:'success',title:'Thanks for getting in touch.',text:'We will serve with your query at the earliest.'})
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.querySelector('#flexCheckDefault');
    const form = document.querySelector('#contact_us');

    form.addEventListener('submit', function (event) {
        const recaptchaResponse = grecaptcha.getResponse();
        if (!checkbox.checked) {
            event.preventDefault();
            checkbox.setCustomValidity(checkbox.getAttribute('data-error'));
            checkbox.reportValidity();
        } else if (!recaptchaResponse) {
            event.preventDefault();
            Swal.fire({icon:'error',title:'Oops!',text:'Please complete the reCAPTCHA to proceed.'});
            return false;
        } else {
            checkbox.setCustomValidity('');
            checkbox.reportValidity();
        }
    });

    checkbox.addEventListener('change', function () {
        if (checkbox.checked) checkbox.setCustomValidity('');
    });
});
</script>
@endsection()
