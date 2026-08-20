@extends('admin.layout.main')

@section('main-section')

        <div class="col-lg-10 column-client">
            @if(isset($query))
            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 ">
                    <form class="form-inline d-flex justify-content-between w-100">
                        <h3 class="text-primary">{{ $query->ticket_no }}</h3>
                        <a class="text-white" style="height:fit-content;cursor:pointer;" onclick="history.back();">Back</a>
                    </form>
                </div>
                <form method="POST" class="form-control" action="{{ route('send_query_response') }}">
                    @csrf
                <div class="col">
                    <div class="row px-3">
                        <input type="hidden" name="id" value="{{ $query->id }}" />
                        <div class="col-md-4 p-2">
                            <label style="font-weight:550;">Subscriber ID</label>
                        </div>
                        <div class="col-md-8 p-2">
                            <p class="form-control m-0">{{ $query->subscriber_id }}</p>
                        </div>
                        <!--<div class="col-md-4 p-2">
                            <label style="font-weight:550;">Client ID</label>
                        </div>
                        <div class="col-md-8 p-2">
                            <p class="form-control m-0">{{ $query->client_id }}</p>
                        </div>-->
                        <div class="col-md-4 p-2">
                            <label style="font-weight:550;">Status</label>
                        </div>
                        <div class="col-md-8 p-2 d-flex align-items-center gap-2">
                            <p class="form-control m-0 flex-grow-1">{{ $query->status }}</p>
                            @if($query->status == 'Open')
                                <a href="{{ route('update_query_status', $query->id) }}" class="btn btn-sm text-white" style="background:red;border-color:red;white-space:nowrap;">Mark Closed</a>
                            @else
                                <a href="{{ route('update_query_status', $query->id) }}" class="btn btn-sm text-white" style="background:green;border-color:green;white-space:nowrap;">Mark Open</a>
                            @endif
                        </div>
                        <div class="col-md-4 p-2">
                            <label style="font-weight:550;">Query/Issue</label>
                        </div>
                        <div class="col-md-8 p-2">
                            <p class="form-control m-0">
                                {{ $query->issue }}
                                </p>
                        </div>
                        <div class="col-md-4 p-2">
                            <label style="font-weight:550;">Response</label>
                        </div>
                        <div class="col-md-8 p-2">
                            <textarea rows="3" class="form-control" name="response" placeholder="Type Response"></textarea>
                            {{-- {{ $query->response }} --}}
                        </div>
                        <div class="col-md-4 p-2">
                            <label style="font-weight:550;"></label>
                        </div>
                        <div class="col-md-8 p-2 text-end">
                            <button type="submit" class="btn btn-primary">Send</button>
                            {{-- {{ $query->response }} --}}
                        </div>
                    </form>
                    </div>
                </div>
                
            </div>
            @endif
        </div>
    </div>

  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
  </script>
  <script>
      $(document).ready(() => {
        
          $("#country").change(function(){
            var country = $(this).val();
            // console.log(counrty);
            $.ajax({
                url: 'get_states',
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    country: country,
                },
                cache:false,
                success: function(data){
                  console.log(data);
                    $("#state").html(data);
                }
            });
          });
          $("#client").change(function(){
            var id = $(this).val();
            // console.log(counrty);
            $.ajax({
                url: 'get_job_role',
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    id: id,
                },
                cache:false,
                success: function(data){
                  console.log(data);
                    $("#job_role").html(data);
                }
            });
          });
      });
  </script>
  <script>
      function deleteuser(id){
          var conf = confirm('Are you sure you want to delete this user?');
          if(conf == true){
              window.location.href = "delete_user/"+id+"";
          }
      }
  </script>
  
  @if(session()->has('deleted'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Query deleted successfully.'
      })
    </script>
  
  @endif

@endsection()
            