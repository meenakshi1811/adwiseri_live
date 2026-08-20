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

$canViewMessages = $user->user_type == 'admin'
    || ($communication_roles && ($communication_roles->read_only == 1 || $communication_roles->read_write_only == 1));
$canDeleteMessages = $user->user_type == 'admin'
    || ($communication_roles && ($communication_roles->write_only == 1 || $communication_roles->read_write_only == 1));
@endphp

        <div class="col-lg-10 column-client">
            <div class="col-12 d-flex justify-content-between align-items-center  mb-3">
                    <h3 class="text-primary text-center flex-grow-1 text-center m-0">Communications</h3>
              </div>
            <div class="client-dashboard">
              @include('partials.communication_tabs', ['activeTab' => 'communication'])

              <link rel="stylesheet" href="{{ asset('web_assets/css/topbar-notifications.css') }}">
              @include('partials.communication_inbox_toolbar')

              <div class="table-wrapper m-0 communication">
                <table class="fl-table table table-hover p-0 m-0 comm-table" id="clientTable">
                    <thead>
                    <tr>
                        <th class="p-1 text-center">Sr No.</th>
                        <th class="p-1 text-center">Status</th>
                        <th class="p-1 text-center">Comm. ID</th>
                        <th class="p-1 text-center">Sent By</th>
                        <th class="p-1 text-center">Sent To</th>
                        <th class="p-1 text-center">Message</th>
                        <th class="p-1 text-center">Date</th>
                        <th class="p-1 text-center">Action</th>
                    </tr>
                    </thead>
                        <tbody>
                            @if($messages != null)
                                @foreach($messages as $sn => $msg)
                                @php
                                    $messageStatus = $notificationService->messageStatusForUser($user, $msg);
                                    if($msg->send_by == 1){
                                        $receiver = $user->name;
                                    }
                                    else{
                                        $receiver = "";
                                        $receivernames = json_decode($msg->receiver_name, true);
                                        if (is_array($receivernames)) {
                                            foreach($receivernames as $k => $name){
                                                if($k == count($receivernames)-1){
                                                    $receiver = $receiver.$name;
                                                }
                                                else{
                                                    $receiver = $receiver.$name.", ";
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <tr class="{{ $messageStatus === 'unread' ? 'comm-row-unread' : '' }}" data-message-id="{{ $msg->id }}" data-message-status="{{ $messageStatus }}">
                                    <td class="p-1 text-center" style="width: 5%;">{{ $sn+1 }}</td>
                                    <td class="p-1 text-center comm-status-cell" style="width: 10%;">
                                        @include('partials.communication_status_badge', ['message' => $msg, 'messageStatus' => $messageStatus])
                                    </td>
                                    <td class="p-1 text-center comm-id-cell" style="width: 10%;">{{ $msg->communication_id }}</td>
                                    <td class="p-1 text-center" style="width: 15%;">{{ $msg->sender_name }} ({{ $msg->send_by }})</td>
                                    <td  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $receiver }}"  class="p-1 text-center" style="width: 15%;">@if(strlen($receiver) > 22){{ substr($receiver, 0, 22) }}... @else {{ $receiver }} @endif</td>
                                    <td  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $msg->message }}"  class="p-1 text-center comm-message-cell" style="width: 35%;">@if(strlen($msg->message) > 50){{ substr($msg->message, 0, 50) }}... @else {{ $msg->message }} @endif</td>
                                    <td class="p-1 text-center" style="width: 10%;">{{ \Carbon\Carbon::parse($msg->created_at)->format('d-m-Y H:i:s') }}</td>
                                    <td class="p-1 text-center action-icon" style="width: 12%;">
                                        @include('partials.communication_action_buttons', [
                                            'message' => $msg,
                                            'messageStatus' => $messageStatus,
                                            'canView' => $canViewMessages,
                                            'canDelete' => $canDeleteMessages || ((int) $msg->send_by === (int) $user->id),
                                            'viewRoute' => $canViewMessages ? route('view_message', $msg->id) : '#',
                                        ])
                                    </td>
                                </tr>
                                @endforeach
                            @endif
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

  function togglepage(page){
    var vpage = page;
    if(vpage == "communication"){
      $(".communication").css('display','block');
      $(".messaging").css('display','none');
    }
    else{
      $(".communication").css('display','none');
      $(".messaging").css('display','block');
    }
  }

</script>

@if(session()->has('deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Application deleted successfully.'
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
@if(session()->has('application_updated'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Application updated successfully.'
    })
  </script>

@endif
@endsection()
