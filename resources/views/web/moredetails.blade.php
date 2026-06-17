@extends('web.layout.main')

@section('main-section')
  <div class="container registration-details-page py-5">
    <div class="row w-100 justify-content-center mb-4">
        <div class="col-12">
            <div class="reg-progress-wrapper">
                <div class="reg-progress">
                    <div class="reg-progress-step completed">
                        <span class="step-circle"><i class="fa fa-check"></i></span>
                        <span class="step-label">Account &amp; Verification</span>
                    </div>
                    <div class="reg-progress-line completed"></div>
                    <div class="reg-progress-step active">
                        <span class="step-circle">2</span>
                        <span class="step-label">Organization Details</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row w-100 justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <form class="details-box login-box register-box" method="POST" action="{{ route('update_user') }}">
              @csrf
              <input type="hidden" name="local_time" class="localtime" />
              <input type="hidden" name="moredetails" value="moredetails">

                <div class="text-center mb-4">
                    <h3>Complete Your Profile</h3>
                    <p class="details-subtitle">
                        Welcome, <strong>{{ $user->name }}</strong>. Please provide your organization and location details to finish setting up your account.
                    </p>
                    <img src="{{ asset('web_assets/images/loginimg.png') }}" width="60" height="60" class="log-img" alt="" style="margin: 0 auto;">
                </div>

                <div class="details-section">
                    <h5 class="details-section-title">Organization Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="organization" class="form-label">Organization Name</label>
                            <input name="organization" id="organization" minlength="3" maxlength="100" required type="text" class="form-control" placeholder="Enter organization name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Your Designation</label>
                            <input name="designation" id="designation" minlength="3" maxlength="100" required type="text" class="form-control" placeholder="e.g. Director, Manager">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="employee_strength" class="form-label">Employee Strength</label>
                            <select name="employee_strength" id="employee_strength" required class="form-select">
                                <option value="">Select team size</option>
                                <option value="1-10">1-10</option>
                                <option value="10-20">10-20</option>
                                <option value="20-50">20-50</option>
                                <option value="50-100">50-100</option>
                                <option value="Above 100">Above 100</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h5 class="details-section-title">Address &amp; Location</h5>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="address_line" class="form-label">Address Line</label>
                            <input name="address_line" id="address_line" minlength="3" maxlength="150" required type="text" class="form-control" placeholder="Street address, suite, etc.">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <select name="country" id="country" required class="form-select">
                                <option value="">Select country</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State / County</label>
                            <select name="state" id="state" required class="form-select">
                                <option value="">Select state or county</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City / Town</label>
                            <input type="text" name="city" id="city" minlength="3" maxlength="100" required class="form-control" placeholder="Enter city or town">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pincode" class="form-label">Postcode</label>
                            <input name="pincode" id="pincode" minlength="3" maxlength="10" style="text-transform:uppercase" required type="text" class="form-control" placeholder="Enter postcode">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select name="timezone" id="timezone" required class="form-select">
                                <option value="">Select timezone</option>
                                @foreach($tzlist as $zone)
                                <option value="{{ $zone }}">{{ $zone }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center mt-2">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100">Complete Registration</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script>
    $(document).ready(function(){
        $("#country").change(function(){
          var country = $(this).val();
          $.ajax({
              url: "{{ route('get_states') }}",
              method: 'POST',
              data: {
                  "_token": "{{ csrf_token() }}",
                  country: country,
              },
              cache: false,
              success: function(data){
                  $("#state").html(data);
              }
          });
          $.ajax({
              url: "{{ route('get_timezone') }}",
              method: 'POST',
              data: {
                  "_token": "{{ csrf_token() }}",
                  country: country,
              },
              cache: false,
              success: function(data){
                  $("#timezone").html(data);
              }
          });
        });
    });
  </script>

@endsection()
