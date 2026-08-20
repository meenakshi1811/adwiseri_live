@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <h3 class="text-primary text-center px-2">Create Invoice</h3>
        <div class="col">
            <form class="register-box login-box" method="POST" action="{{ route('store_associate_invoice') }}" autocomplete="off">
                @csrf
                <div class="row">
                    <div class="col-md-4 p-1"><label>Select Associate<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="associate_id" id="associateSelect" class="form-control form-select @error('associate_id') is-invalid @enderror" required>
                            <option value="">Select Associate</option>
                            @foreach($associates as $a)
                            <option value="{{ $a->id }}" {{ old('associate_id') == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ $a->id }})</option>
                            @endforeach
                        </select>
                        @error('associate_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Select Client<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="client_id" id="clientSelect" class="form-control form-select @error('client_id') is-invalid @enderror" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->id }})</option>
                            @endforeach
                        </select>
                        @error('client_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Link Application<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="application_id" id="applicationSelect" class="form-control form-select @error('application_id') is-invalid @enderror" required>
                            <option value="">Select Application</option>
                            @foreach($applications as $app)
                            <option value="{{ $app->id }}" data-client-id="{{ $app->client_id }}" {{ old('application_id') == $app->id ? 'selected' : '' }}>{{ $app->application_name }} ({{ $app->application_id }})@if($app->client_name) — {{ $app->client_name }}@endif</option>
                            @endforeach
                        </select>
                        @error('application_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Select Services<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        @include('web.associates._service_checkboxes', ['services' => $services, 'selectedServices' => old('services', [])])
                        <div id="businessServiceHiddenInputs"></div>
                    </div>

                    <div class="col-md-4 p-1"><label>Fees<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="fees" id="feesField" type="number" step="0.01" min="0" class="form-control @error('fees') is-invalid @enderror" value="{{ old('fees') }}" required readonly placeholder="Select associate and application">
                        @error('fees')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Status<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="status" class="form-control form-select @error('status') is-invalid @enderror" required>
                            @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('status', 'UnPaid') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Due Date</label></div>
                    <div class="col-md-8 p-1">
                        <input name="due_date" type="text" class="form-control date @error('due_date') is-invalid @enderror" value="{{ old('due_date', date('d-m-Y')) }}" max="{{ date('d-m-Y') }}" placeholder="dd-mm-yyyy" autocomplete="off">
                        @error('due_date')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-12 p-1 text-center">
                        <button type="submit" class="btn btn-primary px-4">Submit</button>
                        <a href="{{ route('associate_invoices') }}" class="btn btn-outline-primary px-4 ms-3">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
@include('web.partials.invoice_duplicate_confirm_script')
<script>
    $(document).ready(function(){
        // Which clients / applications each associate is linked to (via business entries).
        var associateClientMap = @json($associateClientMap);
        var associateApplicationMap = @json($associateApplicationMap);
        var businessInvoiceDataMap = @json($businessInvoiceDataMap ?? []);

        var $assoc = $("#associateSelect");
        var $client = $("#clientSelect");
        var $app = $("#applicationSelect");
        var $fees = $("#feesField");

        // Pristine copies so the lists can be rebuilt as selections change.
        var clientOptions = $client.find('option').clone();
        var appOptions = $app.find('option').clone();

        function allowedIdsFor(map){
            var id = $assoc.val();
            return (id && map[id]) ? map[id].map(String) : [];
        }

        function clientHasInvoiceableApplications(clientId) {
            if (!clientId) {
                return false;
            }

            var allowedApps = allowedIdsFor(associateApplicationMap);
            var hasApp = false;

            appOptions.each(function(){
                var $o = $(this);
                if ($o.val() === '') { return; }
                if (allowedApps.indexOf(String($o.val())) === -1) { return; }
                if (String($o.data('client-id')) !== String(clientId)) { return; }
                hasApp = true;
                return false;
            });

            return hasApp;
        }

        // Clients limited to those linked with the chosen associate and still invoiceable.
        function rebuildClients(){
            var allowed = allowedIdsFor(associateClientMap);
            var prev = $client.val();
            var matchedClients = [];
            $client.empty().append('<option value="">Select Client</option>');
            clientOptions.each(function(){
                var $o = $(this);
                if($o.val() === '') { return; }
                if(allowed.indexOf(String($o.val())) === -1){ return; }
                if(!clientHasInvoiceableApplications($o.val())){ return; }
                $client.append($o.clone());
                matchedClients.push(String($o.val()));
            });
            if (matchedClients.length === 1) {
                $client.val(matchedClients[0]);
            } else if(prev && $client.find('option[value="'+prev+'"]').length) {
                $client.val(prev);
            }
        }

        // Applications limited to the selected client and business-referral links for the associate.
        function rebuildApplications(){
            var allowedApps = allowedIdsFor(associateApplicationMap);
            var clientId = $client.val();
            var prev = $app.val();
            var seen = {};
            $app.empty().append('<option value="">Select Application</option>');

            if (!clientId) {
                syncBusinessData();
                return;
            }

            var matchedOptions = [];
            appOptions.each(function(){
                var $o = $(this);
                if($o.val() === '') { return; }
                if(allowedApps.indexOf(String($o.val())) === -1) { return; }
                if(String($o.data('client-id')) !== String(clientId)) { return; }
                if(seen[String($o.val())]) { return; }
                seen[String($o.val())] = true;
                $app.append($o.clone());
                matchedOptions.push(String($o.val()));
            });

            if (matchedOptions.length === 1) {
                $app.val(matchedOptions[0]);
            } else if (prev && $app.find('option[value="'+prev+'"]').length) {
                $app.val(prev);
            }

            syncBusinessData();
        }

        function syncBusinessData(){
            var associateId = $assoc.val();
            var applicationId = $app.val();
            var key = String(associateId) + ':' + String(applicationId);
            var data = businessInvoiceDataMap[key] || null;
            var $serviceChecks = $('input[name="services[]"]');
            var $hiddenServices = $('#businessServiceHiddenInputs');

            $hiddenServices.empty();
            $serviceChecks.prop('checked', false).prop('disabled', true);

            if (data && data.fees !== undefined && data.fees !== null && data.fees !== '') {
                $fees.val(data.fees);
            } else {
                $fees.val('');
            }

            if (data && Array.isArray(data.services) && data.services.length) {
                data.services.forEach(function(service){
                    $serviceChecks.filter('[value="'+service+'"]').prop('checked', true);
                    $hiddenServices.append(
                        $('<input>', { type: 'hidden', name: 'services[]', value: service })
                    );
                });
            }
        }

        $assoc.change(function(){ rebuildClients(); rebuildApplications(); });
        $client.change(rebuildApplications);
        $app.change(syncBusinessData);

        rebuildClients();
        rebuildApplications();

        bindInvoiceDuplicateConfirm('form.register-box', {
            type: 'associate',
            clientField: '#clientSelect',
            applicationField: '#applicationSelect'
        });
    });
</script>

@endsection()
