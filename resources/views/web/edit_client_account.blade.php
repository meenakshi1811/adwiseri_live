@extends('web.layout.main')

@section('main-section')
<style>
    .register-box.login-box .account-form-actions a.btn {
        padding: var(--adwiseri-btn-padding-y) var(--adwiseri-btn-padding-x) !important;
        text-decoration: none !important;
    }
</style>
<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="client-btn d-flex mb-2">
            <h3 class="text-primary text-center flex-grow-1 m-0">Edit Client Account Record</h3>
        </div>

        <div class="col">
            <form id="client_account_form" class="register-box login-box" method="POST" action="{{ route('update_client_account', $account->id) }}">
                @csrf
                <input type="hidden" name="local_time" class="localtime" />

                <div class="row">
                    <div class="col-md-4 p-1">
                        <label>Client <span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                        <select name="client_id" id="client_id" required class="form-control form-select @error('client_id') is-invalid @enderror">
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ (int) old('client_id', $account->client_id) === (int) $client->id ? 'selected' : '' }}>
                                    {{ $client->name . '(' . $client->id . ')' }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-4 p-1">
                        <label>Application</label>
                    </div>
                    <div class="col-md-8 p-1">
                        <select name="application_id" id="application_id" class="form-control form-select @error('application_id') is-invalid @enderror">
                            <option value="">Select Application</option>
                            @foreach($applications as $application)
                                <option value="{{ $application->id }}" {{ (int) old('application_id', $account->application_id) === (int) $application->id ? 'selected' : '' }}>
                                    {{ $application->application_name . ' (' . $application->id . ')' }}
                                </option>
                            @endforeach
                        </select>
                        @error('application_id')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-4 p-1">
                        <label>Transaction Type <span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trans_type" id="trans_credit" value="Credit"
                                {{ old('trans_type', $account->trans_type) === 'Credit' ? 'checked' : '' }}>
                            <label class="form-check-label" for="trans_credit">Credit</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trans_type" id="trans_debit" value="Debit"
                                {{ old('trans_type', $account->trans_type) === 'Debit' ? 'checked' : '' }}>
                            <label class="form-check-label" for="trans_debit">Debit</label>
                        </div>
                        @error('trans_type')
                            <span class="text-danger d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-4 p-1">
                        <label>Amount <span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                        <input name="amount" type="number" min="0.01" step="0.01" required
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', $account->amount) }}" placeholder="Amount">
                        @error('amount')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-4 p-1">
                        <label>Description <span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                        <select name="description" id="description" required class="form-control form-select @error('description') is-invalid @enderror">
                            <option value="">Select Description</option>
                        </select>
                        <input name="description_other" id="description_other" type="text" maxlength="255"
                            class="form-control mt-2 @error('description_other') is-invalid @enderror"
                            value="{{ old('description_other', $descriptionOther) }}" placeholder="Specify other description"
                            style="display: {{ old('description', $selectedDescription) === 'Other' ? 'block' : 'none' }};">
                        @error('description')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-4 p-1">
                        <label>Transaction Date <span class="text-danger" style="font-size: 18px;">*</span></label>
                    </div>
                    <div class="col-md-8 p-1">
                        <input name="transaction_date" type="text" required
                            class="form-control datepicker @error('transaction_date') is-invalid @enderror"
                            value="{{ old('transaction_date', $account->transaction_date ? $account->transaction_date->format('d-m-Y') : date('d-m-Y')) }}"
                            placeholder="DD-MM-YYYY">
                        @error('transaction_date')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="col-md-4 p-1"></div>
                    <div class="col-md-8 p-1 text-left account-form-actions d-flex justify-content-left gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary">Update Entry</button>
                        <a href="{{ route('client_accounts') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var localtimeInput = document.querySelector('.localtime');
    if (localtimeInput) {
        localtimeInput.value = new Date().toString();
    }

    var creditDescriptionTypes = @json($creditDescriptionTypes);
    var debitDescriptionTypes = @json($debitDescriptionTypes);
    var initialDescription = @json(old('description', $selectedDescription));

    function getTransType() {
        var checked = document.querySelector('input[name="trans_type"]:checked');
        return checked ? checked.value : 'Credit';
    }

    function toggleDescriptionOther() {
        var descriptionSelect = document.getElementById('description');
        var descriptionOther = document.getElementById('description_other');
        if (descriptionOther) {
            descriptionOther.style.display = descriptionSelect.value === 'Other' ? 'block' : 'none';
        }
    }

    function updateDescriptionOptions(resetSelection) {
        var descriptionSelect = document.getElementById('description');
        var types = getTransType() === 'Credit' ? creditDescriptionTypes : debitDescriptionTypes;
        var selectedValue = resetSelection ? '' : (descriptionSelect.value || initialDescription);

        descriptionSelect.innerHTML = '<option value="">Select Description</option>';
        types.forEach(function (type) {
            var option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            if (selectedValue === type) {
                option.selected = true;
            }
            descriptionSelect.appendChild(option);
        });

        if (selectedValue && types.indexOf(selectedValue) === -1) {
            descriptionSelect.value = '';
        }

        initialDescription = '';
        toggleDescriptionOther();
    }

    function loadApplications(clientId, selectedId) {
        var appSelect = document.getElementById('application_id');
        var currentSelected = selectedId || appSelect.value;
        appSelect.innerHTML = '<option value="">Select Application</option>';
        if (!clientId) return;

        fetch('{{ url('/get-applications-by-client') }}/' + clientId)
            .then(function (response) { return response.json(); })
            .then(function (apps) {
                apps.forEach(function (app) {
                    var option = document.createElement('option');
                    option.value = app.id;
                    option.textContent = app.application_name + ' (' + app.id + ')';
                    if (currentSelected && String(currentSelected) === String(app.id)) {
                        option.selected = true;
                    }
                    appSelect.appendChild(option);
                });
            });
    }

    document.getElementById('client_id').addEventListener('change', function () {
        loadApplications(this.value, null);
    });

    document.querySelectorAll('input[name="trans_type"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            updateDescriptionOptions(true);
        });
    });

    document.getElementById('description').addEventListener('change', toggleDescriptionOther);

    updateDescriptionOptions(false);
});
</script>
@endsection
