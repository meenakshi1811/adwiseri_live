@extends('web.layout.main')

@push('css')
<style>
    /* Get A Demo: illustration beside the form on desktop. */
    .demo-request-page { max-width: 1180px; }
    .demo-request-art { justify-content: center; align-items: center; }
    .demo-request-art img { width: 100%; max-width: 560px; height: auto; display: block; }
    .demo-request-page .register-box { margin-top: 0 !important; }
</style>
@endpush

@section('main-section')
{{-- Illustration on the left, the same form (narrower) on the right; on tablets and phones the
     illustration is hidden and the form takes the full width as before. --}}
<div class="container form-page demo-request-page">
    <div class="row justify-content-center align-items-center g-4">
        <div class="col-lg-5 d-none d-lg-flex demo-request-art" aria-hidden="true">
            <img src="{{ asset('web_assets/images/demo-illustration.svg') }}?v=20261125a" alt="" width="520" height="440">
        </div>
        <div class="col-lg-7 col-md-10 col-sm-12">
            <form id="registration_form" class="register-box login-box mt-5" method="POST" action="{{ route('demo_post') }}">
                @csrf
                <h3 class="mb-2 mt-4 text-center">Request A Demo</h3>
                <div class="log-img mb-5 text-center">
                    {{-- <img src="{{ asset('web_assets/images/loginimg.png') }}" width="60" height="60" alt=""> --}}
                </div>

                <div class="row ">
                    <div class="col-md-6  mb-4">
                        <input name="name" minlength="3" maxlength="100" required type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Name" autocomplete="name">
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <input name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required placeholder="Phone Number" autocomplete="phone">
                        @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <input name="email" minlength="3" maxlength="100" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="Email ID" autocomplete="email">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <select name="country" class="form-control form-select @error('country') is-invalid @enderror" required>
                            <option value="">Select Country</option>
                            @include('partials.country_select_options_by_name', [
                                'countries' => $countries,
                                'phoneForPrefill' => old('phone'),
                            ])
                        </select>
                        @error('country')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <input name="company_name" minlength="3" maxlength="1000" required type="text" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" placeholder="Company/Business Name" autocomplete="Company/Business Name">
                        @error('company_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <input name="job_title" minlength="3" maxlength="1000" required type="text" class="form-control @error('job_title') is-invalid @enderror" value="{{ old('job_title') }}" placeholder="Job Title" autocomplete="Job Title">
                        @error('job_title')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <select name="how_did_hear" id="how_did_hear" class="form-control form-select @error('how_did_hear') is-invalid @enderror" required>
                        <option value="">How did you hear about us?</option>
                        <option {{ (old('how_did_hear') == "LinkedIn") ? 'selected' : ''}} value="LinkedIn">LinkedIn</option>
                        <option {{ (old('how_did_hear') == "Twitter") ? 'selected' : ''}} value="Twitter">Twitter</option>
                        <option {{ (old('how_did_hear') == "YouTube") ? 'selected' : ''}} value="YouTube">YouTube</option>
                        <option {{ (old('how_did_hear') == "Industry friend") ? 'selected' : ''}} value="Industry friend">Industry friend</option>
                        <option {{ (old('how_did_hear') == "Google") ? 'selected' : ''}} value="Google">Google</option>
                        <option {{ (old('how_did_hear') == "Other") ? 'selected' : ''}} value="Other">Other</option>
                    </select>
                    @error('how_did_hear')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Shown only when "Other" is chosen above: where they heard about us. --}}
                <div class="mb-4" id="how_did_hear_other_wrap" @if(old('how_did_hear') !== 'Other') style="display:none" @endif>
                    <input name="how_did_hear_other" id="how_did_hear_other" type="text" minlength="2" maxlength="100"
                        class="form-control @error('how_did_hear_other') is-invalid @enderror"
                        value="{{ old('how_did_hear_other') }}"
                        placeholder="Where did you hear about us?"
                        @if(old('how_did_hear') === 'Other') required @endif>
                    @error('how_did_hear_other')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>



                <div class="col-lg-12 mb-4">
                    {!! NoCaptcha::renderJs() !!}
                    {!! NoCaptcha::display() !!}
                    {{-- @error('g-recaptcha-response')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror --}}
                </div>
                <div class="form-check mb-5">
                    <input class="form-check-input @error('terms') is-invalid @enderror"      data-error="Please check this box to proceed." name="terms" type="checkbox"  id="flexCheckDefault"   >
                    <p class="t small">
                        Yes, I understand and agree to the
                        <a href="{{ route('terms_use') }}" target="_blank">Adwiseri Terms of Service</a>,
                        including the <a href="{{ route('terms_use') }}" target="_blank">User Agreement</a> and
                        <a href="{{ route('privacy_policy') }}" target="_blank">Privacy Policy</a>.
                    </p>
                    {{-- <label class="form-check-label" for="flexCheckDefault">
                        I agree to the <a href="{{route('terms_use')}}" target="_blank">Terms and Conditions</a> and <a href="{{ route('privacy_policy')}}" target="_blank">Privacy Policy</a></label>
                    </label> --}}
                    @error('terms')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                </div>

                <input type="hidden" name="local_time" class="localtime" />
                <div class="row justify-content-center">
                    <div class="col-lg-6 d-flex justify-content-center">
                        <button type="submit" class="btn btn-sm btn-primary btn-fit btn-fit-sm-full mb-5">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
</script>
@error('g-recaptcha-response')
<script>
   Swal.fire({
          icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
          title: 'Oops!',
          text: 'Please complete the reCAPTCHA to proceed.',
      });
</script>
@enderror

<script>
    $(document).ready(() => {
      $("#category").change(function(){
          var category = $(this).val();
          $.ajax({
            url: "{{ route('get_sub_category') }}",
            method: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                category: category,
            },
            cache:false,
            success: function(data){
              console.log(data);
                $("#subcategory").html(data);
            }
          });
        });
        $("#subcategory").change(function(){
          var subcategory = $(this).val();
          if(subcategory == "Other"){
            $("#other_field").css('display','block');
            $("#other").attr('required','true');
          }
          else{
            $("#other_field").css('display','none');
            $("#other").removeAttr("required");
          }
        });
    });
    // "Other" in "How did you hear about us?" opens a box to say where; it is required only then.
    document.addEventListener('DOMContentLoaded', function () {
        var source = document.getElementById('how_did_hear');
        var otherWrap = document.getElementById('how_did_hear_other_wrap');
        var otherInput = document.getElementById('how_did_hear_other');
        if (!source || !otherWrap || !otherInput) { return; }

        function toggleOtherSource() {
            var isOther = source.value === 'Other';
            otherWrap.style.display = isOther ? '' : 'none';
            otherInput.required = isOther;
            if (isOther && document.activeElement === source) { otherInput.focus(); }
        }

        source.addEventListener('change', toggleOtherSource);
        toggleOtherSource();
    });

    document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.querySelector('#flexCheckDefault');
    const form = document.querySelector('#registration_form');

    form.addEventListener('submit', function (event) {
        console.log('Checkbox checked:', checkbox.checked); // Always log the checkbox state

        const recaptchaResponse = grecaptcha.getResponse(); // Get the reCAPTCHA response
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
@if(session()->has('submitted'))
<script>
  Swal.fire({
    icon: 'success',
    title: 'We received your demo request.',
    text: 'A member of our team will be in touch with you soon.'
  })
</script>

@endif

@endsection()
