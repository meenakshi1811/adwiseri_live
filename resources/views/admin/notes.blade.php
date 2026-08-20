@extends('admin.layout.main')

@section('main-section')
  
        <div class="col-lg-10 column-client">
            <div class="row m-0 pb-2">
                <div class="col-4 border p-1 text-center top_modules" style="cursor: pointer;" onclick="window.location.href = '{{ route('communication') }}';">
                  Communication
                </div>
                <div class="col-4 border p-1 text-center bg-info text-white top_modules" style="cursor: pointer;" onclick="window.location.href = '{{ route('meetings') }}';">
                  Meeting notes (Clients)
                </div>
                <div class="col-4 border p-1 text-center top_modules">
                  Messaging
                </div>
              </div>
              <div class="col mb-3 d-flex justify-content-between">
                <h3 class="text-primary px-2">Meeting Notes (Clients)</h3>
                {{-- @if(count($clients) > 0)
                <button type="button" id="add_new" class="btn btn-info text-white">Add New</button>
                @else
                <button type="button" id="add_new_zero" class="btn btn-info text-white">Add New</button>
                @endif --}}
                <button type="button" id="back" class="btn btn-info text-white" style="display: none;">Back</button>
              </div>
            {{-- <div style="display: none;" id="new_discussion" class="col">
                <form id="registration_form" class="register-box login-box" method="POST" action="{{ route('post_client_discussion') }}">
                    @csrf
                    <input type="hidden" name="local_time" class="localtime" />
                    <div class="row">
                        <div class="col-md-4 p-1">
                            <label>Client<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="client" id="client" class="form-control form-select @error('client') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                <option value="">Select Client</option>
                                @foreach($clients as $client)
                                <option {{ (old('client') == $client->id) ? 'selected' : '' }} value="{{ $client->id }}">{{ $client->name }}({{$client->id}})</option>
                                @endforeach
                            </select>
                            @error('client')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4 p-1">
                            <label>Application<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="application" id="application" class="form-control form-select @error('client') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                <option value="">Select Application</option>
                            </select>
                            @error('application')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4 p-1">
                            <label>Communication Type<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="communication_type" class="form-control form-select @error('client') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                <option value="">Select Communication Type</option>
                                <option value="Call">Call</option>
                                <option value="Email">Email</option>
                                <option value="E-meet">E-meet</option>
                                <option value="Office Visit">Office Visit</option>
                            </select>
                            @error('communication_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4 p-1">
                            <label>Communication Date<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input type="datetime-local" name="communication_date" class="form-control" required />
                            @error('communication_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4 p-1">
                            <label>Details<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <textarea name="discussion" class="form-control" rows="3" minlength="5" maxlength="25000" placeholder="Details" required>{{old('discussion')}}</textarea>
                            @error('discussion')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col text-start p-1 adwiseri-form-actions">
                            <button type="submit" class="form-control btn btn-primary" style="width: fit-content;">Submit</button>
                        </div>
                    </div>
                </form>
            </div> --}}
            <div id="discussions" class="col">
                <div class="table-wrapper">
                    <table class="table table-hover table-bordered fl-table" id="clientTable">
                        <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>User</th>
                            <th>Client Name</th>
                            <th>Application (ID)</th>
                            <th>Mode</th>
                            <th>Date</th>
                            <th>Discussion</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($notes as $key => $discus)
                        @php
                            $userDisplay = ($discus->user && $discus->user->user_type === 'Subscriber') ? 'SUB' : $discus->user_name;
                            $appName = trim((string) ($discus->application->application_name ?? ''));
                            $appId = (string) ($discus->application_id ?? '');
                            $applicationDisplay = $appName !== '' ? $appName . ' (' . $appId . ')' : $appId;
                            $meetingNotePayload = [
                                'user' => $userDisplay,
                                'client' => $discus->client_name,
                                'application' => $applicationDisplay,
                                'mode' => $discus->communication_type,
                                'date' => date('d-m-Y H:i:s', strtotime($discus->communication_date)),
                                'discussion' => $discus->discussion,
                            ];
                        @endphp
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $userDisplay }}</td>
                            <td>{{ $discus->client_name }}</td>
                            <td>{{ $applicationDisplay }}</td>
                            <td>{{ $discus->communication_type }}</td>
                            <td>{{ date("d-m-Y H:i:s",strtotime($discus->communication_date)) }}</td>
                            <td><div style="max-height: 100px;overflow:auto;">{{ $discus->discussion }}</div></td>
                            <td class="text-center action-icon">
                                <button type="button" class="btn p-0 border-0 bg-transparent" title="View"
                                    onclick="viewMeetingNote(@json($meetingNotePayload))">
                                    <i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i>
                                </button>
                            </td>
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
  <script>
      function viewMeetingNote(note) {
          const discussion = (note.discussion || '').replace(/\n/g, '<br>');
          Swal.fire({
              title: 'Meeting Note Details',
              html: `
                  <div class="text-start">
                      <p><strong>User:</strong> ${note.user || ''}</p>
                      <p><strong>Client:</strong> ${note.client || ''}</p>
                      <p><strong>Application:</strong> ${note.application || ''}</p>
                      <p><strong>Mode:</strong> ${note.mode || ''}</p>
                      <p><strong>Date:</strong> ${note.date || ''}</p>
                      <p><strong>Discussion:</strong></p>
                      <div style="max-height:300px;overflow:auto;text-align:left;white-space:normal;">${discussion}</div>
                  </div>
              `,
              width: '640px',
              confirmButtonText: 'Close'
          });
      }

      $(document).ready(() => {

        $("#add_new_zero").click(function(){
            Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: 'No clients have been created yet.'
            });
        });

        $("#add_new").click(function(){
            $("#add_new").css('display','none');
            $("#back").css('display','block');
            $("#new_discussion").css('display','block');
            $("#discussions").css('display','none');
        });
        $("#back").click(function(){
            history.back();
            // $("#add_new").css('display','block');
            // $("#back").css('display','none');
            // $("#new_discussion").css('display','none');
            // $("#discussions").css('display','block');
        });
          
          $("#client").change(function(){
            var id = $(this).val();
            $("#application").html('<option value="">Select Application</option>');
            if (!id) {
                return;
            }
            $.ajax({
                url: "{{ route('get_application') }}",
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    id: id,
                    comm: "communication",
                },
                cache:false,
                success: function(data){
                    $("#application").html(data);
                }
            });
          });
      });
  </script>
@if(session()->has('success'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Note added successfully.'
    })
  </script>

@endif

@endsection()
