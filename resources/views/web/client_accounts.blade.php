@extends('web.layout.main')

@section('main-section')
@php
use App\Models\UserRoles;
use App\Support\ModuleAvailability;
$client_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Clients')->first();
$canDownloadPdf = $client_roles && (
    $client_roles->read_only == 1 ||
    $client_roles->read_write_only == 1 ||
    $client_roles->write_only == 1
);
$canAddEntry = $client_roles && ($client_roles->write_only == 1 || $client_roles->read_write_only == 1);
@endphp
<style>
    .client-accounts-page-header {
        margin-top: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.35rem;
    }

    .client-accounts-page-header .client-accounts-actions {
        margin-top: -0.35rem;
    }

    .client-accounts-pdf-link.disabled {
        opacity: 0.45;
        pointer-events: none;
        cursor: not-allowed;
        text-decoration: none;
    }
</style>
<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="client-btn d-flex justify-content-between align-items-start client-accounts-page-header">
            <h3 class="text-primary text-center flex-grow-1 text-center m-0 pt-1">Client Accounts</h3>
            <div class="d-flex gap-3 align-items-center flex-wrap justify-content-end client-accounts-actions">
                @if($canDownloadPdf)
                <a href="javascript:void(0)" id="downloadClientAccountsPdf" class="m-0 client-accounts-pdf-link disabled" aria-disabled="true">Download PDF</a>
                @endif
                @if($canAddEntry)
                    @if(ModuleAvailability::hasClients($user))
                    <a href="{{ route('add_client_account') }}" class="m-0">Add Client Account Record</a>
                    @else
                    <a href="javascript:void(0)" onclick="showNoClientAlert(); return false;" class="m-0">Add Client Account Record</a>
                    @endif
                @endif
            </div>
        </div>

        @include('partials.client_module_tabs', ['activeTab' => 'accounts'])

        <div class="row mx-2 mb-3 g-2 align-items-end">
            <div class="col-md-4">
                <label for="filter_client_id" class="form-label mb-1">Client</label>
                <select id="filter_client_id" class="form-control form-select">
                    <option value="">All Clients</option>
                    @foreach($clientsWithAccounts as $client)
                        <option value="{{ $client->id }}">{{ $client->name . ' (' . $client->id . ')' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="filter_application_id" class="form-label mb-1">Application</label>
                <select id="filter_application_id" class="form-control form-select" disabled>
                    <option value="">All Applications</option>
                </select>
            </div>
        </div>

        @if(count($accounts) != 0)
        <div class="table-wrapper">
            <table class="fl-table table table-hover p-0 m-0" id="clientAccountsTable">
                <thead>
                    <tr>
                        <th class="p-1 text-center">Trans_ID</th>
                        <th class="p-1 text-center">Client_ID</th>
                        <th class="p-1 text-center">App_ID</th>
                        <th class="p-1 text-center">Trans_Type</th>
                        <th class="p-1 text-center">Amount</th>
                        <th class="p-1 text-center">Description</th>
                        <th class="p-1 text-center">Prev_Balance</th>
                        <th class="p-1 text-center">Total</th>
                        <th class="p-1 text-center">Date</th>
                        <th class="p-1 text-center">By</th>
                        <th class="p-1 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                    <tr
                        data-account-row="1"
                        data-client-id="{{ $account->client_id }}"
                        data-application-id="{{ $account->application_id ?? '' }}">
                        <td class="p-1 text-center" data-order="{{ $account->id }}">{{ $account->id }}</td>
                        <td class="p-1 text-center">
                            {{ $account->client ? $account->client->name . '(' . $account->client_id . ')' : $account->client_id }}
                        </td>
                        <td class="p-1 text-center">{{ $account->application_id ?? '—' }}</td>
                        <td class="p-1 text-center">
                            <span class="{{ strcasecmp($account->trans_type, 'Credit') === 0 ? 'text-success' : 'text-danger' }}">
                                {{ $account->trans_type }}
                            </span>
                        </td>
                        <td class="p-1 text-center">{{ number_format((float) $account->amount, 2, '.', '') }}</td>
                        <td class="p-1 text-center">{{ $account->description === 'Advance Collection' ? 'Deposit / Advance Collected' : $account->description }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $account->prev_balance, 2, '.', '') }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $account->total, 2, '.', '') }}</td>
                        <td class="p-1 text-center" data-order="{{ $account->transaction_date ? $account->transaction_date->format('Y-m-d') : '' }}">
                            {{ $account->transaction_date ? $account->transaction_date->format('d-m-Y') : '—' }}
                        </td>
                        <td class="p-1 text-center">{{ $account->trans_by }}</td>
                        <td class="p-1 text-center">
                            @if($client_roles && ($client_roles->read_only == 1 || $client_roles->read_write_only == 1))
                            <a style="background:none; border:none;" href="{{ route('view_client_account', $account->id) }}" class="m-0 p-0" title="View">
                                <i class="fa-solid fa-eye p-1 text-info" style="font-size:14px;"></i>
                            </a>
                            @endif
                            @if($client_roles && ($client_roles->write_only == 1 || $client_roles->read_write_only == 1))
                            <a style="background:none; border:none;" href="{{ route('edit_client_account', $account->id) }}" class="m-0 p-0" title="Edit">
                                <i class="fa-solid fa-pen-to-square p-1 text-primary" style="font-size:14px;"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-secondary px-3">No client account records to show.</p>
        @endif
    </div>
