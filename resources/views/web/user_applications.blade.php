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

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                @include('partials.application_module_header', [
                    'activeTab' => 'management',
                    'application_roles' => $application_roles,
                    'user' => $user,
                    'clients' => $clients,
                    'applications' => $applications,
                    'siteusers' => $siteusers,
                    'unassignedApplicationsCount' => $unassignedApplicationsCount ?? 0,
                ])

                <div style="display: none;" id="new_assignment" class="col">
                  <h5 class="text-primary text-center fw-bold" style="font-weight:700; margin: 2.5em 0;">New Application Assignment</h5>
                    <form id="registration_form" class="register-box login-box" method="POST" action="{{ route('user_app_assignment') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="local_time" class="localtime" />
                        <div class="row">
                            <div class="col-md-4 p-1">
                                <label>Client<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="client_id" id="client_id" required class="form-control form-select @error('client_id') is-invalid @enderror" aria-describedby="emailHelp">
                                    <option value="">Select Client</option>
                                    @foreach($clients as $clint)
                                    <option value="{{ $clint->id }}">{{ $clint->name."(".$clint->id.")" }}</option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="application_id" id="application_id" class="form-control form-select @error('application_id') is-invalid @enderror" aria-describedby="emailHelp" required>
                                    <option value="">Select Application</option>
                                </select>
                                @error('application_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>User/Advisor<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="user_id" id="user_id" class="form-control form-select @error('user_id') is-invalid @enderror" aria-describedby="emailHelp" required>
                                    <option value="">Select User/Advisor</option>
                                    @foreach($siteusers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}({{ $u->id }})</option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1"></div>
                            <div class="col-md-8 p-1">
                                <button type="submit" class="form-control btn btn-primary" style="width: fit-content;">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                @if(($assignments ?? collect())->count() > 0)
                @include('partials.table_filter_toolbar', [
                    'filterItems' => $userApplicationFilters ?? [],
                    'tableId' => 'clientTable',
                    'toolbarTitle' => 'Users (Staff) By Applications (Assigned)',
                    'totalCount' => ($assignments ?? collect())->count(),
                ])
                @endif
                <div id="assignments" class="table-wrapper">
                    <table class="table table-hover table-bordered fl-table" id="clientTable">
                        <thead>
                        <tr>
                          <th class="text-center">Sr No.</th>
                            <th class="text-center">Client(ID)</th>
                            <th class="text-center">Application (ID)</th>
                            <th class="text-center">Assigned To</th>
                            <th class="text-center">Assigned On</th>
                            <th class="text-center">Updated On</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($assignments as $key=>$assign)
                        @php
                            $assignmentFilterKey = \App\Services\TableFilterCountService::keyFor(
                                optional($assign->user)->name ?: ($assign->user_name ?? ('User #' . $assign->user_id))
                            );
                        @endphp
                        <tr data-filter-value="{{ $assignmentFilterKey }}">
                        <td class="text-center">{{ $key+1 }} </td>
                            <td class="text-center">{{ $assign->client ?  $assign->client->name .'('. $assign->client_id.')' : '' }}</td>
                            <td class="text-center">{{ $assign->application_id ?  $assign->application->application_name .'('. $assign->application_id.')' : '' }}</td>
                            <td class="text-center">{{ $assign->user_name  .'('. $assign->user_id.')'}}</td>
                            <td class="text-center">{{ date("d-m-Y H:i:s",strtotime($assign->created_at)) }}</td>
                            <td class="text-center">
                                @if($assign->updated_at && $assign->created_at && strtotime($assign->updated_at) > strtotime($assign->created_at))
                                    {{ date('d-m-Y H:i:s', strtotime($assign->updated_at)) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- <a style="background:transparent;border:none;" class="p-0 m-0 text-dark" href="{{ route('application_view', $doc->id)}}"><i class="fa-solid fa-eye btn text-info p-1 m-0"></i></a> --}}
                                <i class="fa-solid fa-edit btn text-primary p-1 m-0" style="font-size:14px;" @if($application_roles->update_only ==1) onclick="updateassignmentt({{ $assign->id }})" @endif></i>
                                <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" @if($application_roles->delete_only ==1) onclick="deleteassignmentt({{ $assign->id }})" @endif></i>
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
                {{-- <div class="table-btn">
                    <button>Previous</button>
                    <button>1</button>
                    <button>Next</button>
                </div> --}}
            </div>
        </div>
    </div>

  </div>
  <script>
    function deleteassignmentt(id){
      Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#695EEE',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "delete_app_assignment/"+id+"";
        }
      })
    }
      function updateassignmentt(id){
        Swal.fire({
          title: 'Are you sure?',
          text: "Do you want to update this record?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#695EEE',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, continue'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "update_application_assignment/"+id+"";
          }
        })
      }
  </script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
  </script>
  <script>
      $(document).ready(() => {

        $("#new_assign_zero").click(function(){
            Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: @json(count($applications) > 0 ? 'No open (unassigned) applications are available to assign.' : 'No applications have been created yet.')
            });
        });

        $("#app_tracking_zero").click(function(){
            Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: 'No applications have been created yet.'
            });
        });
        $("#new_assign_usr").click(function(){
            Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: 'No Advisors/Counsellors have been created yet for this consultancy.'
            });
        });

        $("#new_assign").click(function(){
            $("#new_assign").css('display','none');
            $("#back").css('display','block');
            $("#new_assignment").css('display','block');
            $("#assignments").css('display','none');
        });
        $("#back").click(function(){
            $("#new_assign").css('display','block');
            $("#back").css('display','none');
            $("#new_assignment").css('display','none');
            $("#assignments").css('display','block');
        });
          $("#client_id").change(function(){
            var id = $(this).val();
            $("#application_id").html('<option value="">Select Application</option>');
            if (!id) {
                return;
            }
            $.ajax({
                url: "{{ route('get_applications') }}",
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    id: id,
                },
                cache:false,
                success: function(data){
                    $("#application_id").html(data);
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
        text: 'Application Assignment deleted successfully.'
      })
    </script>

  @endif
  @if(session()->has('assignment_added'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Application assigned successfully.'
      })
    </script>

  @endif
  @if(session()->has('assignment_updated'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Application assignment updated.'
      })
    </script>

  @endif

@endsection()
