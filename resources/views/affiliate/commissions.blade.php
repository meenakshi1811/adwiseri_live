@extends('affiliate.layout.main')
@push('css')
<style>
    /* Commission totals in theme colours, with white figures and labels. */
    .commission-tile { border-radius: 12px; padding: 18px 12px; text-align: center; }
    .commission-tile h4, .commission-tile p { color: #fff !important; margin: 0; }
    .commission-tile h4 { font-size: 26px; font-weight: 800; margin-bottom: 4px; }
    .commission-tile--earned { background: #695EEE; }
    .commission-tile--paid { background: #15803D; }
    .commission-tile--pending { background: #B45309; }
</style>
@endpush
@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="client-btn d-flex mb-2">
                <h3 class="text-primary text-center flex-grow-1 m-0">Commissions</h3>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="client-box commission-tile commission-tile--earned">
                        <h4>$ {{ number_format((float) $totalEarned, 2) }}</h4>
                        <p style="font-weight: bolder!important;">Total Earned</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="client-box commission-tile commission-tile--paid">
                        <h4>$ {{ number_format((float) $paidAmount, 2) }}</h4>
                        <p style="font-weight: bolder!important;">Paid</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="client-box commission-tile commission-tile--pending">
                        <h4>$ {{ number_format((float) $pendingAmount, 2) }}</h4>
                        <p style="font-weight: bolder!important;">Pending</p>
                    </div>
                </div>
            </div>

            <div class="table-wrapper mt-3">
                <table class="fl-table table table-hover p-0 m-0" id="affiliateCommissionsTable">
                    <thead>
                        <tr>
                            <th class="p-1 text-center">Commission_ID</th>
                            <th class="p-1 text-center">Subscriber (Sub_ID)</th>
                            <th class="p-1 text-center">Plan_Name</th>
                            <th class="p-1 text-center">Purchase (USD)</th>
                            <th class="p-1 text-center">Commission (USD)</th>
                            <th class="p-1 text-center">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningTotal = 0; @endphp
                        @forelse($commissions as $commission)
                            @php $runningTotal += (float) $commission->amount_added; @endphp
                            <tr>
                                <td class="p-1 text-center">{{ $commission->id }}</td>
                                <td class="p-1">{{ $commission->user_name }} ({{ $commission->userid }})</td>
                                <td class="p-1">{{ $commission->user->membership ?? 'N/A' }}</td>
                                <td class="p-1">{{ $commission->total_amount }}</td>
                                <td class="p-1">{{ $commission->amount_added }}</td>
                                <td class="p-1">{{ \Carbon\Carbon::parse($commission->created_at)->format('d-m-Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-3 text-center text-muted">No commissions earned yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($commissions->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="4" class="p-1 text-end"><strong>Total Commission:</strong></td>
                                <td class="p-1"><strong>{{ number_format($runningTotal, 2) }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
