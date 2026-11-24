@extends('web.layout.main')

@section('main-section')

<style>
    /* Contact form in a card like the price plan tables: a solid purple header band with the title,
       the fields below. No banner image, so the card and the footer fit on one screen. */
    /* Normal spacing (not full-screen centring), so the footer follows right after the card. */
    .contact-page {
    padding-top: 32px;
    padding-bottom: 40px;
    }
    /* This page only: the footer follows the card without the usual 96px gap. */
    .ds-footer-wrap {
    margin-top: 0 !important;
    }
    .contact-form .form-box {
    max-width: 720px;
    padding: 0 30px 22px;
    overflow: hidden;
    }
    .contact-form .contact-card-head {
    margin: 0 -30px 20px;
    padding: 18px 24px 16px;
    background: var(--ds-grad-btn, linear-gradient(135deg, #7468F2 0%, #695EEE 45%, #4C3BB7 100%));
    text-align: center;
    }
    .contact-form .contact-card-head h1 {
    margin: 0;
    font-size: clamp(24px, 2.6vw, 30px);
    color: #fff !important;
    }
    .contact-form .contact-card-head p {
    margin: 4px 0 0;
    font-size: 14px;
    color: rgba(255, 255, 255, .88);
    }
    .contact-form .form-box .form-control {
    padding: 8px 14px;
    }
    .contact-form .form-box .form-check p {
    margin-bottom: 0;
    }
</style>

  <div class="container contact-form contact-page">
    <div class="row contact-row">
        <div class="col-md-10 col-lg-8 col-xl-7 mx-auto form-box">
            <div class="contact-card-head">
                <h1>Get in Touch</h1>
                <p>Send us a message and our team will get back to you.</p>
            </div>
            <form method="post" action="{{ route('post_contact') }}" id="contact_us">
                @csrf
                <input type="hidden" name="local_time" class="localtime" />

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" minlength="3" maxlength="100" name="name"
                               @if($user) value="{{$user->name}}" @endif
                               class="form-control"
                               placeholder="Enter Your Name" required>
                    </div>
                    <div class="col-md-6">
                        <input type="tel" name="phone"
                               @if($user) value="{{$user->phone}}" @endif
                               class="form-control"
                               placeholder="Enter Your Phone No." required>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <select style="font-size: 14px;" name="country"
                                class="form-control form-select" required>
                            <option value="">Select Your Country</option>
                            @include('partials.country_select_options_by_name', [
                                'countries' => $countries,
                                'phoneForPrefill' => $user->phone ?? null,
                                'savedCountry' => $user->country ?? null,
                            ])
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" minlength="3" maxlength="100" name="city"
                               @if($user) value="{{$user->city}}" @endif
                               class="form-control"
                               placeholder="Enter Your City" required>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <input type="email" minlength="3" maxlength="100" name="email"
                               @if($user) value="{{$user->email}}" @endif
                               class="form-control"
                               placeholder="Enter a Valid Email Address" required>
                    </div>
                    <div class="col-12">
                        <textarea class="form-control" name="message"
                                  rows="3" placeholder="Enter Your Message" required></textarea>
                    </div>
                </div>

                <div class="form-group mb-2">
                    {!! NoCaptcha::renderJs() !!}
                    {!! NoCaptcha::display() !!}
                    {{-- @error('g-recaptcha-response')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror --}}
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input @error('terms') is-invalid @enderror"
                           name="terms" type="checkbox"
                         data-error="Please check this box to proceed."
                           id="flexCheckDefault" >
                    {{-- <label class="form-check-label" for="flexCheckDefault">
                        I agree to the <a href="{{route('terms_use')}}" target="_blank">Terms and Conditions</a>
                        and <a href="{{ route('privacy_policy')}}" target="_blank">Privacy Policy</a>
                    </label> --}}
                    <p class=" register-box t small text-dark">
                        Yes, I understand and agree to the
                        <a href="{{ route('terms_use') }}" target="_blank">Adwiseri Terms of Service</a>,
                        including the <a href="{{ route('terms_use') }}" target="_blank">User Agreement</a> and
                        <a href="{{ route('privacy_policy') }}" target="_blank">Privacy Policy</a>.
                    </p>

                    @error('terms')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-6 d-flex justify-content-center">
                        <button type="submit" class="btn btn-sm btn-primary btn-fit btn-fit-sm-full">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session()->has('g-recaptcha-response'))
<script>
Swal.fire({
  icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
  title: 'Oops!',
  text: 'Please complete the reCAPTCHA to proceed.',
});
</script>
@endif
<script>

  @if(session()->has('message_sent'))
  
    Swal.fire({
        icon: 'success',
        title: 'Thanks for getting in touch.',
        text: 'We will respond to your query as soon as possible.'
    })

document.querySelector('#contact_us').addEventListener('submit', function (event) {
        const checkbox = document.querySelector('#flexCheckDefault');
        if (!checkbox.checked) {
            event.preventDefault(); // Prevent form submission
            checkbox.setCustomValidity(checkbox.getAttribute('data-error')); // Set custom error
            checkbox.reportValidity(); // Display the error
        } else {
            checkbox.setCustomValidity(''); // Clear custom error if valid
        }
    });
  </script>
  @endif
  <script>

    document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.querySelector('#flexCheckDefault');
    const form = document.querySelector('#contact_us');

    form.addEventListener('submit', function (event) {
        console.log('Checkbox checked:', checkbox.checked); // Always log the checkbox state
        const recaptchaResponse = grecaptcha.getResponse(); // Get the reCAPTCHA response

// Check if reCAPTCHA is completed

        if (!checkbox.checked) {
            event.preventDefault(); // Prevent form submission
            checkbox.setCustomValidity(checkbox.getAttribute('data-error')); // Set error message
            checkbox.reportValidity(); // Display the error message
        }else if (!recaptchaResponse) {
            event.preventDefault(); // Prevent form submission
            Swal.fire({
                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Please complete the reCAPTCHA to proceed.',
            });
            return false; // Stop form submission
        }else {
            checkbox.setCustomValidity(''); // Clear the error message
            checkbox.reportValidity(); // Ensure no residual error message
        }
    });

    // Reset custom validity whenever the checkbox is toggled
    checkbox.addEventListener('change', function () {
        if (checkbox.checked) {
            checkbox.setCustomValidity(''); // Clear error
        }
    });
});
  </script>

@endsection()
