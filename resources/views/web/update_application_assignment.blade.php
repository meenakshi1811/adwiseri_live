@extends('web.layout.main')

@section('main-section')

        <div class="col-lg-10 column-client">
            
            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 ">
                    <h3 class="text-primary text-center flex-grow-1 text-center m-0">Update Application Assignment</h3>
                </div>
                <div class="col">
                    <form id="registration_form" class="register-box login-box" method="POST" action="{{ route('user_app_assignment') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="local_time" class="localtime" />
                        <input type="hidden" value="{{ $assignment->id }}" name="id" />
                        <div class="row">
                            <div class="col-md-4 p-1">
                                <label>Client (ID)<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                @php
                                    $clientLabel = ($client->name ?? '') . '(' . ($client->id ?? $assignment->client_id) . ')';
                                    $selectedApp = collect($applications)->firstWhere('application_id', $assignment->application_id);
                                    $applicationLabel = $selectedApp
                                        ? trim(($selectedApp->application_name ?? '') . ' (' . $selectedApp->application_id . ')')
                                        : (string) $assignment->application_id;
                                @endphp
                                <input type="text" class="form-control" value="{{ $clientLabel }}" readonly>
                                <input type="hidden" name="client_id" id="client_id" value="{{ $assignment->client_id }}">
                                @error('client_id')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application (ID)<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input type="text" class="form-control" value="{{ $applicationLabel }}" readonly>
                                <input type="hidden" name="application_id" id="application_id" value="{{ $assignment->application_id }}">
                                @error('application_id')
                                    <span class="invalid-feedback d-block" role="alert">
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
                                    @foreach($advisors as $u)
                                    <option {{ ($u->id == $assignment->user_id) ? 'selected' : ''}} value="{{ $u->id }}">{{ $u->name }} ({{ $u->designation }})</option>
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
            window.location.href = "app_assignment_update/"+id+"";
          }
        })
      }
  </script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
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
