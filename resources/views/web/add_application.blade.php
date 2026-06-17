@extends('web.layout.main')

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
            <form id="registration_form" class="register-box login-box" method="POST" action="{{ route('add_new_application') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $application->id }}" />
                <input type="hidden" name="local_time" class="localtime" />
                <div class="row">
                    <div class="col-md-4 p-1">
                        <label>Client (ID)<span class="text-danger" style="font-size: 18px;">*</span><i class="fa-solid fa-esterisk"></i></label>
                    </div>
                    <div class="col-md-8 p-1">
                        <input type="text" name="client" id="client" required readonly value="{{ ($application->client->name ?? 'N/A') . ' (' . $application->client_id . ')' }}" class="form-control @error('client') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp">
                        <input type="hidden" name="client_id" value="{{ $application->client_id }}">
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Application (ID)<span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                                <input type="text" readonly value="{{ $application->application_name }} ({{ $application->id }})" class="form-control">
                                <input type="hidden" name="job_role" value="{{ $application->application_name }}">
                                @error('job_role')
                                    <span class="invalid-feedback" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                        <!-- <select name="job_role" id="job_role" class="form-control form-select @error('job_role') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                            <option value="">Select Application Type</option>
                            @foreach($job_roles as $job)
                            <option {{ ($job->job == $application->application_name) ? 'selected' : '' }} value="{{ $job->job }}">{{ $job->job }}</option>
                            @endforeach
                        </select>
                        @error('job_role')
                            <span class="invalid-feedback" role="alert">
                                {{ $message }}
                            </span>
                        @enderror -->
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
                                <select name="visa_country" id="visa_country" class="form-control form-select @error('visa_country') is-invalid @enderror" style="background-color: #fff; color:#000 !important;" aria-describedby="emailHelp" required>
                                    <option value="">Select Visa Country</option>
                                    @foreach($countries as $country)
                                    <option {{ (string) $selectedVisaCountry === (string) $country->country_name ? 'selected':'' }} value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                                @error('visa_country')
                                    <span class="invalid-feedback" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror    
                        <!-- <select name="visa_country" id="visa_country" class="form-control form-select @error('visa_country') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                            <option value="">Select Visa Country</option>
                            @foreach($countries as $country)
                            <option {{ ($country->country_name == $application->application_country) ? 'selected' : '' }} value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <span class="invalid-feedback" role="alert">
                                {{ $message }}
                            </span>
                        @enderror -->
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Application Start Date<span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">

                        <input name="job_open_date"
                          type="date"
                         class="form-control date @error('job_open_date') is-invalid @enderror"
                          id="job_open_date"  aria-describedby="emailHelp"
                          max="{{ date('Y-m-d') }}"
                          value="{{ $application->start_date_input ?: '' }}"
                          placeholder="Application Start Date" autocomplete="job_open_date" style="background-color: #fff;">

                    @error('job_open_date')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Application Status<span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                        @php
                            $currentApplicationStatus = old('job_status', $application->application_status ?? '');
                            $isTerminalApplicationStatus = in_array($currentApplicationStatus, ['Withdrawn', 'Cancelled'], true);
                            $endDateEditableStatuses = ['Decision', 'Appeal Decision', 'AR / JR Decision', 'Withdrawn', 'Cancelled'];
                            $isEndDateEditable = in_array($currentApplicationStatus, $endDateEditableStatuses, true);
                        @endphp
                        <select name="job_status" class="form-control form-select js-app-status @error('job_status') is-invalid @enderror" id="exampleInputEmail1" style="background-color: #fff; color:#000 !important;" aria-describedby="emailHelp" required>
                            <option value="">Select Application Status</option>
                            <option {{ ($currentApplicationStatus == "Client Registered") ? 'selected' : '' }} value="Client Registered" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Client Registered") disabled @endif>Client Registered</option>
                            <option {{ ($currentApplicationStatus == "Client Counselled") ? 'selected' : '' }} value="Client Counselled" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Client Counselled") disabled @endif>Client Counselled</option>
                            <option {{ ($currentApplicationStatus == "Preparation") ? 'selected' : '' }} value="Preparation" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Preparation") disabled @endif>Preparation</option>
                            <option {{ ($currentApplicationStatus == "Apointment Booked") ? 'selected' : '' }} value="Apointment Booked" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Apointment Booked") disabled @endif>Apointment Booked</option>
                            <option {{ ($currentApplicationStatus == "Applied") ? 'selected' : '' }} value="Applied" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Applied") disabled @endif>Applied</option>
                            <option {{ ($currentApplicationStatus == "Decision") ? 'selected' : '' }} value="Decision" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Decision") disabled @endif>Decision</option>
                            <option {{ ($currentApplicationStatus == "Appeal Lodged") ? 'selected' : '' }} value="Appeal Lodged" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Appeal Lodged") disabled @endif>Appeal Lodged</option>
                            <option {{ ($currentApplicationStatus == "Appeal Decision") ? 'selected' : '' }} value="Appeal Decision" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Appeal Decision") disabled @endif>Appeal Decision</option>
                            <option {{ ($currentApplicationStatus == "AR / JR Lodged") ? 'selected' : '' }} value="AR / JR Lodged" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "AR / JR Lodged") disabled @endif>AR / JR Lodged</option>
                            <option {{ ($currentApplicationStatus == "AR / JR Decision") ? 'selected' : '' }} value="AR / JR Decision" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "AR / JR Decision") disabled @endif>AR / JR Decision</option>
                            <option {{ ($currentApplicationStatus == "Withdrawn") ? 'selected' : '' }} value="Withdrawn" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Withdrawn") disabled @endif>Withdrawn</option>
                            <option {{ ($currentApplicationStatus == "Cancelled") ? 'selected' : '' }} value="Cancelled" @if($isTerminalApplicationStatus && $currentApplicationStatus !== "Cancelled") disabled @endif>Cancelled</option>
                        </select>
                    @error('job_status')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Application End Date</label>
                    </div>
                    <div class="col-md-8 p-1">
                        <input name="job_completion_date" type="date"
                        class="form-control date js-app-end-date @error('job_completion_date') is-invalid @enderror"
                        id="job_completion_date"
                        aria-describedby="emailHelp"
                        min="{{ $application->start_date_input ?: '' }}"
                        max="{{ date('Y-m-d') }}"
                        value="{{ $isEndDateEditable ? ($application->end_date_input ?: '') : '' }}"
                        placeholder="Application End Date"
                        autocomplete="job_completion_date"
                        @unless($isEndDateEditable) readonly disabled @endunless
                        />     @error('job_completion_date')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Remarks</label>
                    </div>
                    <div class="col-md-8 p-1">
                        <textarea name="job_detail" rows="3" class="form-control @error('job_detail') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{ $application->application_detail }}" placeholder="Additional Information" autocomplete="job_detail">{{ $application->application_detail }}</textarea>
                    @error('job_detail')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                    </div>
                    <div class="col-md-4 p-1">
                            </div>
                            <div class="col-md-8 text-left p-1">
                                <button type="submit" class="btn btn-primary">Submit</button>
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
            <form id="registration_form" class="register-box login-box" method="POST" action="{{ route('add_new_application') }}">
                @csrf
                <input type="hidden" name="local_time" class="localtime" />
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
                                {{ $message }}
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
                                        {{ $message }}
                                    </span>
                                @enderror
                        <!-- <select name="visa_country" id="visa_country" class="form-control @error('visa_country') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                            <option value="">Select Visa Country</option>
                            @foreach($countries as $country)
                            <option {{ (old('visa_country') == $country->country_name) ? 'selected':'' }} value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <span class="invalid-feedback" role="alert">
                                {{ $message }}
                            </span>
                        @enderror -->
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
                                        {{ $message }}
                                    </span>
                                @enderror
                        <!-- <select name="job_role" id="job_role" class="form-control form-select @error('job_role') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                            <option value="">Select Application Type</option>
                            @foreach($client_jobs as $job)
                            <option {{(old('job_role') == $job->job) ? 'selected':''}} value="{{ $job->job }}">{{ $job->job }}</option>
                            @endforeach
                            @if(old('job_role'))
                            <option value="{{old('job_role')}}" selected>{{old('job_role')}}</option>
                            @endif
                        </select>
                        @error('job_role')
                            <span class="invalid-feedback" role="alert">
                                {{ $message }}
                            </span>
                        @enderror -->
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Application Start Date<span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                        {{-- <input name="job_open_date" type="date" max="{{date('Y-m-d')}}"
                        class="form-control date @error('job_open_date') is-invalid @enderror"
                        id="app_start_date" onchange="document.getElementById('app_end_date').setAttribute('min',this.value);"
                        aria-describedby="emailHelp" value="{{ old('job_open_date') }}"
                        required placeholder="Application Start Date"
                        autocomplete="job_open_date"> --}}
                        <input name="job_open_date" type="date"
                                                    class="form-control date @error('job_open_date') is-invalid @enderror"
                                                    id="job_open_date"
                                                    aria-describedby="emailHelp"
                                                    value="{{ old('job_open_date') ? date('Y-m-d', strtotime(old('job_open_date'))) : null }}"
                                                    placeholder="Application Start Date"
                                                    autocomplete="job_open_date"
                                                    {{-- max={{ date('Y-m-d')}} --}}
                                                    required
                                                      max="{{date('Y-m-d')}}"
                                                      {{-- max="{{ date('Y-m-d', strtotime('+2 years')) }}" --}}
                                                    />
                    @error('job_open_date')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Application Status<span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                        <select name="job_status" class="form-control form-select js-app-status @error('job_status') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{ old('job_status') }}" required>
                            <option value="">Select Application Status</option>
                            <option {{ (old('job_status') == "Client Registered") ? 'selected':'' }} value="Client Registered">Client Registered</option>
                            <option {{ (old('job_status') == "Client Counselled") ? 'selected':'' }} value="Client Counselled">Client Counselled</option>
                            <option {{ (old('job_status') == "Preparation") ? 'selected':'' }} value="Preparation">Preparation</option>
                            <option {{ (old('job_status') == "Apointment Booked") ? 'selected':'' }} value="Apointment Booked">Apointment Booked</option>
                            <option {{ (old('job_status') == "Applied") ? 'selected':'' }} value="Applied">Applied</option>
                            <option {{ (old('job_status') == "Decision") ? 'selected':'' }} value="Decision">Decision</option>
                            <option {{ (old('job_status') == "Appeal Lodged") ? 'selected':'' }} value="Appeal Lodged">Appeal Lodged</option>
                            <option {{ (old('job_status') == "Appeal Decision") ? 'selected':'' }} value="Appeal Decision">Appeal Decision</option>
                            <option {{ (old('job_status') == "AR / JR Lodged") ? 'selected':'' }} value="AR / JR Lodged">AR / JR Lodged</option>
                            <option {{ (old('job_status') == "AR / JR Decision") ? 'selected':'' }} value="AR / JR Decision">AR / JR Decision</option>
                            <option {{ (old('job_status') == "Withdrawn") ? 'selected' : '' }} value="Withdrawn">Withdrawn</option>
                            <option {{ (old('job_status') == "Cancelled") ? 'selected':'' }} value="Cancelled">Cancelled</option>
                        </select>
                    @error('job_status')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Application End Date</label>
                    </div>
                    <div class="col-md-8 p-1">
                        @php
                            $selectedApplicationStatus = old('job_status', '');
                            $endDateEditableStatuses = ['Decision', 'Appeal Decision', 'AR / JR Decision', 'Withdrawn', 'Cancelled'];
                            $isEndDateEditable = in_array($selectedApplicationStatus, $endDateEditableStatuses, true);
                        @endphp
                        <input name="job_completion_date" type="date"
                        class="form-control date js-app-end-date @error('job_completion_date') is-invalid @enderror"
                        id="job_completion_date"
                        aria-describedby="emailHelp"
                        value="{{ $isEndDateEditable && old('job_completion_date') ? date('Y-m-d', strtotime(old('job_completion_date'))) : null }}"

                        placeholder="Application End Date"
                        autocomplete="job_completion_date"
                          max="{{date('Y-m-d')}}"
                        @unless($isEndDateEditable) readonly disabled @endunless
                        />
                    @error('job_completion_date')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                    </div>
                    <div class="col-md-4 p-1">
                        <label>Remarks</label>
                    </div>
                    <div class="col-md-8 p-1">
                        <textarea name="job_detail" rows="3" class="form-control @error('job_detail') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Additional Information">{{old('job_detail')}}</textarea>
                    @error('job_detail')
                        <span class="invalid-feedback" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                    </div>
                    <div class="col-md-4 p-1">
                            </div>
                            <div class="col-md-8 text-left p-1">
                                <!-- <button type="submit" class="form-control btn btn-primary" style="width: fit-content;">Submit</button> -->
                                <button type="submit" class="btn btn-primary">Submit</button>
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
        const endDateEditableStatuses = ["Decision", "Appeal Decision", "AR / JR Decision", "Withdrawn", "Cancelled"];

        const syncEndDateEditability = () => {
            const statusField = document.querySelector(".js-app-status");
            const endDateField = document.querySelector(".js-app-end-date");

            if (!statusField || !endDateField) {
                return;
            }

            const canEditEndDate = endDateEditableStatuses.includes(statusField.value);
            endDateField.readOnly = !canEditEndDate;
            endDateField.disabled = !canEditEndDate;

            if (!canEditEndDate) {
                endDateField.value = "";
            }
        };

        const clientEl = document.getElementById('client');
        var id = clientEl ? clientEl.value : '';
        const isClientSelect = clientEl && clientEl.tagName === 'SELECT';
        if(isClientSelect && id != ''){
            $.ajax({
                url: '/fetch_visa_country/' + id,
                method: 'GET',
                data: {
                    "_token": "{{ csrf_token() }}",
                    // id: id,
                },
                cache:false,
                success: function(data){
                    if (data) {
                        $("#visa_country").val(data);
                    }
                }
            });
        }

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
                    if (data) {
                        $("#visa_country").val(data);
                    }
                }
            });
          });

        $(document).on("change", ".js-app-status", syncEndDateEditability);
        syncEndDateEditability();

        $("#job_open_date").on("change", function () {
                var startDate = $(this).val(); // Get the selected start date
                var $endDateInput = $("#job_completion_date");
                const selectedStatus = $(".js-app-status").first().val();
                const canEditEndDate = endDateEditableStatuses.includes(selectedStatus);
                $endDateInput.prop("readonly", !canEditEndDate);
                $endDateInput.prop("disabled", !canEditEndDate);
                // Update the min attribute of the end date
                $endDateInput.attr("min", startDate);

                // Calculate the max date (startDate + 2 years)
                    var maxDate = new Date(startDate);
                    maxDate.setFullYear(maxDate.getFullYear() ); // Add 2 years

                    // Format the maxDate to YYYY-MM-DD
                    var formattedMaxDate = maxDate.toISOString().split("T")[0];

                    console.log(formattedMaxDate);

                    // Update the max attribute of the end date
                    $endDateInput.attr("max", formattedMaxDate);

                // If the current end date is less than the start date, clear it
                if ($endDateInput.val() && $endDateInput.val() < startDate) {
                    $endDateInput.val("");
                }
             });

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
            // Keep server-rendered dates/country intact on edit load; no client-side auto-clearing here.

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
          
        //   $("#client").change(function(){
        //     var id = $(this).val();
        //     // console.log(counrty);
        //     $.ajax({
        //         url: 'get_job_role',
        //         method: 'POST',
        //         data: {
        //             "_token": "{{ csrf_token() }}",
        //             id: id,
        //         },
        //         cache:false,
        //         success: function(data){
        //           console.log(data);
        //             $("#job_role").html(data);
        //         }
        //     });
        //   });
      });
  </script>
  <script>
      function deleteuser(id){
          var conf = confirm('Are you sure you want to delete this application?');
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
        text: 'Application deleted successfully..'
      })
    </script>

  @endif
@endsection()
