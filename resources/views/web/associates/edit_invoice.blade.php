@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <h3 class="text-primary text-center px-2">Edit Invoice <small class="text-secondary">({{ $invoice->id }})</small></h3>
        <div class="col">
            <form class="register-box login-box" method="POST" action="{{ route('update_associate_invoice') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="id" value="{{ $invoice->id }}" />
                <div class="row">
                    <div class="col-md-4 p-1"><label>Select Associate<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="associate_id" id="associateSelect" class="form-control form-select @error('associate_id') is-invalid @enderror" required>
                            <option value="">Select Associate</option>
                            @foreach($associates as $a)
                            <option value="{{ $a->id }}" {{ old('associate_id', $invoice->associate_id) == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ $a->id }})</option>
                            @endforeach
                        </select>
                        @error('associate_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Select Client<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="client_id" id="clientSelect" class="form-control form-select @error('client_id') is-invalid @enderror" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ old('client_id', $invoice->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->id }})</option>
                            @endforeach
                        </select>
                        @error('client_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Link Application<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="application_id" id="applicationSelect" class="form-control form-select @error('application_id') is-invalid @enderror" required>
                            <option value="">Select Application</option>
                            @foreach($applications as $app)
                            <option value="{{ $app->id }}" data-client-id="{{ $app->client_id }}" {{ old('application_id', $invoice->application_id) == $app->id ? 'selected' : '' }}>{{ $app->application_name }} ({{ $app->application_id }})@if($app->client_name) — {{ $app->client_name }}@endif</option>
                            @endforeach
                        </select>
                        @error('application_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Select Services<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        @php $selectedInvoiceServices = old('services', array_filter(array_map('trim', explode(',', (string)($invoice->services ?: $invoice->service_provided))))); @endphp
                        @include('web.associates._service_checkboxes', ['services' => $services, 'selectedServices' => $selectedInvoiceServices])
                    </div>

                    <div class="col-md-4 p-1"><label>Fees<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="fees" type="number" step="0.01" min="0" class="form-control @error('fees') is-invalid @enderror" value="{{ old('fees', $invoice->fees) }}" required placeholder="Fees">
                        @error('fees')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Status<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="status" class="form-control form-select @error('status') is-invalid @enderror" required>
                            @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $invoice->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Due Date</label></div>
                    <div class="col-md-8 p-1">
                        <input name="due_date" type="text" class="form-control date @error('due_date') is-invalid @enderror" value="{{ old('due_date', $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') : '') }}" max="{{ date('d-m-Y') }}" placeholder="dd-mm-yyyy" autocomplete="off">
                        @error('due_date')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-8 offset-md-4 p-1 text-muted" style="font-size:12px;">
                        Paid so far: {{ number_format((float) $invoice->paid, 2) }} &nbsp;|&nbsp; Outstanding: {{ number_format($invoice->outstanding, 2) }}
                    </div>

                    <div class="col-md-12 p-1 text-center">
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                        <a href="{{ route('associate_invoices') }}" class="btn btn-outline-primary px-4 ms-3">Back</a>
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
        var associateClientMap = @json($associateClientMap ?? []);
        var associateApplicationMap = @json($associateApplicationMap ?? []);
        var invoicedApplicationCombinations = @json($invoicedApplicationCombinations ?? []);
        var invoicedApplicationKeys = {};

        invoicedApplicationCombinations.forEach(function (row) {
            invoicedApplicationKeys[row.client_id + ':' + row.application_id] = true;
        });

        function isInvoicedClientApplication(clientId, applicationId) {
            if (!clientId || !applicationId) {
                return false;
            }

            return !!invoicedApplicationKeys[String(clientId) + ':' + String(applicationId)];
        }

        var $assoc = $("#associateSelect");
        var $client = $("#clientSelect");
        var $app = $("#applicationSelect");
        var clientOptions = $client.find('option').clone();
        var appOptions = $app.find('option').clone();

        function allowedIdsFor(map){
            var id = $assoc.val();
            return (id && map[id]) ? map[id].map(String) : [];
        }

        function rebuildClients(){
            var allowed = allowedIdsFor(associateClientMap);
            var prev = $client.val();
            $client.empty().append('<option value="">Select Client</option>');
            clientOptions.each(function(){
                var $o = $(this);
                if($o.val() === '') { return; }
                if(allowed.indexOf(String($o.val())) === -1){ return; }
                $client.append($o.clone());
            });
            if(prev && $client.find('option[value="'+prev+'"]').length){ $client.val(prev); }
        }

        function rebuildApplications(){
            var allowedApps = allowedIdsFor(associateApplicationMap);
            var clientId = $client.val();
            var prev = $app.val();
            var seen = {};
            $app.empty().append('<option value="">Select Application</option>');

            if (!clientId) {
                return;
            }

            appOptions.each(function(){
                var $o = $(this);
                if($o.val() === '') { return; }
                if(allowedApps.indexOf(String($o.val())) === -1) { return; }
                if(String($o.data('client-id')) !== String(clientId)) { return; }
                if(isInvoicedClientApplication($o.data('client-id'), $o.val())) { return; }
                if(seen[String($o.val())]) { return; }
                seen[String($o.val())] = true;
                $app.append($o.clone());
            });
            if(prev && $app.find('option[value="'+prev+'"]').length){ $app.val(prev); }
        }

        $assoc.change(function(){ rebuildClients(); rebuildApplications(); });
        $client.change(rebuildApplications);
        $app.change(function(){
            var clientId = $(this).find(':selected').data('client-id');
            if(clientId){ $client.val(String(clientId)); }
        });

        rebuildClients();
        rebuildApplications();
    });
</script>

@endsection()
