@extends('admin.layout.main')

@section('main-section')

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 ">
                    <form class="form-inline d-flex justify-content-between w-100">
                        <h3 class="text-primary">Feedbacks</h3>
                        {{-- <p>
                          <a href="{{ route('new_client') }}" class="m-0">Add New</a>
                          <a href="{{ route('clients_export') }}" class="m-0">Export</a>
                        </p> --}}
                        {{-- <div class="d-flex ">
                            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                        </div> --}}
                      </form>
                      {{-- <i class="fa-solid fa-magnifying-glass"></i> --}}
                </div>
                <div class="alert alert-info mb-3" role="note">
                    <strong>Feedback popup rules (rule of thumb):</strong>
                    <ul class="mb-0 mt-2">
                        <li>Applies to every <strong>Subscriber</strong> and <strong>staff user</strong> in the web portal (each account is tracked separately).</li>
                        <li><strong>First review:</strong> on the <strong>90th day</strong> — from subscription purchase date for subscribers, or account creation date for staff.</li>
                        <li><strong>Second review onwards:</strong> at most <strong>once per year per user</strong>, counted from that user&rsquo;s anchor date anniversary.</li>
                        <li>Example (subscriber, purchase 1 Jan 2025): first popup from 1 Apr 2025; next from 1 Jan 2026 if not submitted in that year.</li>
                        <li>Example (staff, added 1 Mar 2025): first popup from 29 May 2025; next from 1 Mar 2026 if not submitted in that year.</li>
                    </ul>
                </div>
                <div class="table-wrapper">
                    <table class="table table-hover table-bordered fl-table" id="clientTable">
                        <thead>
                        <tr>
                            <th class="p-1 text-center">ID</th>
                            <th class="p-1 text-center">User Name(User_ID)</th>
                            <th class="p-1 text-center">Rating</th>
                            <th class="p-1 text-center">Feedback</th>
                            <th class="p-1 text-center">Feedback date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($feedbacks as $feedback)
                        <tr>
                            <td class="text-center">{{ $feedback->id }}</td>
                            <td class="text-center" style="position: relative;">{{ $feedback->user->name .'('.$feedback->user_id.')' }}</td>
                            <td class="text-center">{{ $feedback->rating }}</td>
                            <td class="text-center">{{ $feedback->feedback }}</td>
                            <td class="text-center">{{  \Carbon\Carbon::parse($feedback->created_at)->format('d-m-Y H:i:s') }}</td>

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
      function deleteclient(id){
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
            window.location.href = "delete_clients/"+id+"";
          }
        })
          // var conf = confirm('Are you sure you want to delete this client?');
          // if(conf == true){
          //     window.location.href = "delete_clients/"+id+"";
          // }
      }
      function updateclient(id){
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
            window.location.href = "client_update/"+id+"";
          }
        })
      }
  </script>

  @if(session()->has('deleted'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Client deleted successfully.'
      })
    </script>

  @endif
  @if(session()->has('client_added'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Client added successfully.'
      })
    </script>

  @endif
  @if(session()->has('client_updated'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Client updated successfully.'
      })
    </script>

  @endif

@endsection()
