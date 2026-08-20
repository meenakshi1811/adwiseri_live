@extends('admin.layout.main')

@php
    $staffUserOptions = $staffUserOptions ?? \App\Models\User::where('user_type', 'User')->orderBy('name')->get(['id', 'name', 'email', 'added_by']);
@endphp

@section('main-section')

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <ul class="nav nav-tabs module-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-list-tab" data-bs-toggle="tab" data-bs-target="#users-list" type="button" role="tab">All Users (Staff)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="user-activity-tab" data-bs-toggle="tab" data-bs-target="#user-activity" type="button" role="tab">Activity Log</button>
                    </li>
                </ul>

                <div class="tab-content">
                <div class="tab-pane fade show active" id="users-list" role="tabpanel">
                <div class="client-btn d-flex mb-2 ">
                    {{-- <form class="form-inline d-flex justify-content-between w-100">
                        <h3 class="text-primary">Users (Staff)</h3> --}}
                        <h3 class="text-primary text-center flex-grow-1 text-center m-0">Users (Staff)</h3>
                        <p class="mt-1">

                          {{-- <a href="{{ route('users_export') }}" class="m-0">Export</a> --}}
                          <a href="{{ route('new_user') }}" class="m-0">Add New</a>
                        </p>
                        {{-- <div class="d-flex ">
                            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                        </div> --}}
                      {{-- </form> --}}
                      {{-- <i class="fa-solid fa-magnifying-glass"></i> --}}
                </div>
                <div class="table-wrapper">
                    <table class="table table-hover table-bordered fl-table" id="userTable">
                        <thead>
                        <tr>
                        <tr>
                            <th class="text-center">Sr No.</th>
                            <th class="text-center">Sub_ID</th>
                            {{-- <th>UserID</th> --}}
                            <th class="text-center">Name</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Phone</th>
                            <th class="text-center">Country</th>
                            <th class="text-center">City</th>
                            <th class="text-center">Designation</th>
                            <th class="text-center">Created Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($siteusers as $key => $siteuser)
                        <tr>
                        <td class="text-center">{{ $key+1 }}</td>
                            <td class="text-center">{{ $siteuser->added_by }}</td>
                            {{-- <td>{{ $siteuser->id }}</td> --}}
                            <td class="text-center"  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $siteuser->name }}" style="position: relative;">@if(strlen($siteuser->name) > 22){{ substr($siteuser->name, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{$siteuser->name}} ({{ $siteuser->id }})</span> @else {{$siteuser->name}} ({{ $siteuser->id }}) @endif</td>
                            <td class="text-center"  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $siteuser->email }}" style="position: relative;">@if(strlen($siteuser->email) > 22){{ substr($siteuser->email, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{$siteuser->email}}</span> @else {{$siteuser->email}} @endif</td>
                            <td class="text-center">@include('partials.phone_display', ['phone' => $siteuser->phone])</td>
                            <td class="text-center">{{ $siteuser->country }}</td>
                            <td class="text-center">{{ $siteuser->city }}</td>
                            <td class="text-center"  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $siteuser->designation }}" style="position: relative;">@if(strlen($siteuser->designation) > 22){{ substr($siteuser->designation, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{$siteuser->designation}}</span> @else {{$siteuser->designation}} @endif</td>
                            <td class="text-center">{{  \Carbon\Carbon::parse($siteuser->created_at)->format('d-m-Y H:i:s') }}</td>
                            <!-- <td class="text-center">@if($siteuser->status == 'true') <a style="background:green;border-color:green;" href="{{ route('subscriber_status', $siteuser->id) }}" class="p-0 px-1">Active</a> @else <a style="background:red;border-color:red;" href="{{ route('subscriber_status', $siteuser->id) }}" class="p-0 px-1">Inactive</a> @endif</td> -->
                            @php
                                $isActive = false;

                                // check expiry date
                                if(!empty($siteuser->membership_expiry_date)) {
                                    $isActive = \Carbon\Carbon::parse($siteuser->membership_expiry_date)->isFuture() 
                                                || \Carbon\Carbon::parse($siteuser->membership_expiry_date)->isToday();
                                }
                            @endphp

                            <td class="text-center">
                                @if($isActive)
                                    <a style="background:green;border-color:green;" 
                                      href="{{ route('subscriber_status', $siteuser->id) }}" 
                                      class="p-0 px-1">Active</a>
                                @else
                                    <a style="background:red;border-color:red;" 
                                      href="{{ route('subscriber_status', $siteuser->id) }}" 
                                      class="p-0 px-1">Inactive</a>
                                @endif
                            </td>

                            <td class="text-center">
                                <a style="background:transparent;border:none;" class="p-0 m-0 text-dark" href="{{ route('view_user', $siteuser->id)}}"><i class="fa-solid fa-eye btn text-info p-1 m-0"></i></a>
                                <i class="fa-solid fa-edit btn text-primary p-1 m-0" style="font-size:14px;" onclick="updateuser({{ $siteuser->id }})"></i>
                                <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" onclick="deleteuser({{ $siteuser->id }})"></i>
                            </td>
                        </tr>
                        @endforeach

                        <tbody>
                    </table>
                </div>
                </div>{{-- end users-list tab --}}

                <div class="tab-pane fade" id="user-activity" role="tabpanel">
                    <div class="client-btn d-flex mb-2">
                        <h3 class="text-primary text-center flex-grow-1 text-center m-0">Users Activity Log</h3>
                    </div>
                    @include('admin.partials.journey_log_panel', [
                        'panelId' => 'userActivityPanel',
                        'entityFilterId' => 'userActivityEntity',
                        'durationFilterId' => 'userActivityDuration',
                        'tableId' => 'userActivityTable',
                        'chartId' => 'userActivityChart',
                        'dataUrl' => route('admin_user_activity_log_data'),
                        'entityParam' => 'user_id',
                        'entityLabel' => 'Select User (Staff)',
                        'entities' => $staffUserOptions,
                        'panelTitle' => 'Users Activity Log',
                    ])
                </div>
                </div>{{-- end tab-content --}}
            </div>
        </div>
    </div>

  </div>
  <script>
      document.addEventListener("DOMContentLoaded", function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    var activityTab = document.getElementById('user-activity-tab');
    if (activityTab) {
        activityTab.addEventListener('shown.bs.tab', function () {
            if (typeof inituserActivityPanel === 'function') {
                inituserActivityPanel();
            }
        });
    }
});
      function deleteuser(id){
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
            window.location.href = "delete_user/"+id+"";
          }
        })
      }
      function updateuser(id){
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
            window.location.href = "siteuser_update/"+id+"";
          }
        })
      }
  </script>

  @if(session()->has('deleted'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'User deleted successfully.'
      })
    </script>

  @endif
  @if(session()->has('status_updated'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'User status changed successfully.'
      })
    </script>

  @endif
  @if(session()->has('user_updated'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'User updated successfully.'
      })
    </script>

  @endif
  @if(session()->has('user_added'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'User added successfully.'
      })
    </script>

  @endif

@endsection()
