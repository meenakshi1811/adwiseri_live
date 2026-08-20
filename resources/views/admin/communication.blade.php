@extends('admin.layout.main')

@section('main-section')

<style>
  .dropdown-menu{
    height:auto;
    max-height:150px;
    overflow:auto;
  }
</style>

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 ">
                        <h3 class="text-primary text-center flex-grow-1 text-center m-0">Communication</h3>
                </div>
                @include('partials.admin_communication_tabs', ['activeTab' => 'communication'])

                <link rel="stylesheet" href="{{ asset('web_assets/css/topbar-notifications.css') }}">
                @include('partials.communication_inbox_toolbar')

                <div class="table-wrapper m-0">
                    <table class="fl-table table table-hover p-0 m-0 comm-table" id="clientTable" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="p-1 text-center" style="width: 8%;">Sub_ID</th>
                                <th class="p-1 text-center" style="width: 10%;">Status</th>
                                <th class="p-1 text-center" class="squeeze-column" style="width: 10%;">Comm. ID</th>
                                <th class="p-1 text-center" style="width: 14%;">Sent By</th>
                                <th class="p-1 text-center" style="width: 14%;">Sent To</th>
                                <th class="p-1 text-center" style="width: 28%;">Message</th>
                                <th class="p-1 text-center" style="width: 8%;">Date</th>
                                <th class="p-1 text-center" style="width: 12%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $sn => $msg)
                            @php
                                $messageStatus = $notificationService->messageStatusForUser($user, $msg);
                                $receiver = "";
                                $receivernames = json_decode($msg->receiver_name, true);
                                if (is_array($receivernames)) {
                                    foreach ($receivernames as $k => $name) {
                                        $receiver .= ($k == count($receivernames) - 1) ? $name : $name . ", ";
                                    }
                                }
                            @endphp
                            <tr class="{{ $messageStatus === 'unread' ? 'comm-row-unread' : '' }}" data-message-id="{{ $msg->id }}" data-message-status="{{ $messageStatus }}">
                                <td class="p-1 text-center">{{ $msg->subscriber_id }}</td>
                                <td class="p-1 text-center comm-status-cell">
                                    @include('partials.communication_status_badge', ['message' => $msg, 'messageStatus' => $messageStatus])
                                </td>
                                <td class="p-1 text-center squeeze-column comm-id-cell">{{ $msg->communication_id }}</td>
                                <td class="p-1 text-center">{{ $msg->sender_name }} ({{ $msg->user_id }})</td>
                                <td class="p-1 text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $receiver }}">
                                    @if(strlen($receiver) > 20)
                                        {{ substr($receiver, 0, 20) }}...
                                    @else
                                        {{ $receiver }}
                                    @endif
                                </td>
                                <td class="p-1 text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $msg->message }}">
                                    @if(strlen($msg->message) > 50)
                                        <span class="comm-message-cell">{{ substr($msg->message, 0, 50) }}...</span>
                                    @else
                                        <span class="comm-message-cell">{{ $msg->message }}</span>
                                    @endif
                                </td>
                                <td class="p-1 text-center squeeze-column">{{ \Carbon\Carbon::parse($msg->created_at)->format('d-m-Y H:i:s') }}</td>
                                <td class="p-1 text-center action-icon squeeze-column">
                                    @include('partials.communication_action_buttons', [
                                        'message' => $msg,
                                        'messageStatus' => $messageStatus,
                                        'canView' => true,
                                        'canDelete' => true,
                                        'viewRoute' => route('view_communication', $msg->id),
                                    ])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

  </div>
@include('partials.communication_action_scripts')
  <script>
    document.addEventListener("DOMContentLoaded", function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
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
  @if(session()->has('sent'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Message sent successfully.'
      })
    </script>
  @endif

@endsection()
