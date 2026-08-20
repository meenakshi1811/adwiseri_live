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

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  #message_form {
    border: none !important;
    padding: 0.75rem;
  }
  #message_form .msg-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 0.85rem;
  }
  #message_form .msg-label {
    flex: 0 0 50%;
    max-width: 50%;
    padding-right: 0.75rem;
  }
  #message_form .msg-label h6 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.4;
  }
  #message_form .msg-field {
    flex: 0 0 50%;
    max-width: 50%;
  }
  #message_form .form-select,
  #message_form .form-control,
  #message_form textarea.form-control {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    min-height: 38px;
    font-size: 0.95rem;
  }
  #message_form .select2-container {
    width: 100% !important;
  }
  #message_form .select2-container--default .select2-selection--multiple {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 38px;
    max-height: 110px;
    overflow-y: auto;
    padding: 4px 28px 4px 8px;
    background-color: #fff;
    position: relative;
  }
  #message_form .select2-container--default.select2-container--focus .select2-selection--multiple,
  #message_form .form-select:focus,
  #message_form .form-control:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }
  #message_form .select2-container--default .select2-selection--multiple .select2-selection__rendered {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding: 0;
    max-height: 100px;
    overflow-y: auto;
  }
  #message_form .select2-container--default .select2-selection--multiple .select2-selection__clear {
    position: absolute;
    right: 8px;
    top: 6px;
    margin: 0;
    z-index: 2;
    font-size: 1rem;
    line-height: 1;
  }
  #message_form .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #695EEE;
    border: 1px solid #695EEE;
    color: #fff;
    border-radius: 0.25rem;
    padding: 2px 8px 2px 4px;
    margin: 0;
    float: none;
    position: relative;
    display: inline-flex;
    align-items: center;
    line-height: 1.35;
    max-width: 100%;
  }
  #message_form .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    position: static !important;
    left: auto !important;
    top: auto !important;
    color: #fff !important;
    margin: 0 6px 0 0;
    border: none !important;
    border-right: 1px solid rgba(255, 255, 255, 0.4) !important;
    border-radius: 0 !important;
    padding: 0 6px 0 2px !important;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
    background: transparent !important;
    flex-shrink: 0;
  }
  #message_form .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #f8f9fa !important;
    background: transparent !important;
  }
  #message_form .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
    padding: 0;
    cursor: default;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  #message_form .select2-container--default .select2-search--inline .select2-search__field {
    margin-top: 3px;
    height: 24px;
  }
  #message_form .select2-dropdown {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
  }
  #message_form .select2-results__option--highlighted[aria-selected] {
    background-color: #695EEE;
  }
  #message_form .select2-results__option[aria-selected="true"] {
    background-color: #ecebff;
  }
  @media (max-width: 767.98px) {
    #message_form .msg-label,
    #message_form .msg-field {
      flex: 0 0 100%;
      max-width: 100%;
      padding-right: 0;
    }
    #message_form .msg-label {
      margin-bottom: 0.35rem;
    }
  }
</style>
@endpush

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <div class="client-btn d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-primary text-center flex-grow-1 text-center m-0">Messaging</h3>
                  </div>
              @include('partials.communication_tabs', ['activeTab' => 'messaging'])

              <div class="col mt-3 p-2 messaging" style="border: 2px solid lightgrey; border-radius:7px;">
                <h4 class="text-center pb-3">Send Message</h4>
                <form id="message_form" class="form-control" method="POST" action="{{route('communicate')}}">
                    @csrf
                    <input type="hidden" name="local_time" class="localtime" />

                    <div class="msg-row">
                      <div class="msg-label">
                          <h6>Send To</h6>
                      </div>
                      <div class="msg-field">
                        <select id="recipientsId" name="sendto[]" class="form-select" multiple data-placeholder="Select Recipient(s)">
                            <option value="__all__">Select All</option>
                            @if($user->user_type == "Subscriber")
                            <option value="all user">All User(Staff)</option>
                            @else
                            <option value="{{$user->added_by}}">Subscriber</option>
                            @endif

                            @if($siteusers)
                            @foreach($siteusers as $suser)
                            <option value="{{$suser->id}}">{{$suser->name}}</option>
                            @endforeach
                            @endif
                        </select>
                        @error('sendto')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                      </div>
                    </div>

                    <div class="msg-row" style="align-items: flex-start;">
                      <div class="msg-label" style="padding-top: 0.5rem;">
                          <h6>Message</h6>
                      </div>
                      <div class="msg-field">
                        <textarea rows="3" class="form-control" minlength="3" maxlength="500" required name="message" placeholder="Type Message"></textarea>
                        @error('message')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                      </div>
                    </div>

                    <div class="msg-row">
                      <div class="msg-label"></div>
                      <div class="msg-field">
                          <input class="btn btn-primary" type="submit" value="Send" />
                      </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>

  </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    const $recipients = $('#recipientsId');
    const allOptionValue = '__all__';

    function getAllOptionValues() {
        const values = [];
        $recipients.find('option').each(function () {
            const val = $(this).val();
            if (val && val !== allOptionValue) {
                values.push(val);
            }
        });
        return values;
    }

    $recipients.select2({
        placeholder: $recipients.data('placeholder') || 'Select Recipient(s)',
        allowClear: true,
        width: '100%',
        closeOnSelect: false
    });

    $recipients.on('select2:select', function (e) {
        if (e.params.data.id === allOptionValue) {
            $(this).val(getAllOptionValues()).trigger('change');
        }
    });

    $('#message_form').on('submit', function (e) {
        let selected = $recipients.val() || [];
        selected = selected.filter(function (val) {
            return val !== allOptionValue;
        });
        $recipients.val(selected);

        if (!selected.length) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'No recipients selected',
                text: 'Please choose at least one recipient.'
            });
        }
    });
});
</script>
@endpush

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
