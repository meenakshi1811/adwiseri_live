@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <h3 class="text-primary text-center px-2">Add Payment Record</h3>
        <div class="col">
            <form class="register-box login-box" method="POST" action="{{ route('store_associate_payment') }}" autocomplete="off">
                @csrf

                <div class="row">
                    <div class="col-md-4 p-1"><label>Record Type</label></div>
                    <div class="col-md-8 p-1 d-flex align-items-center" style="gap:20px;">
                        <label class="m-0"><input type="radio" name="record_type" value="new" {{ old('record_type', 'new') === 'new' ? 'checked' : '' }}> New</label>
                        <label class="m-0"><input type="radio" name="record_type" value="existing" {{ old('record_type') === 'existing' ? 'checked' : '' }}> Existing</label>
                    </div>

                    <div class="col-md-4 p-1"><label>Select Invoice<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="associate_invoice_id" id="invoiceSelect" class="form-control form-select @error('associate_invoice_id') is-invalid @enderror" required>
                            <option value="">Select Invoice</option>
                            @foreach($invoices as $inv)
                            <option
                                value="{{ $inv->id }}"
                                data-bucket="{{ $inv->payment_bucket }}"
                                {{ (string) old('associate_invoice_id') === (string) $inv->id ? 'selected' : '' }}>
                                {{ $inv->display_label }}
                            </option>
                            @endforeach
                        </select>
                        @error('associate_invoice_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                        <small id="invoiceBucketHint" class="text-muted d-block mt-1"></small>
                    </div>

                    <div class="col-md-4 p-1"><label>Associate</label></div>
                    <div class="col-md-8 p-1"><input type="text" id="associateField" class="form-control" readonly placeholder="Associate"></div>

                    <div class="col-md-4 p-1"><label>Client</label></div>
                    <div class="col-md-8 p-1"><input type="text" id="clientField" class="form-control" readonly placeholder="Client"></div>

                    <div class="col-md-4 p-1"><label>Application</label></div>
                    <div class="col-md-8 p-1"><input type="text" id="applicationField" class="form-control" readonly placeholder="Application"></div>

                    <div class="col-md-4 p-1"><label>Service(s)</label></div>
                    <div class="col-md-8 p-1"><input type="text" id="serviceField" class="form-control" readonly placeholder="Service(s)"></div>

                    <div class="col-md-4 p-1"><label>Fees</label></div>
                    <div class="col-md-8 p-1"><input type="text" id="feesField" class="form-control" readonly placeholder="Fees"></div>

                    <div class="col-md-4 p-1"><label>Paid</label></div>
                    <div class="col-md-8 p-1"><input type="text" id="paidField" class="form-control" readonly placeholder="Paid"></div>

                    <div class="col-md-4 p-1"><label>Outstanding</label></div>
                    <div class="col-md-8 p-1"><input type="text" id="outstandingField" class="form-control" readonly placeholder="Outstanding"></div>

                    <div class="col-md-4 p-1"><label>Paying<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="paying" id="payingField" type="number" step="0.01" min="0.01" class="form-control @error('paying') is-invalid @enderror" value="{{ old('paying') }}" required placeholder="Paying">
                        @error('paying')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Payment Mode<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="payment_mode" class="form-control form-select @error('payment_mode') is-invalid @enderror" required>
                            <option value="">Payment Mode</option>
                            @foreach(['Cash','Cheque','DD','Wire','UPI','Vouchers','Notes'] as $mode)
                            <option value="{{ $mode }}" {{ old('payment_mode') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                            @endforeach
                        </select>
                        @error('payment_mode')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Payment Date<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="payment_date" type="text" class="form-control date @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', date('d-m-Y')) }}" max="{{ date('d-m-Y') }}" required placeholder="Payment Date" autocomplete="off">
                        @error('payment_date')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-12 p-1 text-center">
                        <button type="submit" class="btn btn-primary px-4">Submit</button>
                        <a href="{{ route('associate_payments') }}" class="btn btn-outline-primary px-4 ms-3">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        var $invoiceSelect = $("#invoiceSelect");
        var $hint = $("#invoiceBucketHint");
        var allOptions = $invoiceSelect.find('option').clone();

        function selectedBucket(){
            return $('input[name="record_type"]:checked').val() || 'new';
        }

        function clearInvoiceDetails(){
            $("#associateField,#clientField,#applicationField,#serviceField,#feesField,#paidField,#outstandingField").val('');
            $("#payingField").removeAttr('max');
        }

        function rebuildInvoiceOptions(preserveId){
            var bucket = selectedBucket();
            var prev = preserveId || $invoiceSelect.val();
            $invoiceSelect.empty().append('<option value="">Select Invoice</option>');

            var matched = 0;
            allOptions.each(function(){
                var $opt = $(this);
                if(!$opt.val()){ return; }
                if(String($opt.data('bucket')) !== String(bucket)){ return; }
                $invoiceSelect.append($opt.clone());
                matched++;
            });

            if(prev && $invoiceSelect.find('option[value="'+prev+'"]').length){
                $invoiceSelect.val(prev);
            } else {
                $invoiceSelect.val('');
                clearInvoiceDetails();
            }

            if(bucket === 'new'){
                $hint.text(matched ? 'Showing invoices with no payment recorded yet.' : 'No unpaid invoices without a payment yet.');
            } else {
                $hint.text(matched ? 'Showing invoices that already have at least one payment.' : 'No invoices with existing payments and outstanding balance.');
            }
        }

        function loadInvoiceDetails(invoiceId){
            if(!invoiceId){
                clearInvoiceDetails();
                return;
            }
            $.ajax({
                url: "{{ route('associate_invoice_details') }}",
                method: 'POST',
                data: { "_token": "{{ csrf_token() }}", invoice_id: invoiceId },
                cache: false,
                success: function(data){
                    $("#associateField").val((data.associate_name || '') + (data.associate_id ? ' (' + data.associate_id + ')' : ''));
                    $("#clientField").val((data.client_name || '') + (data.client_id ? ' (' + data.client_id + ')' : ''));
                    $("#applicationField").val(data.application_name || '');
                    $("#serviceField").val(data.services || data.service_provided || '');
                    $("#feesField").val(data.fees || '');
                    $("#paidField").val(data.paid || '');
                    $("#outstandingField").val(data.outstanding || '');
                    $("#payingField").attr('max', data.outstanding || '');
                }
            });
        }

        $('input[name="record_type"]').on('change', function(){
            rebuildInvoiceOptions();
        });

        $invoiceSelect.change(function(){ loadInvoiceDetails($(this).val()); });

        rebuildInvoiceOptions(@json(old('associate_invoice_id')));
        if($invoiceSelect.val()){ loadInvoiceDetails($invoiceSelect.val()); }
    });
</script>

@endsection()
