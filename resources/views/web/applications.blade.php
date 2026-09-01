@extends('web.layout.main')

@section('main-section')

@php

use App\Models\UserRoles;
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
@php
    $statusFlow = ['Client Registered', 'Client Counselled', 'Preparation', 'Appointment Booked', 'Applied', 'Decision', 'Appeal Lodged', 'Appeal Decision', 'AR / JR Lodged', 'AR / JR Decision', 'Withdrawn', 'Cancelled'];
@endphp




        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                @include('partials.application_module_header', [
                    'activeTab' => 'applications',
                    'application_roles' => $application_roles,
                    'user' => $user,
                    'clients' => $clients,
                    'applications' => $applications,
                ])
                {{-- <div class="col-12 d-flex justify-content-between mb-2 ">
                        <h3 class="text-primary">Applications</h3>

                </div> --}}

                @include('partials.table_filter_toolbar', [
                    'filterItems' => $applicationTypeFilters ?? [],
                    'tableId' => 'clientTable',
                    'toolbarTitle' => 'Application Type',
                    'totalCount' => count($applications),
                ])
                <div class="table-wrapper">
                    <table class="table table-hover table-bordered fl-table" id="clientTable">
                        <thead>
                        <tr >
                            <th class="p-1 text-center">Sr No.</th>
                            <th class="p-1 text-center">Client(ID)</th>
                            <th class="p-1 text-center">Application (ID)</th>
                            <th class="p-1 text-center">Visa Country</th>
                            <th class="p-1 text-center">Home Country</th>
                            <th class="p-1 text-center">Status</th>
                            <th class="p-1 text-center">Start Date</th>
                            <th class="p-1 text-center">End Date</th>
                            <th class="p-1 text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($applications as $key=>$app)
                        @php
                            $applicationType = trim((string) ($app->application_name ?? '')) ?: 'Unspecified';
                            $applicationFilterKey = \App\Services\TableFilterCountService::keyFor($applicationType);
                        @endphp
                        <tr data-filter-value="{{ $applicationFilterKey }}">
                            <td class="p-1 text-center">{{ $key+1}}</td>
                            <td class="p-1 text-center">{{ $app->client ? $app->client->name .'('.$app->client_id.')' :  '' }}</td>
                            <td class="p-1 text-center">{{  $app->application_name  .'('.$app->application_id.')'}}</td>
                            <td class="p-1 text-center">{{ $app->visa_country ?: ($app->client->visa_country ?? '') }}</td>
                            <td class="p-1 text-center">{{ $app->application_country }}</td>
                            @php
                                $currentStatus = $app->application_status ?: 'Client Registered';
                                $currentIndex = array_search($currentStatus, $statusFlow, true);
                                $currentIndex = $currentIndex === false ? 0 : $currentIndex;
                                $isTerminalStatus = in_array($currentStatus, ['Withdrawn', 'Cancelled'], true);
                            @endphp
                            <td class="p-1 text-center">
                                <select class="form-control form-select application-status-select"
                                        data-application-id="{{ $app->id }}"
                                        @if(!($application_roles->update_only == 1 || $application_roles->read_write_only == 1)) disabled @endif>
                                    @foreach($statusFlow as $statusOption)
                                        @php $optionIndex = array_search($statusOption, $statusFlow, true); @endphp
                                        <option value="{{ $statusOption }}"
                                            @if($currentStatus === $statusOption) selected @endif
                                            @if($optionIndex < $currentIndex || ($isTerminalStatus && $statusOption !== $currentStatus)) disabled @endif>
                                            {{ $statusOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-1 text-center">{{ $app->formatted_start_date }}</td>
                            <td class="p-1 text-center">@if($app->end_date != null){{ $app->formatted_end_date }}@endif</td>
                            <td class="p-1 text-center action-icon">
                                <a style="background:transparent;border:none;" class="p-0 m-0 text-dark" @if($application_roles->read_only == 1 or $application_roles->read_write_only == 1) href="{{ route('view_application', $app->id)}}" @else href="#" @endif><i class="fa-solid fa-eye btn text-info p-1 m-0"></i></a>
                                <a style="background:transparent;border:none;" class=" p-0 m-0 text-dark" @if($application_roles->update_only == 1) href="{{ route('update_application', $app->id)}}" @else href="#" @endif><i class="fa-solid fa-edit btn text-primary p-1 m-0"></i></a>
                                <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" @if($application_roles->delete_only == 1) onclick="deleteapplication({{ $app->id }})" @endif></i>
                            </td>
                            {{-- <td>
                                <a class="p-1 text-dark" href=""><i class="fa-solid fa-eye"></i></a>
                                <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" onclick="deleteapplication({{ $app->id }})"></i>
                            </td> --}}
                        </tr>
                        @endforeach

                        <tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
  </script>
  <script>
    function deleteapplication(id){
        var conf = confirm('Are you sure you want to delete this application?');
        if(conf == true){
            window.location.href = "delete_application/"+id+"";
        }
    }
  </script>
  <script>
      $(document).ready(() => {
        $('.application-status-select').on('change', function () {
            const selectEl = $(this);
            const applicationId = selectEl.data('application-id');
            const selectedStatus = selectEl.val();

            $.ajax({
                url: "{{ route('applications.update_status') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    application_id: applicationId,
                    status: selectedStatus
                },
                success: function(response) {
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message || 'Status updated successfully.' })
                        .then(() => window.location.reload());
                },
                error: function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to update status.';
                    Swal.fire({ icon: 'error', title: 'Oops!', text: message });
                    window.location.reload();
                }
            });
        });

        $("#add_new_zero").click(function(){
            Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: 'No applications have been created yet.'
            });
        });

        $("#new_assign_zero").click(function(){
            Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: 'No applications have been created yet.'
            });
        });

        $("#app_tracking_zero").click(function(){
            Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: 'No applications have been created yet.'
            });
        });
      })
  </script>

@if(session()->has('deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Application deleted successfully.'
    })
  </script>

@endif
@if(session()->has('application_added'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: @json(session('application_added'))
    })
  </script>

@endif
@if(session()->has('application_updated'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Application updated successfully.'
    })
  </script>

@endif
@endsection()
