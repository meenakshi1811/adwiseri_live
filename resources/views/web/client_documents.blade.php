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
                    'activeTab' => 'documents',
                    'application_roles' => $application_roles,
                    'user' => $user,
                    'clients' => $clients,
                    'applications' => $applications,
                ])


                <div style="display: none;" class="col" id="new_document">
                    <h5>Add New Document</h5>
                    <form id="registration_form" class="register-box login-box" method="POST" action="{{ route('upload_client_document') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="local_time" class="localtime" />
                        <div class="row">
                            <div class="col-md-4 p-1">
                                <label>Client ID<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="client_id" id="client_id" class="form-control form-select @error('client_id') is-invalid @enderror" required>
                                    <option value="" {{ old('client_id') ? '' : 'selected' }}>Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }} ({{ $client->id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Application ID<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="application_id" id="application_id" required class="form-control form-select @error('application_id') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp">
                                    <option value="">Select Application</option>
                                    @if(old('application_id'))
                                    <option value="{{old('application_id')}}" selected>{{old('application_id')}}</option>
                                    @endif
                                </select>
                                @error('application_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-4 p-1">
                                <label>Document Type<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="doc_type" id="doc_type" class="form-control form-select @error('doc_type') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                    <option value="">Select Document Type</option>
                                    <option value="Photo">Photo</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Birth Certificate">Birth Certificate</option>
                                    <option value="Marriage Certificate">Marriage Certificate</option>
                                    <option value="Degree Certificate">Degree Certificate</option>
                                    <option value="Application Form">Application Form</option>
                                    <option value="Decision">Decision</option>
                                    <option value="Appeal">Appeal</option>
                                    <option value="Appeal Decision">Appeal Decision</option>
                                    <option value="Admin/Judicial Review">Admin/Judicial Review</option>
                                    <option value="AR/JR Decision">AR/JR Decision</option>
                                    <option value="Supporting Document 1">Supporting Document 1</option>
                                    <option value="Supporting Document 2">Supporting Document 2</option>
                                    <option value="Supporting Document 3">Supporting Document 3</option>
                                    <option value="Supporting Document 4">Supporting Document 4</option>
                                    <option value="Supporting Document 5">Supporting Document 5</option>
                                    <option value="Supporting Document 6">Supporting Document 6</option>
                                    <option value="Supporting Document 7">Supporting Document 7</option>
                                    <option value="Supporting Document 8">Supporting Document 8</option>
                                    <option value="Supporting Document 9">Supporting Document 9</option>
                                    <option value="Supporting Document 10">Supporting Document 10</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('doc_type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Document Name<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="doc_name" type="text" minlength="3" maxlength="100" class="form-control @error('doc_name') is-invalid @enderror" id="doc_name" aria-describedby="emailHelp" value="{{ old('doc_name') }}" required placeholder="Document Name" autocomplete="doc_name">
                            @error('doc_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Document File<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="doc_file" type="file" class="form-control @error('doc_file') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" accept=".jpg,.jpeg,.png,.pdf" required>
                            @error('doc_file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col text-center p-1">
                                <button type="submit" class="form-control btn btn-primary" style="width: fit-content;">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-wrapper" id="documents">
                    <table class="table table-hover table-bordered fl-table" id="clientTable">
                        <thead>
                        <tr>
                            <th class="p-1 text-center">Sr No.</th>
                            <th class="p-1 text-center">DocumentID</th>
                            <th class="p-1 text-center">Client(ID)</th>
                            <th class="p-1 text-center">Application (ID)</th>
                            <th class="p-1 text-center">Type</th>
                            <th class="p-1 text-center">Name</th>
                            <th class="p-1 text-center">File Name</th>
                            <th class="p-1 text-center">Uploaded Date</th>
                            <th class="p-1 text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($client_docs as $key=>$doc)
                        <tr>
                            <td class="p-1 text-center"> {{ $key+1}}</td>
                            <td class="p-1 text-center"> {{ $doc->id}}</td>
                            <td class="p-1 text-center">{{ $doc->client_id ? $doc->client->name .'('.$doc->client_id.')' : '' }}</td>
                            <td class="p-1 text-center">{{ $doc->application_id  ? $doc->application->application_name .'('.$doc->application_id.')' : '' }}</td>
                            <td class="p-1 text-center">{{ $doc->doc_type }}</td>
                            <td class="p-1 text-center">{{ $doc->doc_name }}</td>
                                                        <td class="p-1 text-center">
                                @php $shortFileName = \App\Support\DocumentFileName::forTable($doc->doc_file, $doc->doc_name); @endphp
                                <a @if($application_roles->read_only == 1) href="{{ asset('web_assets/users/client'.$doc->client_id.'/docs/'.$doc->doc_file) }}" download="{{ $doc->doc_file }}" @else href="#" @endif class="p-0 m-0" style="text-decoration: none;border:none;background:none;" title="{{ $doc->doc_file }}"><i class="fa-solid fa-download btn p-1 text-primary" style="font-size:14px;"></i></a><span class="doc-file-label" title="{{ $doc->doc_file }}">{{ $shortFileName }}</span>
                            </td>
                            <td class="p-1 text-center">{{ date("d-m-Y H:i:s", strtotime($doc->created_at)) }}</td>
                            <td class="p-1 text-center">
                                {{-- <a style="background:transparent;border:none;" class="p-0 m-0 text-dark" href="{{ route('application_view', $doc->id)}}"><i class="fa-solid fa-eye btn text-info p-1 m-0"></i></a> --}}
                                <i class="fa-solid fa-edit btn text-primary p-1 m-0" style="font-size:14px;" @if($application_roles->update_only ==1) onclick="updatedocument({{ $doc->id }})" @endif></i>
                                <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" @if($application_roles->delete_only == 1) onclick="deletedocument({{ $doc->id }})" @endif></i>
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
    function deletedocument(id){
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
          window.location.href = "delete_document/"+id+"";
        }
      })
    }
      function updatedocument(id){
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
            window.location.href = "client_document_update/"+id+"";
          }
        })
      }
  </script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
  </script>
  <script>
    $(document).ready(() => {

        $("#add_new_zero").click(function(){
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

        $("#add_new").click(function(){
            $("#add_new").css('display','none');
            $("#back").css('display','block');
            $("#new_document").css('display','block');
            $("#documents").css('display','none');
        });
        $("#back").click(function(){
            $("#add_new").css('display','block');
            $("#back").css('display','none');
            $("#new_document").css('display','none');
            $("#documents").css('display','block');
        });
        $("#client_id").change(function(){
            var app_id = $(this).val();
            // console.log(counrty);
            $.ajax({
                url: "{{route('get_client')}}",
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    id: app_id,
                },
                cache:false,
                success: function(data){
                    console.log(data);
                    $("#application_id").html(data);
                }
            });
        });
        var lastAutoDocName = @json(trim(old('doc_name', '')));

        function syncDocNameFromType(force) {
            var type = ($('#doc_type').val() || '').trim();
            if (type === '') return;

            var $nameInput = $('#doc_name');
            var currentName = ($nameInput.val() || '').trim();
            if (force || currentName === '' || currentName === lastAutoDocName) {
                $nameInput.val(type);
                lastAutoDocName = type;
            }
        }

        $('#doc_type').on('change', function () {
            syncDocNameFromType(true);
        });

        $(document).on('change', 'input[type=file]', function(){
          const file = this.files[0];
          var filepath = $(this).val();
          var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.pdf|\.JPG|\.JPEG|\.PNG|\.PDF)$/i;
          if (!allowedExtensions.exec(filepath)) {
              Swal.fire({
                  title: "Oops!",
                  icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                  html: "Please select a valid file format (jpg, jpeg, png, or pdf)."
              });
              $(this).val("");
              return false;
          }
          const size = (this.files[0].size / 1024 / 1024).toFixed(2);
          if (size > 4) {
              Swal.fire({
                  title: "Oops!",
                  icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                  html: "Please select a file up to 4 MB."
              });
              $(this).val("");
              return false;
          }
        });
    });
  </script>


  @if($errors->has('doc_file'))
    <script>
      Swal.fire({
        title: 'Oops!',
        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
        html: @json($errors->first('doc_file'))
      })
    </script>
  @endif

  @if(session()->has('deleted'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Document deleted successfully.'
      })
    </script>

  @endif
  @if(session()->has('document_added'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Document added successfully.'
      })
    </script>

  @endif
  @if(session()->has('document_updated'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Document updated successfully.'
      })
    </script>

  @endif

@endsection()
