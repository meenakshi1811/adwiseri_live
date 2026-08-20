<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Client Accounts Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.4;
            margin: 24px 28px;
        }

        .page-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 14px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .meta-label {
            font-weight: bold;
            width: 140px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 18px 0 8px;
        }

        .entries-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .entries-table th,
        .entries-table td {
            border: 1px solid #333;
            padding: 6px 5px;
            text-align: center;
        }

        .entries-table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .credit {
            color: #4C3BB7;
        }

        .debit {
            color: #dc3545;
        }

        .footer-note {
            margin-top: 8px;
            margin-bottom: 16px;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <h1 class="page-title">Client Accounts Report</h1>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Client (ID)</td>
            <td>: {{ $clientLabel }}</td>
        </tr>
        <tr>
            <td class="meta-label">Application (ID)</td>
            <td>: {{ $applicationLabel }}</td>
        </tr>
        <tr>
            <td class="meta-label">Generated On</td>
            <td>: {{ $generatedAt }}</td>
        </tr>
    </table>

    @foreach($accountGroups as $group)
        @if(!empty($allApplications) && !empty($group['label']))
            <div class="section-title">
                @if(!empty($group['without_application']))
                    {{ $group['label'] }}
                @else
                    Application: {{ $group['label'] }}
                @endif
            </div>
        @endif

        <table class="entries-table">
            <thead>
                <tr>
                    <th>Trans_ID</th>
                    <th>Trans_Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Prev_Balance</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Entry By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($group['accounts'] as $account)
                <tr>
                    <td>{{ $account->id }}</td>
                    <td class="{{ strcasecmp($account->trans_type, 'Credit') === 0 ? 'credit' : 'debit' }}">
                        {{ $account->trans_type }}
                    </td>
                    <td>{{ number_format((float) $account->amount, 2, '.', '') }}</td>
                    <td>{{ $account->description === 'Advance Collection' ? 'Deposit / Advance Collected' : $account->description }}</td>
                    <td>{{ number_format((float) $account->prev_balance, 2, '.', '') }}</td>
                    <td>{{ number_format((float) $account->total, 2, '.', '') }}</td>
                    <td>{{ $account->transaction_date ? $account->transaction_date->format('d-m-Y') : '—' }}</td>
                    <td>{{ $account->trans_by }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">No account entries found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($group['accounts']->isNotEmpty())
        <p class="footer-note">
            Closing balance{{ !empty($allApplications) && !empty($group['label']) ? ' (' . $group['label'] . ')' : '' }}:
            {{ number_format((float) $group['accounts']->last()->total, 2, '.', '') }}
            {{ !empty($user->currency) ? '(' . $user->currency . ')' : '' }}
        </p>
        @endif
    @endforeach
</body>
</html>
