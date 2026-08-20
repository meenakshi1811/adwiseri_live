@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <h3 class="text-primary text-center px-2">Edit Business Entry</h3>
        <div class="col">
            <form class="register-box login-box" method="POST" action="{{ route('update_associate_business') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="id" value="{{ $business->id }}" />
                <div class="row">
                    <div class="col-md-4 p-1"><label>Select Associate<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="associate_id" id="associateSelect" class="form-control form-select @error('associate_id') is-invalid @enderror" required>
                            <option value="">Select Associate</option>
                            @foreach($associates as $a)
                            <option value="{{ $a->id }}" {{ old('associate_id', $business->associate_id) == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ $a->id }})</option>
                            @endforeach
                        </select>
                        @error('associate_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Select Client<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="client_id" id="clientSelect" class="form-control form-select @error('client_id') is-invalid @enderror" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ old('client_id', $business->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->id }})</option>
                            @endforeach
                        </select>
                        @error('client_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Link Application<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="application_id" id="applicationSelect" class="form-control form-select @error('application_id') is-invalid @enderror" required>
                            <option value="">Select Application</option>
                            @foreach($applications as $app)
                            <option value="{{ $app->id }}" data-client-id="{{ $app->client_id }}" data-status="{{ $app->application_status }}" {{ old('application_id', $business->application_id) == $app->id ? 'selected' : '' }}>{{ $app->application_name }} ({{ $app->application_id }})@if($app->client_name) — {{ $app->client_name }}@endif</option>
                            @endforeach
                        </select>
                        @error('application_id')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Select Services<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        @php
                            $selectedServices = old('services', array_filter(array_map('trim', explode(',', (string) ($business->services ?: $business->service_provided)))));
                        @endphp
                        @include('web.associates._service_checkboxes', ['services' => $services, 'selectedServices' => $selectedServices])
                    </div>

                    <div class="col-md-4 p-1"><label>Fees<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="fees" type="number" step="0.01" min="0" class="form-control @error('fees') is-invalid @enderror" value="{{ old('fees', $business->fees) }}" required placeholder="Fees">
                        @error('fees')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Application Status<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input type="text" id="applicationStatusDisplay" class="form-control @error('application_status') is-invalid @enderror" readonly placeholder="Select an application" value="{{ old('application_status', $business->application_status) }}">
                        <input type="hidden" name="application_status" id="applicationStatusInput" value="{{ old('application_status', $business->application_status) }}">
                        @error('application_status')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-12 p-1 text-center">
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                        <a href="{{ route('associate_business') }}" class="btn btn-outline-primary px-4 ms-3">Back</a>
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
        var assignedApplicationIds = @json($assignedApplicationIds ?? []);
        var assignedApplicationSet = {};

        assignedApplicationIds.forEach(function (applicationId) {
            assignedApplicationSet[String(applicationId)] = true;
        });

        var $assoc = $("#associateSelect");
        var $client = $("#clientSelect");
        var $app = $("#applicationSelect");
        var $statusDisplay = $("#applicationStatusDisplay");
        var $statusInput = $("#applicationStatusInput");
        var appOptions = $app.find('option').clone();
        var clientOptions = $client.find('option').clone();

        function isApplicationAssigned(applicationId) {
            return !!assignedApplicationSet[String(applicationId)];
        }

        function clientHasAssignableApplications(clientId) {
            if (!clientId) {
                return false;
            }

            var hasApp = false;
            appOptions.each(function(){
                var $opt = $(this);
                if ($opt.val() === '') { return; }
                if (String($opt.data('client-id')) !== String(clientId)) { return; }
                if (isApplicationAssigned($opt.val())) { return; }
                hasApp = true;
                return false;
            });

            return hasApp;
        }

        function rebuildClients() {
            var selected = $client.val();
            $client.empty().append('<option value="">Select Client</option>');
            clientOptions.each(function(){
                var $opt = $(this);
                if ($opt.val() === '') { return; }
                if (clientHasAssignableApplications($opt.val())) {
                    $client.append($opt.clone());
                }
            });
            if (selected && $client.find('option[value="'+selected+'"]').length) {
                $client.val(selected);
            }
        }

        function filterApplications(){
            var clientId = $client.val();
            var selected = $app.val();
            $app.empty().append('<option value="">Select Application</option>');

            if (!clientId) {
                syncApplicationStatus();
                return;
            }

            appOptions.each(function(){
                var $opt = $(this);
                if($opt.val() === '') { return; }
                var appClient = String($opt.data('client-id'));
                if(isApplicationAssigned($opt.val())) { return; }
                if(appClient !== String(clientId)){
                    return;
                }
                $app.append($opt.clone());
            });
            if(selected && $app.find('option[value="'+selected+'"]').length){
                $app.val(selected);
            }
        }

        function syncApplicationStatus(){
            var status = $app.find(':selected').data('status') || '';
            status = String(status).trim();
            $statusDisplay.val(status);
            $statusInput.val(status);
        }

        $assoc.change(function(){ rebuildClients(); filterApplications(); syncApplicationStatus(); });
        $client.change(function(){ filterApplications(); syncApplicationStatus(); });
        $app.change(syncApplicationStatus);
        rebuildClients();
        filterApplications();
        syncApplicationStatus();
    });
</script>

@endsection()