</div>

@if(session('client_account_success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('client_account_success'))
            });
        }
    });
</script>
@endif

@if(session('client_account_error'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: @json(session('client_account_error'))
            });
        }
    });
</script>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var applicationsByClient = @json($applicationsByClient);
    var clientSelect = document.getElementById('filter_client_id');
    var applicationSelect = document.getElementById('filter_application_id');
    var downloadBtn = document.getElementById('downloadClientAccountsPdf');
    var accountsTable = null;

    window.clientAccountsFilterState = {
        clientId: '',
        applicationId: ''
    };

    if (!document.getElementById('clientAccountsTable')) {
        return;
    }

    if (!window.clientAccountsSearchInstalled) {
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'clientAccountsTable') {
                return true;
            }

            var api = new $.fn.dataTable.Api(settings);
            var row = api.row(dataIndex).node();
            if (!row) {
                return true;
            }

            var clientId = window.clientAccountsFilterState.clientId;
            var applicationId = window.clientAccountsFilterState.applicationId;

            if (!clientId) {
                return true;
            }

            if (String(row.getAttribute('data-client-id')) !== String(clientId)) {
                return false;
            }

            if (applicationId && String(row.getAttribute('data-application-id')) !== String(applicationId)) {
                return false;
            }

            return true;
        });
        window.clientAccountsSearchInstalled = true;
    }

    accountsTable = $('#clientAccountsTable').DataTable({
        order: [[8, 'desc'], [0, 'desc']]
    });

    function populateApplications(clientId) {
        if (!applicationSelect) {
            return;
        }

        applicationSelect.innerHTML = '<option value="">All Applications</option>';
        applicationSelect.disabled = true;

        if (!clientId) {
            return;
        }

        var apps = applicationsByClient[clientId] || applicationsByClient[String(clientId)] || [];
        apps.forEach(function (app) {
            var option = document.createElement('option');
            option.value = app.id;
            option.textContent = app.name + ' (' + app.id + ')';
            applicationSelect.appendChild(option);
        });

        applicationSelect.disabled = false;
    }

    function countMatchingEntries(clientId, applicationId) {
        if (!clientId || !accountsTable) {
            return 0;
        }

        var count = 0;
        accountsTable.rows().every(function () {
            var row = this.node();
            if (!row) {
                return;
            }

            if (String(row.getAttribute('data-client-id')) !== String(clientId)) {
                return;
            }

            if (applicationId && String(row.getAttribute('data-application-id')) !== String(applicationId)) {
                return;
            }

            count++;
        });

        return count;
    }

    function updateDownloadButton() {
        if (!downloadBtn) {
            return;
        }

        var clientId = clientSelect ? clientSelect.value : '';
        var applicationId = applicationSelect ? applicationSelect.value : '';
        var hasEntries = countMatchingEntries(clientId, applicationId) > 0;

        if (hasEntries) {
            downloadBtn.classList.remove('disabled');
            downloadBtn.setAttribute('aria-disabled', 'false');
        } else {
            downloadBtn.classList.add('disabled');
            downloadBtn.setAttribute('aria-disabled', 'true');
        }
    }

    function applyFilters() {
        window.clientAccountsFilterState.clientId = clientSelect ? clientSelect.value : '';
        window.clientAccountsFilterState.applicationId = applicationSelect ? applicationSelect.value : '';

        if (accountsTable) {
            accountsTable.draw();
        }

        updateDownloadButton();
    }

    if (clientSelect) {
        clientSelect.addEventListener('change', function () {
            populateApplications(this.value);
            if (applicationSelect) {
                applicationSelect.value = '';
            }
            applyFilters();
        });
    }

    if (applicationSelect) {
        applicationSelect.addEventListener('change', applyFilters);
    }

    if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {
            if (downloadBtn.classList.contains('disabled')) {
                return;
            }

            var clientId = clientSelect ? clientSelect.value : '';
            var url = '{{ route('client_accounts_pdf') }}?client_id=' + encodeURIComponent(clientId);
            var applicationId = applicationSelect ? applicationSelect.value : '';

            if (applicationId) {
                url += '&application_id=' + encodeURIComponent(applicationId);
            }

            window.location.href = url;
        });
    }

    applyFilters();
});
</script>
@endpush
