@extends('web.layout.main')
<style>
    .error {
        border: 2px red solid !important;
    }

    input {
        width: 100% !important;
    }

    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        padding-top: 100px;
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        float: right !important;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    #myModal {
        z-index: 1050;
    }

    #myModal .modal-dialog,
    #myModal .modal-content,
    #myModal .modal-body {
        overflow: visible;
    }

    #myModal .app-date-field-wrap {
        position: relative;
    }

    #myModal .app-date-field-wrap .flatpickr-calendar {
        position: relative !important;
        top: 0 !important;
        left: 0 !important;
        margin-top: 4px;
        box-shadow: 0 3px 13px rgba(0, 0, 0, 0.08);
    }

    .flatpickr-calendar.open {
        z-index: 10000;
    }
</style>


@section('main-section')
@php
use App\Models\UserRoles;
use App\Support\ModuleAvailability;
$client_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Clients')->first();
$application_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Applications')->first();
$communication_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Communication')->first();
$invoice_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Invoices')->first();
$payment_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Payments')->first();
$report_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Reports')->first();
$subscription_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Subscription')->first();
$setting_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Settings')->first();
$support_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Support')->first();
@endphp
        <div class="col-lg-10 column-client">
            <div class="client-dashboard">

                <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                    <h3 class="text-primary text-center flex-grow-1 text-center m-0">Clients</h3>
                    <div class="d-flex gap-2">
                    @php
                    $encryptedId = encrypt($user->id);
                    @endphp
                    <a href="{{ route('createLead', $encryptedId) }}" class="btn btn-primary btn-sm">
                    Add Enquiry
                    </a>
                      <a
                        @if($client_roles->write_only == 1 or $client_roles->read_write_only == 1)
                          href="{{ route('add_client') }}"
                        @else
                          href="#"
                        @endif
                        class="btn btn-primary btn-sm"
                      >
                        Add Client
                      </a>
                      @if($user->user_type == "Subscriber")
                        @if(ModuleAvailability::hasClients($user))
                        <a href="javascript:void(0)" id="AddApplication" class="btn btn-primary">Add Application</a>
                        <a href="javascript:void(0)" id="AddDependent" class="m-0">Add Spouse/Dependant</a>
                        @else
                        <a href="javascript:void(0)" onclick="showNoClientAlert(); return false;" class="btn btn-primary">Add Application</a>
                        <a href="javascript:void(0)" onclick="showNoClientAlert(); return false;" class="m-0">Add Spouse/Dependant</a>
                        @endif
                      @endif
                    </div>
                  </div>
                  <div class="client-btn d-flex justify-content-between mb-4">
                    <div id="myModal" class="modal" tabindex="-1">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h5 class="modal-title text-primary">Add Application</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <!-- Modal Body -->
                                <div class="modal-body">
                                    <form id="add-client-application">
                                        @csrf
                                        <input type="hidden" name="confirm_duplicate" value="0">

                                        <!-- Subscriber Selection -->

                                        <!-- Client Selection -->
                                        <div class="mb-3">
                                            <label for="clients" class="form-label">Clients <span class="text-danger" style="font-size: 18px;">*</span></label>
                                            <select name="client_id" id="add-clients-app" required
                                                class="form-control form-select @error('client_id') is-invalid @enderror" >
                                                <option value="">Select Client</option>
                                                @foreach ($clients as $client )
                                                <option value="{{$client->id}}" data-subscriberId="{{$client->subscriber_id}}">{{$client->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('client_id')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <!-- Application Type -->
                                        <div class="mb-3">
                                            <label for="job_role" class="form-label">Application Type <span class="text-danger" style="font-size: 18px;">*</span></label>
                                            <select name="job_role" id="job_role" required
                                                class="form-control form-select @error('job_role') is-invalid @enderror">
                                                <option value="">Select Application Type</option>
                                            </select>
                                            @error('job_role')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <!-- Visa Country -->
                                        <div class="mb-3">
                                            <label for="visa_country" class="form-label">Visa Country <span class="text-danger" style="font-size: 18px;">*</span></label>
                                            <select name="visa_country" id="visa_country" required
                                                class="form-control form-select @error('visa_country') is-invalid @enderror">
                                                <option value="">Select Visa Country</option>
                                                @foreach ($countries as $country)
                                                    <option {{ old('visa_country') == $country->country_name ? 'selected' : '' }}
                                                        value="{{ $country->country_name }}">{{ $country->country_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('visa_country')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <!-- Application Dates -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="app_start_date" class="form-label">Application Start Date <span class="text-danger" style="font-size: 18px;">*</span></label>
                                                <div class="app-date-field-wrap">
                                                    <input name="job_open_date" type="text"
                                                    class="form-control app-modal-datepicker @error('job_open_date') is-invalid @enderror"
                                                    id="app_modal_job_open_date"
                                                    data-calendar-init="1"
                                                    aria-describedby="emailHelp"
                                                    value="{{ old('job_open_date') ? date('d-m-Y', strtotime(old('job_open_date'))) : '' }}"
                                                    placeholder="DD-MM-YYYY"
                                                    autocomplete="off"
                                                    inputmode="numeric"
                                                    required
                                                    />
                                                </div>
                                                @error('job_open_date')
                                                <span class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="app_end_date" class="form-label">Application End Date </label>
                                                <div class="app-date-field-wrap">
                                                    <input name="job_completion_date" type="text"
                                                    class="form-control app-modal-datepicker @error('job_completion_date') is-invalid @enderror"
                                                    id="app_modal_job_completion_date"
                                                    data-calendar-init="1"
                                                    aria-describedby="emailHelp"
                                                    value="{{ old('job_completion_date') ? date('d-m-Y', strtotime(old('job_completion_date'))) : date('d-m-Y') }}"
                                                    placeholder="DD-MM-YYYY"
                                                    autocomplete="off"
                                                    inputmode="numeric"
                                                    />
                                                </div>
                                                @error('job_completion_date')
                                                <span class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Application Status -->
                                        <div class="mb-3">
                                            <label for="job_status" class="form-label">Application Status <span class="text-danger" style="font-size: 18px;">*</span></label>
                                            <select name="job_status" id="job_status" required
                                                class="form-control form-select @error('job_status') is-invalid @enderror">
                                                <option value="">Select Application Status</option>
                                                <option {{ (old('job_status', 'Client Registered') == "Client Registered") ? 'selected':'' }} value="Client Registered">Client Registered</option>
                                                <option {{ (old('job_status') == "Applied") ? 'selected':'' }} value="Applied">Applied</option>
                                                <option {{ (old('job_status') == "Cancelled") ? 'selected':'' }} value="Cancelled">Cancelled (Application/Appeal Cancelled by Consultancy/Authorities)</option>
                                                <option {{ (old('job_status') == "Withdrawn") ? 'selected' : '' }} value="Withdrawn">Withdrawn (Application/Appeal Withdrawn by Client)</option>
                                            </select>
                                            @error('job_status')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        @include('web.partials.application_visa_detail_fields', ['layout' => 'modal'])

                                        <div class="mb-3">
                                            <label>Remarks</label>
                                            <textarea rows="3" maxlength="255" name="job_detail"
                                                class="form-control @error('job_detail') is-invalid @enderror" id="exampleInputEmail1"
                                                aria-describedby="emailHelp" value="{{ old('job_detail') }}" placeholder="Additional Information"></textarea>
                                            @error('job_detail')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success">Add Application</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Modal Footer -->
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="myDependent" class="modal" tabindex="-1">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h5 class="modal-title text-primary">Add Spouse/Dependant</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <!-- Modal Body -->
                                <div class="modal-body">
                                    <form id="add-client-dependent">
                                        @csrf

                                        <!-- Subscriber Selection -->

                                        <!-- Client Selection -->
                                        <div class="mb-3">
                                            <label for="clients" class="form-label">Client <span class="text-danger" style="font-size: 18px;">*</span></label>
                                            <select name="client_id" id="add-clients-dependent" required
                                                class="form-control form-select @error('client_id') is-invalid @enderror">
                                                <option value="">Select Client</option>
                                                @foreach ($clients as $client )
                                                <option value="{{$client->id}}">{{$client->name.'--('.$client->id.')' }}</option>
                                                @endforeach
                                            </select>
                                            @error('client_id')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <!-- Application Type -->
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name <span class="text-danger" style="font-size: 18px;">*</span></label>
                                            <input name="name" type="text" id="app_end_date" placeholder="Spouse/Dependant Name"
                                                    class="form-control @error('name') is-invalid @enderror">
                                            @error('name')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <!-- Visa Country -->
                                        <div class="mb-3">
                                            <label for="gender" class="form-label"> Gender <span class="text-danger" style="font-size: 18px;">*</span></label>
                                            <select name="gender" id="gender" required
                                                class="form-control form-select @error('gender') is-invalid @enderror">
                                                <option value="">Select  Gender</option>
                                                    <option {{ old('gender') == 'Male' ? 'selected' : '' }}value="Male">Male</option>
                                                    <option {{ old('gender') == 'Female' ? 'selected' : '' }}value="Female">Female</option>
                                                    <option {{ old('gender') == 'Prefer Not To Say' ? 'selected' : '' }}value="Prefer Not To Say">Prefer Not to Say</option>
                                            </select>
                                            @error('gender')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="relation" class="form-label">Relation with client <span class="text-danger" style="font-size: 18px;">*</span></label>
                                            <select name="relation" id="job_status" required
                                                class="form-control form-select @error('relation') is-invalid @enderror">
                                                <option value="">Select Relation</option>
                                                <option {{ old('relation') == 'Husband' ? 'selected' : '' }} value="Husband">Husband</option>
                                                <option {{ old('relation') == 'Wife' ? 'selected' : '' }} value="Wife">Wife</option>
                                                <option {{ old('relation') == 'Son' ? 'selected' : '' }} value="Son">Son</option>
                                                <option {{ old('relation') == 'Daughter' ? 'selected' : '' }} value="Daughter">Daughter</option>
                                                <option {{ old('relation') == 'Father' ? 'selected' : '' }} value="Father">Father</option>
                                                <option {{ old('relation') == 'Mother"' ? 'selected' : '' }} value="Mother">Mother</option>
                                                <option {{ old('relation') == 'Other"' ? 'selected' : '' }} value="Other">Other</option>

                                                </select>
                                            @error('relation')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <!-- Application Dates -->
                                            <div class="mb-3">
                                                <label for="app_start_date" class="form-label">DateOfBirth <span class="text-danger" style="font-size: 18px;">*</span></label>
                                                <input name="dob" type="date"
                                                    class="form-control date @error('dob') is-invalid @enderror"
                                                    id="dob"
                                                    aria-describedby="emailHelp"
                                                    value="{{ old('dob') ? date('Y-m-d', strtotime(old('dob'))) : '' }}"
                                                    placeholder="{{ date('d-m-Y')}}"
                                                    autocomplete="bday"
                                                    max="{{ date('Y-m-d')}}"
                                                    />
                                                    @error('dob')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                <!-- <input name="dob" type="text"
                                                    class="form-control date @error('dob') is-invalid @enderror"
                                                    id="dob"
                                                    aria-describedby="emailHelp"
                                                    value="{{ old('dob') ? date('Y-m-d', strtotime(old('dob'))) : null }}"
                                                    placeholder="DateOfBirth"
                                                    autocomplete="dob"
                                                    max={{ date('Y-m-d')}}
                                                    required
                                                   onfocus="(this.type='date')"
                                                    onblur="(this.type='text')"
                                                    />
                                                @error('dob')
                                                <span class="invalid-feedback">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror -->

                                        </div>

                                        <!-- Application Status -->

                                        <div class="mb-3">
                                            <label>Passport No</label>
                                                <input oninput="validateInput(this)" name="passport_no" type="text" class="form-control @error('passport_no') is-invalid @enderror"
                                                    id="passport_no" value="{{ old('passport_no') }}" placeholder="Passport Number" minlength="6" maxlength="14" pattern="^[A-Z0-9]+$"
                                                    onkeyup="this.value = this.value.toUpperCase();">
                                            @error('passport_no')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success">Add Spouse/Dependant</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Modal Footer -->
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('partials.client_module_tabs', ['activeTab' => 'clients'])
                @if(count($clients) != 0)
                @include('partials.table_filter_toolbar', [
                    'filterItems' => $clientVisaCountryFilters ?? [],
                    'tableId' => 'clientTable',
                    'toolbarTitle' => 'Clients by Visa (Destination) Country',
                    'totalCount' => count($clients),
                ])
                <div class="table-wrapper">
                    <table class="fl-table table table-hover table-responsive p-0 m-0" id="clientTable">
                        <thead>
                        <tr>
                            <th class="p-1 text-center">Sr No.</th>
                            <!-- <th class="p-1 text-center">Client Name(Sub_ID)</th> -->
                            <th class="p-1 text-center">Client Name (ID)</th>
                            <th class="p-1 text-center">Phone No</th>
                            <th class="p-1 text-center">Email</th>
                            <th class="p-1 text-center">Country</th>
                            <th class="p-1 text-center">City/Town</th>
                            <th class="p-1 text-center">Postcode</th>
                            <th class="p-1 text-center">NOA </th>
                            <th class="p-1 text-center">Created </th>
                            <th class="p-1 text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($clients as $client)
                        @php
                            $clientVisaCountry = \App\Services\TableFilterCountService::clientVisaCountry($client);
                            $clientFilterKey = \App\Services\TableFilterCountService::keyFor($clientVisaCountry);
                        @endphp
                        <tr data-filter-value="{{ $clientFilterKey }}">
                            <td class="p-1">{{ $client->id }}</td>

                            <td class="p-1 text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $client->name }}" style="position: relative;">@if(strlen($client->name) > 22){{ substr($client->name, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{$client->name}} ({{$client->id}})</span> @else {{$client->name}} ({{$client->id}})@endif</td>
                            <td class="p-1 text-center">@include('partials.phone_display', ['phone' => $client->phone])</td>
                            <td class="p-1 text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $client->email }}" style="position: relative;">@if(strlen($client->email) > 22){{ substr($client->email, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{$client->email}}</span> @else {{$client->email}} @endif</td>
                            <td class="p-1 text-center">{{ $client->country }}</td>
                            <td class="p-1 text-center">{{ $client->city }}</td>
                            <td class="p-1 text-center">{{ $client->pincode }}</td>
                            <td class="text-center"> {{ 1 + (int) ($client->dependants_count ?? 0) }}</td>
                            <td class="text-center">{{  \Carbon\Carbon::parse($client->created_at)->format('d-m-Y H:i:s') }}</td>
                            <td class="text-center action-icon p-1">
                                <a @if($client_roles->read_only == 1 or $client_roles->read_write_only == 1) href="{{ route('client_profile', $client->id)}}" @else href="#" @endif style="text-decoration:none;"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:12px;"></i></a>
                                <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:12px;" @if($client_roles->delete_only == 1) onclick="deleteclient({{ $client->id }})" @endif></i>


                            </td>
                        </tr>
                        @endforeach
                        <tbody>
                    </table>
                </div>
                @else
                <p class="text-secondary px-3">Clients Not added...</p>
                @endif
                {{-- <div class="table-btn">
                    <button>Previous</button>
                    <button>1</button>
                    <button>2</button>
                    <button>3</button>
                    <button>Next</button>
                </div> --}}
            </div>
        </div>
    </div>

  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  
  <script>
      document.addEventListener("DOMContentLoaded", function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
function validateInput(input) {
    input.value = input.value.replace(/[^A-Za-z0-9]/g, '');
}
</script>
<script>
     // Close Modal on "Close" button or when clicking outside
     document.getElementById('dob').addEventListener('change', function () {
                var inputField = this;
                var inputDate = new Date(inputField.value); // Get the selected date
                var today = new Date(); // Current date

                // Check if the input date is in the future
                if (inputDate > today) {
                    inputField.value = ""; // Clear the invalid value
                    inputField.placeholder = "Future dates are not allowed."; // Show error in the placeholder
                    inputField.classList.add('is-invalid'); // Add red border for invalid input
                } else {
                    inputField.classList.remove('is-invalid'); // Remove error state
                    inputField.placeholder = "Payment Date"; // Reset placeholder
                }
            });
            
    function deleteclient(id){
      var localtime = new Date();
        var conf = confirm('Are you sure you want to delete this client?');
        if(conf == true){
            window.location.href = "delete_client/"+id+"/"+localtime.toString()+"";
        }
    }

       // Get modal, buttons, and close elements
var modal = document.getElementById("myModal");
var btn = document.getElementById("AddApplication");
var btnDependent = document.getElementById("AddDependent"); // Only if exists
var modalDependent = document.getElementById("myDependent"); // Only if exists
var closeButtons = document.querySelectorAll(".btn-close, .close");

// Open modal on button click
if (btn) {
    btn.onclick = function () {
        modal.style.display = "block";
        if (typeof window.initApplicationModalDatePickers === 'function') {
            window.initApplicationModalDatePickers();
        }
    };
}

// (Optional) Open affiliate modal on button click
if (btnDependent && modalDependent) {
    btnDependent.onclick = function () {
        modalDependent.style.display = "block";
        $.ajax({
                    url: 'getClient',
                    method: 'get',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: id,
                        name: name,
                    },
                    cache: false,
                    success: function(data) {
                        console.log(data);

                        if (data.clients.length > 0) {
                            var options =
                            '<option value="">Select Client</option>'; // Default option
                            data.clients.forEach(function(client) {
                                // Add each client as an option in the dropdown
                                options += '<option value="' + client.id + '">' + client
                                    .name + '</option>';
                            });
                            $("#add-clients-dependent").html(
                            options); // Update the clients dropdown with the new options
                        } else {
                            // Optionally handle the case when no clients are found
                            $("#add-clients-dependent").html(
                            '<option value="">No clients available</option>');
                        }
                    }
                });
    };
}

// Close modal on clicking close buttons
closeButtons.forEach(function (closeButton) {
    closeButton.onclick = function () {
        modal.style.display = "none";
        if (modalDependent) modalDependent.style.display = "none";
    };
});

// Close modal if clicking outside of it
window.onclick = function (event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
    if (modalDependent && event.target == modalDependent) {
        modalDependent.style.display = "none";
    }
};

        $(document).ready(function() {
            const applicationDatePickers = { start: null, end: null };

            function getFlatpickrOptions(input, extraOptions) {
                return Object.assign({
                    dateFormat: "d-m-Y",
                    allowInput: true,
                    clickOpens: true,
                    disableMobile: true,
                    maxDate: "today",
                    static: true,
                    appendTo: input.parentElement,
                    defaultDate: input.value || null
                }, extraOptions || {});
            }

            window.initApplicationModalDatePickers = function() {
                var startInput = document.getElementById('app_modal_job_open_date');
                var endInput = document.getElementById('app_modal_job_completion_date');

                if (!startInput || !endInput || typeof flatpickr === 'undefined') {
                    return;
                }

                [startInput, endInput].forEach(function(input) {
                    input.type = 'text';
                    input.readOnly = false;
                    if (input._flatpickr) {
                        input._flatpickr.destroy();
                    }
                });

                applicationDatePickers.start = flatpickr(startInput, getFlatpickrOptions(startInput, {
                    onChange: function(selectedDates, dateStr) {
                        if (applicationDatePickers.end) {
                            applicationDatePickers.end.set('minDate', dateStr || null);
                        }
                    }
                }));

                applicationDatePickers.end = flatpickr(endInput, getFlatpickrOptions(endInput, {
                    defaultDate: endInput.value || "today"
                }));
            };
    document.getElementById('dob').addEventListener('change', function () {
        var inputField = this;
        var inputDate = new Date(inputField.value); // Get the selected date
        var today = new Date(); // Current date

        // Check if the input date is in the future
        if (inputDate > today) {
            inputField.value = ""; // Clear the invalid value
            inputField.placeholder = "Future dates are not allowed."; // Show error in the placeholder
            inputField.classList.add('is-invalid'); // Add red border for invalid input
        } else {
            inputField.classList.remove('is-invalid'); // Remove error state
            inputField.placeholder = "DOB"; // Reset placeholder
        }
    });
            $("#subscriber").change(function() {
                var subscriber = $(this).val();
                $.ajax({
                    url: 'check_client_limit',
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        subscriber: subscriber,
                    },
                    cache: false,
                    success: function(data) {
                        //   console.log(data);
                        if (data.limit == 'full') {
                            Swal.fire({
                                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                                title: 'Oops!',
                                text: 'Client limit reached for this subscriber.'
                            });
                            setTimeout(function() {
                                window.location.reload();
                            }, 5000);
                        }
                        if (data.clients.length > 0) {
                            var options =
                            '<option value="">Select Client</option>'; // Default option
                            data.clients.forEach(function(client) {
                                // Add each client as an option in the dropdown
                                options += '<option value="' + client.id + '">' + client
                                    .name +'('+client.subscriber.name +')'+'</option>';
                            });
                            $("#clients").html(
                            options); // Update the clients dropdown with the new options
                        } else {
                            // Optionally handle the case when no clients are found
                            $("#clients").html(
                            '<option value="">No clients available</option>');
                        }
                    }
                });
            });

            $("#add-clients-app").change(function() {
                var clientId = $(this).val();

                if (!clientId) {
                    $("#job_role").html('<option value="">Select Application Type</option>');
                    @if(isset($subscriber))
                    $.post("{{ route('get_cc_countries') }}", {
                        _token: "{{ csrf_token() }}",
                        subscriber_id: {{ $subscriber->id }}
                    }, function (html) {
                        $("#visa_country").html(html);
                    });
                    @endif
                    return;
                }

                $.get('fetch_visa_country/' + clientId, function (selectedCountry) {
                    $.post("{{ route('get_cc_countries') }}", {
                        _token: "{{ csrf_token() }}",
                        client_id: clientId,
                        selected: selectedCountry || ''
                    }, function (html) {
                        $("#visa_country").html(html);
                    });
                });

                $.ajax({
                    url: "{{ route('get_job_role') }}",
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: clientId,
                    },
                    cache: false,
                    success: function(data) {
                        $("#job_role").html(data);
                        if (typeof window.syncApplicationVisaDetailFields === 'function') {
                            window.syncApplicationVisaDetailFields();
                        }
                    }
                });
            });

            @if(isset($subscriber))
            $.ajax({
                url: "{{ route('get_job_role') }}",
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    id: {{ $subscriber->id }},
                    name: 'subscriber',
                },
                cache: false,
                success: function(data) {
                    $("#job_role").html(data);
                    if (typeof window.syncApplicationVisaDetailFields === 'function') {
                        window.syncApplicationVisaDetailFields();
                    }
                }
            });
            @endif
            $("#subscriber").change(function() {
                var id = $(this).val();
                var name = 'subscriber';
                // console.log(counrty);
                $.ajax({
                    url: "{{ route('get_job_role') }}",
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: id,
                        name: name,
                    },
                    cache: false,
                    success: function(data) {
                        console.log(data);
                        $("#job_role").html(data);
                        if (typeof window.syncApplicationVisaDetailFields === 'function') {
                            window.syncApplicationVisaDetailFields();
                        }
                    }
                });
            });

            $('#add-client-dependent').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Proceed with AJAX call if validation passes
                const formData = $(this).serialize();
                $.ajax({
                    url: "{{ url('addClientDependent') }}",
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        });
                        setTimeout(function () {
                        location.reload();
                    }, 5000); // 5000 milliseconds = 5 seconds

                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'Failed to save spouse/dependant details.',
                        });
                    },
                });
            });
            $('#add-client-application').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $confirmField = $form.find('input[name="confirm_duplicate"]');

                function postApplication() {
                    $.ajax({
                        url: "{{ url('addClientApplication') }}",
                        method: 'POST',
                        data: $form.serialize(),
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            });
                            setTimeout(function () {
                                location.reload();
                            }, 5000);
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON || {};
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: response.message || 'Failed to save application details.',
                            });
                        },
                    });
                }

                $confirmField.val('0');
                window.submitApplicationWithDuplicateCheck({
                    clientId: $form.find('[name="client_id"]').val(),
                    applicationName: $form.find('[name="job_role"]').val(),
                    confirmField: $confirmField,
                    onSubmit: function () {
                        postApplication();
                    }
                });
            });

        });

</script>

@if(session()->has('deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Client deleted successfully.'
    })
  </script>

@endif
@if(session()->has('client_limit'))
  <script>
    // Swal.fire({
    //   icon: 'warning',
    //   title: 'Client Limit Reached!',
    //   text: 'Upgrade your membership to add more clients.'
    // })
    Swal.fire({
      icon: 'warning',
      title: 'Client Limit Reached!',
      text: 'Upgrade your membership to add more clients.',
      showCancelButton: true,
      confirmButtonText: 'Upgrade',
      cancelButtonText: 'Later',
      buttonsStyling: true
    }).then((result) => {
      if (result.isConfirmed) {
        // Redirect to the upgrade page
        window.location.href = '{{ route('membership') }}'; // Replace with your actual upgrade URL
      }
    });
  </script>

@endif
@include('partials.application_duplicate_confirm_script')
@include('web.partials.application_visa_detail_fields_script')
@endsection()
