@extends('admin.layout.main')

@section('main-section')
<style>
    /* Subscribed / Unsubscribed pills (the status is a link that toggles it). */
    .column-client .email-sub-status,
    .column-client a.email-sub-status:hover {
        display: inline-block; padding: 2px 10px !important; border-radius: 999px; font-size: 12.5px; font-weight: 600;
        color: #fff !important; text-decoration: none; border: 0 !important; min-height: 0 !important;
    }
    .column-client .email-sub-status.is-on, .column-client a.email-sub-status.is-on:hover { background: #15803D !important; }
    .column-client .email-sub-status.is-off, .column-client a.email-sub-status.is-off:hover { background: #B91C1C !important; }
    #emailSubscriberTable td.email-sub-email { max-width: 360px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 flex-wrap gap-2 align-items-center justify-content-between">
                    <h3 class="text-primary text-center flex-grow-1 text-center m-0">Email Subscribers</h3>
                    @if(!$user->is_support)
                    <form method="POST" action="{{ route('store_email_subscriber') }}" class="d-flex flex-wrap gap-2 align-items-center ms-auto">
                        @csrf
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="form-control form-control-sm @error('email') is-invalid @enderror"
                               placeholder="Add email address"
                               required
                               style="min-width:220px;">
                        <button type="submit" class="btn btn-sm text-white" style="background:#695EEE;white-space:nowrap;">Add Email</button>
                    </form>
                    @endif
                </div>

                @error('email')
                    <div class="alert alert-danger py-2 px-3 small">{{ $message }}</div>
                @enderror
                @if(session()->has('error'))
                    <div class="alert alert-danger py-2 px-3 small">{{ session('error') }}</div>
                @endif

                <div class="table-wrapper">
                    <table class="fl-table table table-hover p-0 m-0" id="emailSubscriberTable" width="100%">
                        <thead>
                        <tr>
                            <th class="text-center">Sr No.</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Subscribed Date</th>
                            <th class="text-center">Status</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

  </div>

  <script>
     document.addEventListener("DOMContentLoaded", function() {
        // 10,000+ subscribers: rows come from the server a page at a time (search and sort run in SQL).
        if (window.jQuery && $.fn.DataTable && !$.fn.DataTable.isDataTable('#emailSubscriberTable')) {
            $('#emailSubscriberTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: @json(route('email_subscribers_data')),
                order: [[2, 'desc']],
                pageLength: 25,
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'email', name: 'email', className: 'text-center email-sub-email' },
                    { data: 'created_at', name: 'created_at', searchable: false, className: 'text-center' },
                    { data: 'status_html', name: 'status', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: { emptyTable: 'No email subscribers found.', processing: 'Loading…' }
            });
        }

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
  </script>

  @if(session()->has('status_updated'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Email subscriber status changed successfully.'
      })
    </script>
  @endif

  @if(session()->has('subscriber_added'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Email subscriber added successfully.'
      })
    </script>
  @endif

@endsection
