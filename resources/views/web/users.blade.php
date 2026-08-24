@extends('web.layout.main')

@section('main-section')
@php use App\Support\ModuleAvailability; @endphp

        <div class="col-lg-10 column-client">
            <div class="client-dashboard users-module">
                <div class="col-12 client-btn d-flex justify-content-between align-items-center mb-3">
                    <h3 class="text-primary text-center flex-grow-1 text-center m-0">Users (Staff)</h3>
                    <div class="d-flex gap-2 mb-0 module-header-actions">
                        <a href="{{ route('add_user') }}">Add User</a>
                        @if(count($siteusers) != 0)
                        <a href="{{ route('export_users') }}">Export</a>
                        @endif
                    </div>
                </div>

              <div class="row m-0 pb-2 users-module-tabs module-tab-row">
                <div class="col-6 border p-1 text-center tab-anchor bg-info text-white">
                  Users
                </div>
                <div class="col-6 border p-1 text-center tab-anchor top_modules {{ ModuleAvailability::hasStaffUsers($user) ? '' : '' }}"
                  @if(ModuleAvailability::hasStaffUsers($user))
                    onclick="window.location.href = '{{ route('user_role') }}';"
                  @else
                    id="uar_zero" style="cursor:pointer;opacity:0.45;"
                  @endif>
                  User Access Rights
                </div>
              </div>

                <div id="users-tab-panel">
                @if(count($siteusers) != 0)
                <div class="table-wrapper">
                    <table class="fl-table table table-hover p-0 m-0" id="clientTable">
                        <thead>
                        <tr>
                            <th class="text-center">Sr No.</th>
                            <th class="text-center">User Name</th>
                            <th class="text-center">City</th>
                            <th class="text-center">Phone No</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Designation</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Created Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($siteusers as $key=>$siteuser)
                        <tr>
                            <td class="p-1 text-center">{{ $key+1 }}</td>
                            <td class="p-1 text-center"  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $siteuser->name }}"  style="position: relative;">@if(strlen($siteuser->name) > 22){{ substr($siteuser->name, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{$siteuser->name}}</span> @else {{$siteuser->name}} @endif</td>
                            <td class="p-1 text-center">{{ $siteuser->city }}</td>
                            <td class="p-1 text-center">@include('partials.phone_display', ['phone' => $siteuser->phone])</td>
                            <td class="p-1 text-center"  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $siteuser->email }}"  style="position: relative;">@if(strlen($siteuser->email) > 22){{ substr($siteuser->email, 0, 22) }}... <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{$siteuser->email}}</span> @else {{$siteuser->email}} @endif</td>
                            <td class="p-1 text-center">{{ $siteuser->designation }}</td>
                            <td class="p-1 text-center">@if($siteuser->status == 'true') <a style="background:green;border-color:green;" href="#" onclick="userstatus({{ $siteuser->id }})" class="p-0 px-1">Active</a> @else <a style="background:red;border-color:red;" href="#" onclick="userstatus({{ $siteuser->id }})" class="p-0 px-1">Inactive</a> @endif</td>
                            <td class="p-1 text-center">{{ \Carbon\Carbon::parse($siteuser->created_at)->format('d-m-Y H:i:s') }}</td>
                            <td class="p-1 text-center action-icon">
                                <a href="{{ route('siteuser_profile', $siteuser->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></a>
                                <a href="{{ route('siteuser_profile', ['id' => $siteuser->id, 'edit' => 1]) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fa-solid fa-pen-to-square btn p-1 text-primary" style="font-size:14px;"></i></a>
                                <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteuser({{ $siteuser->id }})"></i>
                            </td>
                        </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-secondary px-3">Users Not Added...</p>
                @endif
                </div>
            </div>
        </div>

    </div>

</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    var uarZero = document.getElementById('uar_zero');
    if (uarZero) {
        uarZero.addEventListener('click', function () {
            if (window.AdwiseriAlert && typeof window.AdwiseriAlert.oops === 'function') {
                window.AdwiseriAlert.oops('No users have been created yet. Please add a user before managing access rights.');
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Oops!',
                    text: 'No users have been created yet. Please add a user before managing access rights.'
                });
            }
        });
    }
});
  function deleteuser(id){
      var localtime = new Date();
      var conf = confirm('Are you sure you want to delete this user?');
      if(conf == true){
          window.location.href = "delete_siteuser/"+id+"/"+localtime.toString()+"";
      }
  }
    function userstatus(id){
        var localtime = new Date();
        var conf = confirm('Are you sure you want to change this user's status?');
        if(conf == true){
            window.location.href = "subscriber_status/"+id+"/"+localtime.toString()+"";
        }
    }
</script>
@if(session()->has('user_added'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'User added successfully.'
    })
  </script>

@endif
@if(session()->has('deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'User deleted successfully.'
    })
  </script>

@endif
@if(session()->has('user_limit'))
  <script>
    Swal.fire({
      icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
      title: 'User Limit Reached!',
      text: 'Upgrade your membership to add more users.',
      showCancelButton: true,
      confirmButtonText: 'Upgrade',
      cancelButtonText: 'Later',
      buttonsStyling: true
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = '{{ route('membership') }}';
      }
    });
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
@endsection()
