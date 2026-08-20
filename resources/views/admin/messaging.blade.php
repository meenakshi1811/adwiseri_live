@extends('admin.layout.main')

@section('main-section')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  #message_form {
    border: none;
    padding: 0.75rem;
  }
  #message_form .msg-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 0.85rem;
  }
  #message_form .msg-row.admin-recipient-group {
    display: none;
  }
  #message_form .msg-row.admin-recipient-group.is-visible {
    display: flex;
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
    font-family: 'Lato', sans-serif !important;
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
                <div class="client-btn d-flex mb-2 ">
                        <h3 class="text-primary text-center flex-grow-1 text-center m-0">Messaging</h3>
                </div>
                @include('partials.admin_communication_tabs', ['activeTab' => 'messaging'])

                <div class="col mt-3 p-2" style="border: 2px solid lightgrey; border-radius:7px;">
                  <h4 class="text-center pb-3">Send Message</h4>
                  <form id="message_form" class="form-control" method="POST" action="{{ route('admin_communicate') }}">
                      @csrf
                      <input type="hidden" name="local_time" class="localtime" />

                      <div class="msg-row">
                        <div class="msg-label">
                            <h6>Send To</h6>
                        </div>
                        <div class="msg-field">
                          <select class="form-select" id="receiver" name="receiver" required>
                            <option value="">Select Recipient(s)</option>
                            <option value="Subscribers" {{ old('receiver') === 'Subscribers' ? 'selected' : '' }}>Subscribers</option>
                            <option value="Users" {{ old('receiver') === 'Users' ? 'selected' : '' }}>Staff (Users)</option>
                          </select>
                            @error('sendto')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                      </div>

                      <div class="msg-row admin-recipient-group subscribers-group">
                        <div class="msg-label">
                            <h6>Select Subscriber(s)</h6>
                        </div>
                        <div class="msg-field">
                            <select id="subscribersId" name="sendto[]" class="form-select select2-recipients" multiple data-placeholder="Select Subscriber(s)" disabled>
                                <option value="__all__">All Subscribers</option>
                                @if($subscribers)
                                @foreach($subscribers as $suser)
                                <option value="{{ $suser->id }}">{{ $suser->name }} ({{ $suser->id }})</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                      </div>

                      <div class="msg-row admin-recipient-group users-group">
                        <div class="msg-label">
                            <h6>Select User(s)</h6>
                        </div>
                        <div class="msg-field">
                            <select id="userId" name="sendto[]" class="form-select select2-recipients" multiple data-placeholder="Select User(s)" disabled>
                                <option value="__all__">All Users</option>
                                @if($users)
                                @foreach($users as $usrs)
                                @php
                                    $parentSubscriber = isset($subscriberLookup) ? $subscriberLookup->get($usrs->added_by) : null;
                                    $subscriberLabel = $parentSubscriber
                                        ? $parentSubscriber->name . '(' . $parentSubscriber->id . ')'
                                        : 'N/A';
                                @endphp
                                <option value="{{ $usrs->id }}">{{ $subscriberLabel }} - {{ $usrs->name }} ({{ $usrs->id }})</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                      </div>

                      <div class="msg-row" style="align-items: flex-start;">
                        <div class="msg-label" style="padding-top: 0.5rem;">
                            <h6>Message</h6>
                        </div>
                        <div class="msg-field">
                          <textarea rows="3" class="form-control" minlength="3" maxlength="250" required name="message" placeholder="Type Message">{{ old('message') }}</textarea>
                            @error('message')
                            <span class="text-danger">{{ $message }}</span>
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

@endsection()

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $subscribers = $('#subscribersId');
    const $users = $('#userId');
    const receiverSelect = document.getElementById('receiver');
    const subscribersGroup = document.querySelector('.subscribers-group');
    const usersGroup = document.querySelector('.users-group');
    const allOptionValue = '__all__';

    function getAllOptionValues($el) {
        const values = [];
        $el.find('option').each(function () {
            const val = $(this).val();
            if (val && val !== allOptionValue) {
                values.push(val);
            }
        });
        return values;
    }

    function initSelect2($el) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2({
            placeholder: $el.data('placeholder') || 'Select',
            allowClear: true,
            width: '100%',
            closeOnSelect: false
        });
    }

    function bindSelectAll($el) {
        $el.off('select2:select.selectAll select2:unselect.selectAll');

        $el.on('select2:select.selectAll', function (e) {
            if (e.params.data.id === allOptionValue) {
                $(this).val(getAllOptionValues($(this))).trigger('change');
            }
        });

        $el.on('select2:unselect.selectAll', function (e) {
            // If user clears one item after select-all, just leave remaining selections
            if (e.params && e.params.data && e.params.data.id === allOptionValue) {
                $(this).val(null).trigger('change');
            }
        });
    }

    function setGroupVisibility(type) {
        if (subscribersGroup) {
            subscribersGroup.classList.toggle('is-visible', type === 'Subscribers');
        }
        if (usersGroup) {
            usersGroup.classList.toggle('is-visible', type === 'Users');
        }

        $subscribers.prop('disabled', type !== 'Subscribers');
        $users.prop('disabled', type !== 'Users');

        if (type === 'Subscribers') {
            initSelect2($subscribers);
            bindSelectAll($subscribers);
        } else if (type === 'Users') {
            initSelect2($users);
            bindSelectAll($users);
        }
    }

    function clearRecipientSelects() {
        if ($subscribers.hasClass('select2-hidden-accessible')) {
            $subscribers.val(null).trigger('change');
        }
        if ($users.hasClass('select2-hidden-accessible')) {
            $users.val(null).trigger('change');
        }
    }

    if (receiverSelect) {
        $(receiverSelect).on('change', function () {
            clearRecipientSelects();
            setGroupVisibility(this.value);
        });
        setGroupVisibility(receiverSelect.value);
    }

    $('#message_form').on('submit', function (e) {
        const type = receiverSelect ? receiverSelect.value : '';
        let $active = null;

        if (type === 'Subscribers') {
            $active = $subscribers;
        } else if (type === 'Users') {
            $active = $users;
        }

        let selected = $active ? ($active.val() || []) : [];
        selected = selected.filter(function (val) {
            return val !== allOptionValue;
        });

        if ($active) {
            $active.val(selected);
        }

        if (!selected.length) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'No recipients selected',
                text: 'Please choose at least one subscriber or user.'
            });
        }
    });
});
</script>
@if (session()->has('sent'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Message sent successfully.'
    });
</script>
@endif
@if (session()->has('noUser'))
<script>
    Swal.fire({
        icon: 'warning',
        title: 'No recipients selected',
        text: 'Please choose at least one subscriber or user.'
    });
</script>
@endif
@endpush
