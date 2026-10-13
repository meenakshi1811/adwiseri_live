@extends('web.layout.main')

@section('main-section')
@php
use App\Models\UserRoles;
$client_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Clients')->first();
@endphp
<style>
    .client-account-detail-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    .client-account-detail-table th,
    .client-account-detail-table td {
        vertical-align: middle;
    }

    .client-account-detail-table thead th {
        text-align: center;
    }

    .client-account-detail-table thead th.amount-col {
        text-align: right;
    }

    .client-account-detail-table .amount-col {
        width: 28%;
        white-space: nowrap;
    }

    .client-account-detail-table .summary-label {
        text-align: right;
    }
</style>
<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="client-btn d-flex justify-content-between mb-4">
            <h3 class="text-primary px-3">Client Account Entry</h3>
            <div class="d-flex gap-2">
                @if($client_roles && ($client_roles->write_only == 1 || $client_roles->read_write_only == 1))
                <a href="{{ route('edit_client_account', $account->id) }}" class="m-0">Edit</a>
                <a href="javascript:void(0)" onclick="deleteAccount({{ $account->id }})" class="m-0 text-danger">Delete</a>
                @endif
            </div>
        </div>

        <div class="p-3">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="m-1"><strong>Trans_ID:</strong> {{ $account->id }}</p>
                    <p class="m-1"><strong>Client:</strong> {{ $account->client ? $account->client->name . ' (' . $account->client_id . ')' : $account->client_id }}</p>
                    <p class="m-1"><strong>Application:</strong>
                        @if($account->application)
                            {{ $account->application->application_name }} ({{ $account->application_id }})
                        @else
                            —
                        @endif
                    </p>
                    <p class="m-1"><strong>Trans_Type:</strong>
                        <span class="{{ strcasecmp($account->trans_type, 'Credit') === 0 ? 'text-success' : 'text-danger' }}">
                            {{ $account->trans_type }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="m-1"><strong>Date:</strong> {{ $account->transaction_date ? $account->transaction_date->format('d-m-Y') : '—' }}</p>
                    <p class="m-1"><strong>By:</strong> {{ $account->trans_by }}</p>
                    <p class="m-1"><strong>Created:</strong> {{ $account->created_at ? $account->created_at->format('d-m-Y H:i') : '—' }}</p>
                </div>
            </div>

            <div class="fl-table-wrap mt-3">
                <table class="fl-table table client-account-detail-table mb-0" style="border: 1px solid grey;">
                    <colgroup>
                        <col>
                        <col class="amount-col">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Description</th>
                            <th scope="col" class="amount-col">Amount ({{ $user->currency ?? '' }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{ $account->description }}
                                @if($account->payment_mode)
                                    <div class="text-muted small mt-1">Mode Of Payment: {{ $account->payment_mode }}</div>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format((float) $account->amount, 2, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label"><strong>Previous Balance</strong></td>
                            <td class="text-end">{{ number_format((float) $account->prev_balance, 2, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label"><strong>Running Total</strong></td>
                            <td class="text-end"><strong>{{ number_format((float) $account->total, 2, '.', '') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="{{ route('client_accounts') }}" class="btn btn-secondary">Back to Accounts</a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteAccount(id) {
    var localtime = new Date();
    if (confirm('Are you sure you want to delete this account entry?')) {
        window.location.href = '{{ url('delete_client_account') }}/' + id + '/' + localtime.toString();
    }
}
</script>
@endsection
