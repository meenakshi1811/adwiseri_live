@extends('admin.layout.main')

@section('main-section')

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
                        <tbody>
                            @forelse($emailSubscribers as $key => $subscriber)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $subscriber->email }}" style="position: relative;">
                                    @if(strlen($subscriber->email) > 30)
                                        {{ substr($subscriber->email, 0, 30) }}...
                                        <span onmouseover="this.style.opacity='1';" onmouseout="this.style.opacity='0';" style="display:flex;opacity:0;align-items:center;padding:5px;position: absolute;left:0px;top:25px;height:100%;background:lightgrey;min-width:100%; width:fit-content;">{{ $subscriber->email }}</span>
                                    @else
                                        {{ $subscriber->email }}
                                    @endif
                                </td>
                                <td class="text-center" data-order="{{ $subscriber->created_at ? $subscriber->created_at->timestamp : 0 }}">
                                    {{ $subscriber->created_at ? $subscriber->created_at->format('d-m-Y H:i:s') : '—' }}
                                </td>
                                <td class="text-center">
                                    @if(!$user->is_support)
                                        @if($subscriber->isSubscribed())
                                            <a style="background:green;border-color:green;"
                                               href="{{ route('email_subscription_status', $subscriber->id) }}"
                                               class="p-0 px-2 text-white"
                                               title="Click to unsubscribe">Subscribed</a>
                                        @else
                                            <a style="background:red;border-color:red;"
                                               href="{{ route('email_subscription_status', $subscriber->id) }}"
                                               class="p-0 px-2 text-white"
                                               title="Click to subscribe">Unsubscribed</a>
                                        @endif
                                    @else
                                        @if($subscriber->isSubscribed())
                                            <span style="background:green;border-color:green;display:inline-block;padding:0 0.5rem;color:#fff;border-radius:4px;">Subscribed</span>
                                        @else
                                            <span style="background:red;border-color:red;display:inline-block;padding:0 0.5rem;color:#fff;border-radius:4px;">Unsubscribed</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No email subscribers found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

  </div>

  <script>
     document.addEventListener("DOMContentLoaded", function() {
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
