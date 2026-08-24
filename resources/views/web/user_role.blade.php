@extends('web.layout.main')

@section('main-section')
@php use App\Support\ModuleAvailability; @endphp

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                    <h3 class="text-primary text-center flex-grow-1 text-center m-0">User Access Rights</h3>
                    <p>
                        @if(ModuleAvailability::hasStaffUsers($user))
                        <a href="{{ route('add_user_role') }}">Edit UAR (User Access Rights)</a>
                        @else
                        <span class="text-muted">Edit UAR (User Access Rights)</span>
                        @endif
                    </p>
                </div>

              <div class="row m-0 pb-2">
                <div class="col-6 border p-1 text-center top_modules" onclick="window.location.href = '{{ route('users') }}';">
                  Users
                </div>
                <div class="col-6 border p-1 text-center bg-info text-white">
                  User Access Rights
                </div>
              </div>
                @if(count($roles) != 0)
                <div class="table-wrapper">
                    <table class="fl-table table table-hover p-0 m-0" id="clientTable">
                        <thead>
                        <tr>
                           <th class="p-1 text-center">Sr No.</th>
                            <th class="p-1 text-center">User Name</th>
                            <th class="p-1 text-center">Designation</th>
                            <th class="p-1 text-center">Email</th>
                            <th class="p-1 text-center">Access Right</th>
                            <th class="p-1 text-center">Updated Date</th>
                            <th class="p-1 text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($roles as  $key=>$role)
                        <tr>
                        <td class="p-1 text-center">{{$key+1}}</td>
                            <td class="p-1 text-center"  style="position: relative;">{{$role['name']}}</td>
                            <td class="p-1 text-center"  style="position: relative;">{{ $role['designation'] ?? '—' }}</td>
                            <td class="p-1 text-center"  style="position: relative;">{{$role['email']}}</td>
                            <td class="p-1 text-center"  style="position: relative;">{{ $role['access_right'] ?? '—' }}</td>
                            <td class="p-1 text-center"  style="position: relative;">
                                @if(!empty($role['updated_at']))
                                    {{ \Carbon\Carbon::parse($role['updated_at'])->format('d-m-Y H:i:s') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td  class="p-1 text-center action-icon">
                                <a href="{{ route('add_user_role', $role['user_id']) }}" style="text-decoration:none;"><i class="fa-solid fa-edit btn p-1 text-info" style="font-size:14px;"></i></a>
                            </td>
                        </tr>
                        @endforeach

                        <tbody>
                    </table>
                </div>
                @else
                <p class="text-secondary px-3 text-center">Access Rights Not Added...</p>
                @endif
            </div>
        </div>

    </div>

</div>
<script>
  function deleteuser(id){
      var localtime = new Date();
      var conf = confirm('Are you sure you want to delete this user?');
      if(conf == true){
          window.location.href = "delete_role/"+id+"/"+localtime.toString()+"";
      }
  }
    function userstatus(id){
        var localtime = new Date();
        var conf = confirm('Are you sure you want to change this user''s status?');
        if(conf == true){
            window.location.href = "subscriber_status/"+id+"/"+localtime.toString()+"";
        }
    }
</script>
@if(session()->has('all_access'))
  <script>
    Swal.fire({
      icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
      title: 'Oops!',
      text: 'All users have been assigned their access rights.'
    })
  </script>

@endif
@if(session()->has('no_user'))
  <script>
    Swal.fire({
      icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
      title: 'Oops!',
      text: 'No users have been created yet.'
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
        // Redirect to the upgrade page
        window.location.href = '{{ route('membership') }}'; // Replace with your actual upgrade URL
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
