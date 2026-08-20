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
            <div class="client-btn d-flex justify-content-between align-items-center mb-3 ">
                <h3 class="text-primary text-center flex-grow-1 text-center m-0">Meeting Notes (Clients)</h3>
                <div class="module-header-actions">
                @if(count($clients) > 0)
                <button type="button" @if($communication_roles->write_only == 1 or $communication_roles->read_write_only == 1) id="add_new" @endif class="btn btn-info text-white">Add New</button>
                @else
                <button type="button" @if($communication_roles->write_only == 1 or $communication_roles->read_write_only == 1) id="add_new_zero" @endif class="btn btn-info text-white">Add New</button>
                @endif
                <button type="button" id="back" class="btn btn-info text-white" style="display: none;">Back</button>
                </div>
            </div>
            @include('partials.communication_tabs', ['activeTab' => 'meeting_notes'])

            <div style="display: none;" id="new_discussion" class="col">
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
                            <input type="datetime-local" id="comm_date" max="{{ now()->format('Y-m-d\TH:i') }}" value="{{ now()->format('Y-m-d\TH:i') }}" onfocus="set_max()" name="communication_date" class="form-control" autocomplete="off" required />
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
                        <div class="col-md-4 p-1">
                         </div>
                        <div class="col-md-8 text-left p-1">
                            <button type="submit" class="form-control btn btn-primary" style="width: fit-content;">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="discussions" class="col">
                @include('partials.table_filter_toolbar', [
                    'filterItems' => $meetingModeFilters ?? [],
                    'tableId' => 'clientTable',
                    'toolbarTitle' => 'Meeting Notes Mode',
                    'totalCount' => count($discussions),
                ])
                <div class="table-wrapper">
                    <table class="table table-hover table-bordered fl-table" id="clientTable">
                        <thead>
                        <tr>
                            <th class="text-center">Sr. No</th>
                            <th class="text-center">User</th>
                            <th class="text-center">Client Name</th>
                            <th class="text-center">Application (ID)</th>
                            <th class="text-center">Mode</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Discussion</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($discussions as $key => $discus)
                        @php
                            $userDisplay = ($discus->user && $discus->user->user_type === 'Subscriber') ? 'SUB' : $discus->user_name;
                            $appName = trim((string) ($discus->application->application_name ?? ''));
                            $appId = (string) ($discus->application_id ?? '');
                            $applicationDisplay = $appName !== '' ? $appName . ' (' . $appId . ')' : $appId;
                            $meetingMode = trim((string) ($discus->communication_type ?? '')) ?: 'Unspecified';
                            $meetingFilterKey = \App\Services\TableFilterCountService::keyFor($meetingMode);
                            $meetingNotePayload = [
                                'user' => $userDisplay,
                                'client' => $discus->client_name,
                                'application' => $applicationDisplay,
                                'mode' => $discus->communication_type,
                                'date' => date('d-m-Y H:i:s', strtotime($discus->communication_date)),
                                'discussion' => $discus->discussion,
                            ];
                        @endphp
                        <tr data-filter-value="{{ $meetingFilterKey }}">
                            <td class="text-center">{{ $key+1 }}</td>
                            <td class="text-center">{{ $userDisplay }}</td>
                            <td class="text-center">{{ $discus->client_name }}</td>
                            <td class="text-center">{{ $applicationDisplay }}</td>
                            <td class="text-center">{{ $discus->communication_type }}</td>
                            <td class="text-center">{{ date("d-m-Y H:i:s",strtotime($discus->communication_date)) }}</td>
                            <td class="text-center"><div style="max-height: 100px;overflow:auto;">{{ $discus->discussion }}</div></td>
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

    function set_max(){
        var d = new Date();
        var y = d.getFullYear();
        var m = d.getMonth() + 1;
        if(m < 10){
            m = "0"+m;
        }
        var dd = d.getDate();
        if(dd < 10){
            dd = "0"+dd;
        }
        var hh = d.getHours();
        if(hh < 10){
            hh = "0"+hh;
        }
        var mm = d.getMinutes();
        if(mm < 10){
            mm = "0"+mm;
        }
        var ss = d.getSeconds();
        if(ss < 10){
            ss = "0"+ss;
        }
        var maxdate = ""+y+"-"+m+"-"+dd+"T"+hh+":"+mm+":"+ss+"";
        $("#comm_date").attr('max',maxdate);
    }
    function setCurrentCommunicationDate(){
        let now = new Date();
        let year = now.getFullYear();
        let month = String(now.getMonth() + 1).padStart(2, '0');
        let day = String(now.getDate()).padStart(2, '0');
        let hours = String(now.getHours()).padStart(2, '0');
        let minutes = String(now.getMinutes()).padStart(2, '0');
        let localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

        let commDateInput = document.getElementById("comm_date");
        commDateInput.value = localDateTime;
        commDateInput.max = localDateTime;
    }

    document.addEventListener("DOMContentLoaded", function() {
        setCurrentCommunicationDate();
    });
      $(document).ready(() => {

        $("#add_new_zero").click(function(){
            Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: "No clients have been added yet, or you have not been assigned any applications."
            });
        });

        $("#add_new").click(function(){
            setCurrentCommunicationDate();
            $("#add_new").css('display','none');
            $("#back").css('display','block');
            $("#new_discussion").css('display','block');
            $("#discussions").css('display','none');
        });
        $("#back").click(function(){
            $("#add_new").css('display','block');
            $("#back").css('display','none');
            $("#new_discussion").css('display','none');
            $("#discussions").css('display','block');
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
      text: 'Message sent successfully.'
    })
  </script>

@endif

@endsection()
