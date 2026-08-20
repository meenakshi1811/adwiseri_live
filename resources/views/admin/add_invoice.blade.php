@extends('admin.layout.main')

@section('main-section')

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <div class="invoice-form-card">
                <div class="invoice-form-header">
                    <div>
                        <h3 class="text-primary mb-0">Create New Invoice</h3>
                        <p class="text-muted mb-0">Generate a subscriber invoice with one or more services</p>
                    </div>
                </div>
                <div class="col px-0">
                    <form id="registration_form" class="register-box login-box invoice-edit-form" method="POST" action="{{ route('admin_new_invoice_post') }}" onsubmit="document.getElementById('invoice_submit').setAttribute('disabled','true');">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 p-1">
                                <label>Subscriber<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="subscriber" id="subscriber" required class="form-control form-select @error('client') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp">
                                    <option value="">Select Subscriber</option>
                                    @foreach($subscribers as $subs)
                                    <option value="{{ $subs->id }}">{{ $subs->name."(".$subs->id.")" }}</option>
                                    @endforeach
                                </select>
                                @error('subscriber')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            @include('web.partials.invoice_service_rows', [
                                'showApplication' => false,
                                'detailLabel' => 'Service Description',
                                'detailPlaceholder' => 'Service Description',
                                'amountLabel' => 'Amount',
                                'amountPlaceholder' => 'Amount',
                            ])
                            @include('web.partials.invoice_note_form_field', [
                                'invoiceNote' => $invoiceNote ?? '',
                                'isLocked' => false,
                            ])
                            <div class="col-md-4 p-1">
                                <label>Invoice Status<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <select name="status" id="status" class="form-control form-select @error('status') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                                    <option value="">Select Status</option>
                                    <option {{ (old('status') == "PartiallyPaid") ? 'selected' : ''}} value="PartiallyPaid">Partially Paid</option>
                                    <option {{ (old('status') == "UnPaid") ? 'selected' : ''}} value="UnPaid">Unpaid</option>
                                    <option {{ (old('status') == "Paid") ? 'selected' : ''}} value="Paid">Paid</option>
                                    <option {{(old('status') == "Cancelled") ? 'selected':''}} value="Cancelled">Cancelled</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4 p-1">
                                <label>Payment Due Date<span class="text-danger" style="font-size: 18px;">*</span></label>
                            </div>
                            <div class="col-md-8 p-1">
                                <input name="due_date" required type="text" class="form-control datepicker @error('due_date') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" value="{{ old('due_date', date('d-m-Y')) }}"
                                placeholder="dd-mm-yyyy" autocomplete="due_date" pattern="\d{2}-\d{2}-\d{4}">
                            @error('due_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                            <div class="col-md-4 p-1"></div>
                            <div class="col-md-8 p-1">
                                <div class="invoice-form-actions">
                                <button type="submit" id="invoice_submit" class="invoice-btn invoice-btn-primary">
                                    <i class="fa-solid fa-check"></i> Submit
                                </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
                
            </div>
        </div>
    </div>

  </div>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
  </script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
      $(document).ready(() => {
          flatpickr('.datepicker', {
            dateFormat: "d-m-Y",
            defaultDate: $('input[name="due_date"]').val() || "today",
            allowInput: true,
            clickOpens: true
          });

          initInvoiceServiceRows({
              showApplication: false,
              subscriberSelector: '#subscriber'
          });
      });
  </script>
  <script>
      function deleteuser(id){
          var conf = confirm('Are you sure you want to delete this invoice?');
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
        text: 'Invoice deleted successfully.'
      })
    </script>
  
  @endif

@endsection()
            
