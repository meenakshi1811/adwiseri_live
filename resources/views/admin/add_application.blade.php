@extends('admin.layout.main')

@section('main-section')

        <div class="col-lg-10 column-client">
            @if(isset($application))
            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 ">
                    <form class="form-inline d-flex justify-content-between w-100">
                        <h3 class="text-primary">Update Application</h3>
                    </form>
                </div>
                <div class="col">
                    <form id="registration_form" class="register-box login-box" method="POST" action="{{ route('register_new_application') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $application->id }}" />
                        <div class="row">
                            <div class="col-md-4 p-1">
                                <label>Client ID<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input type="text" name="client" id="client" required readonly value="{{ $application->client_id }}" class="form-control @error('client') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp">
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application Type<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="job_role" id="job_role" class="form-control form-select @error('job_role') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                    <option value="">Select Application Type</option>
                                    @foreach($job_roles as $job)
                                    <option {{ ($job->job == $application->application_name) ? 'selected' : '' }} value="{{ $job->job }}">{{ $job->job }}</option>
                                    @endforeach
                                </select>
                                @error('job_role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Visa Country<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                            @php
                                $selectedVisaCountry = old('visa_country')
                                    ?: ($application->visa_country ?: ($application->client->visa_country ?? $application->application_country));
                                if (is_numeric($selectedVisaCountry)) {
                                    $selectedVisaCountry = optional($countries->firstWhere('id', (int) $selectedVisaCountry))->country_name;
                                }
                            @endphp
                            <select name="visa_country" id="visa_country" class="form-control form-select @error('visa_country') is-invalid @enderror" aria-describedby="emailHelp" required>
                                    <option value="">Select Visa Country</option>
                                    @foreach($countries as $country)
                                    <option {{ (string) $selectedVisaCountry === (string) $country->country_name ? 'selected':'' }} value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('visa_country')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <!-- <select name="visa_country" id="visa_country" class="form-control form-select @error('visa_country') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                    <option value="">Select Visa Country</option>
                                    @foreach($countries as $country)
                                    <option {{ ($country->country_name == $application->visa_country) ? 'selected' : '' }} value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror -->
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application Start Date<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="job_open_date_display" min="{{ $application->start_date_input ?: date('Y-m-d') }}" type="date" class="form-control date @error('job_open_date') is-invalid @enderror" id="app_start_date" onchange="document.getElementById('app_end_date').setAttribute('min',this.value);" aria-describedby="emailHelp" value="{{ $application->start_date_input ?: '' }}" readonly placeholder="Application Start Date" autocomplete="job_open_date">
                                <input type="hidden" name="job_open_date" value="{{ $application->start_date_input ?: '' }}">
                            @error('job_open_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application Status<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="job_status" class="form-control form-select @error('job_status') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                    <option value="">Select Application Status</option>
                                    <option {{ ($application->application_status == "Pending") ? 'selected' : '' }} value="Pending">Pending (For submission)</option>
                                    <option {{ ($application->application_status == "In Process") ? 'selected' : '' }} value="In Process">In Process (Waiting for decision)</option>
                                    <option {{ ($application->application_status == "Complete") ? 'selected' : '' }} value="Complete">Completed (Application/Appeal decision received)</option>
                                    <option {{ ($application->application_status == "Cancelled") ? 'selected' : '' }} value="Cancelled">Cancelled (Application/Appeal Cancelled by Consultancy/Authorities)</option>
                                    <option {{ ($application->application_status == "Withdrawn") ? 'selected' : '' }} value="Withdrawn">Withdrawn (Application/Appeal Withdrawn by Client)</option>
                                </select>
                            @error('job_status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application End Date</label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="job_completion_date" type="date" class="form-control date @error('job_completion_date') is-invalid @enderror" id="app_end_date" aria-describedby="emailHelp" min="{{ $application->start_date_input ?: date('Y-m-d') }}" value="{{ $application->end_date_input ?: '' }}" placeholder="Application End Date" autocomplete="job_completion_date">
                            @error('job_completion_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Remarks</label>
                            </div>
                            <div class="col-md-8 p-1">
                                <textarea name="job_detail" type="text" maxlength="255" class="form-control @error('job_detail') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{ $application->application_detail }}" rows="3" placeholder="Additional Information">{{ $application->application_detail }}</textarea>
                            @error('job_detail')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-12 p-1 adwiseri-form-actions">
                                <button type="submit" class="btn btn-outline-success login-btn" style="width: 100%;">Submit</button>
                                <!-- <button type="submit" class="form-control btn btn-primary" style="width: fit-content;">Submit</button> -->
                            </div>
                        </div>
                    </form>
                </div>

            </div>
            @else
            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 ">
                    <form class="form-inline d-flex justify-content-between w-100">
                        <h3 class="text-primary">Add New Application</h3>
                    </form>
                </div>
                <div class="col">
                    <form id="registration_form" class="register-box login-box" method="POST" action="{{ route('register_new_application') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 p-1">
                                <label>Client<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="client" id="client" required class="form-control form-select @error('client') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp">
                                    <option value="">Select Client</option>
                                    @foreach($clients as $clint)
                                    <option {{ (old('client') == $clint->id) ? 'selected':'' }} value="{{ $clint->id }}">{{ $clint->name."(".$clint->id.")" }}</option>
                                    @endforeach
                                </select>
                                @error('client')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application Type<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="job_role" id="job_role" class="form-control form-select @error('job_role') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                    <option value="">Select Application Type</option>
                                    @if(old('job_role'))
                                    <option value="{{old('job_role')}}" selected>{{old('job_role')}}</option>
                                    @endif
                                </select>
                                @error('job_role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Visa Country<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="visa_country" id="visa_country" class="form-control form-select @error('visa_country') is-invalid @enderror" aria-describedby="emailHelp" required>
                                    <option value="">Select Visa Country</option>
                                    @foreach($countries as $country)
                                    <option {{ (old('visa_country') == $country->country_name) ? 'selected':'' }} value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('visa_country')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <!-- <select name="visa_country" id="visa_country" class="form-control form-select @error('visa_country') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                    <option value="">Select Visa Country</option>
                                    @foreach($countries as $country)
                                    <option {{ (old('visa_country') == $country->country_name) ? 'selected':'' }} value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror -->
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application Start Date<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="job_open_date" type="date"
                                                    class="form-control date @error('job_open_date') is-invalid @enderror"
                                                    id="job_open_date"
                                                    aria-describedby="emailHelp"
                                                    value="{{ old('job_open_date') ? date('Y-m-d', strtotime(old('job_open_date'))) : null }}"
                                                    placeholder="Application Start Date"
                                                    autocomplete="job_open_date"
                                                    {{-- max={{ date('Y-m-d')}} --}}
                                                      max="{{date('Y-m-d')}}" required/>
                            @error('job_open_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application Status<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="job_status" class="form-control form-select @error('job_status') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{ old('job_status') }}" required>
                                    <option value="">Select Application Status</option>
                                    <option {{ (old('job_status') == "Pending") ? 'selected':'' }} value="Pending">Pending (For submission)</option>
                                    <option {{ (old('job_status') == "In Process") ? 'selected':'' }} value="In Process">In Process (Waiting for decision)</option>
                                    <option {{ (old('job_status') == "Complete") ? 'selected':'' }} value="Complete">Completed (Application/Appeal decision received)</option>
                                    <option {{ (old('job_status') == "Cancelled") ? 'selected':'' }} value="Cancelled">Cancelled (Application/Appeal Cancelled by Consultancy/Authorities)</option>
                                    <option {{ (old('job_status') == "Withdrawn") ? 'selected' : '' }} value="Withdrawn">Withdrawn (Application/Appeal Withdrawn by Client)</option>
                                </select>
                            @error('job_status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application End Date</label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="job_completion_date" type="date"
                        class="form-control date @error('job_completion_date') is-invalid @enderror"
                        id="job_completion_date"
                        aria-describedby="emailHelp"
                        value="{{ old('job_completion_date') ? date('Y-m-d', strtotime(old('job_completion_date'))) : null }}"
                        placeholder="Application End Date"
                        autocomplete="job_completion_date"
                          max="{{date('Y-m-d')}}"
                        {{-- readonly --}}
                        />
                            @error('job_completion_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Remarks</label>
                            </div>
                            <div class="col-md-8 p-1">
                                <textarea name="job_detail" maxlength="255" class="form-control @error('job_detail') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Additional Information" rows="3">{{old('job_detail')}}</textarea>
                            @error('job_detail')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-12 p-1 adwiseri-form-actions">
                                <button type="submit" class="btn btn-outline-success login-btn" style="width: 100%;">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
            @endif
        </div>
    </div>

  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
  </script>
  <script>
      $(document).ready(() => {
        var id = document.getElementById('client').value;
        if(id != ''){
            $.ajax({
                url: '/fetch_visa_country/' + id,
                method: 'GET',
                data: {
                    "_token": "{{ csrf_token() }}",
                    // id: id,
                },
                cache:false,
                success: function(data){
                  console.log(data);
                    $("#visa_country").val(data);
                }
            });
        }
          //
        // alert(document.getElementById('client').value);
          $("#country").change(function(){
            var country = $(this).val();
            // console.log(counrty);
            $.ajax({
                url: 'get_states',
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    country: country,
                },
                cache:false,
                success: function(data){
                  console.log(data);
                    $("#state").html(data);
                }
            });
          });
          $("#client").change(function(){
            var id = $(this).val();
            // console.log(counrty);
            $.ajax({
                url: 'get_job_role',
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    id: id,
                },
                cache:false,
                success: function(data){
                  console.log(data);
                    $("#job_role").html(data);
                }
            });
          });

          $("#client").change(function(){
            var id = $(this).val();
            // console.log(counrty);
            $.ajax({
                url: 'fetch_visa_country/'+id,
                method: 'GET',
                data: {
                    "_token": "{{ csrf_token() }}",
                    // id: id,
                },
                cache:false,
                success: function(data){
                  console.log(data);
                    $("#visa_country").val(data);
                }
            });
          });

          function attachDateValidation(selector, placeholderText) {
            const inputField = document.querySelector(selector);
            if (!inputField) return;
            inputField.addEventListener('change', function () {
                var inputDate = new Date(inputField.value);
                var today = new Date();
                if (inputDate > today) {
                    inputField.value = "";
                    inputField.placeholder = "Future dates are not allowed!";
                    inputField.classList.add('is-invalid');
                } else {
                    inputField.classList.remove('is-invalid');
                    inputField.placeholder = placeholderText;
                }
            });
          }
          attachDateValidation('#job_open_date, #app_start_date', 'Application Start Date');
          attachDateValidation('#job_completion_date, #app_end_date', 'Application End Date');
      });
  </script>
  <script>
      function deleteuser(id){
          var conf = confirm('Delete User');
          if(conf == true){
              window.location.href = "delete_user/"+id+"";
          }
      }
  </script>

  @if(session()->has('deleted'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'User Deleted Successfully!'
      })
    </script>

  @endif

@endsection()
