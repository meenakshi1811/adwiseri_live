@extends('affiliate.layout.main')
@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="client-btn d-flex mb-2">
                <h3 class="text-primary">Commissions</h3>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="client-box" style="background-color: #FFC107; border-radius: 10px; color: #ffffff;">
                        <h4>$ {{ number_format((float) $totalEarned, 2) }}</h4>
                        <p style="font-weight: bolder!important;">Total Earned</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="client-box" style="background-color: #4CAF50; border-radius: 10px; color: #ffffff;">
                        <h4>$ {{ number_format((float) $paidAmount, 2) }}</h4>
                        <p style="font-weight: bolder!important;">Paid</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="client-box" style="background-color: #9C27B0; border-radius: 10px; color: #ffffff;">
                        <h4>$ {{ number_format((float) $pendingAmount, 2) }}</h4>
                        <p style="font-weight: bolder!important;">Pending</p>
                    </div>
                </div>
            </div>

            <div class="table-wrapper mt-3">
                <table class="fl-table table table-hover p-0 m-0" id="affiliateCommissionsTable">
                    <thead>
                        <tr>
                            <th class="p-1 text-center">Commission ID</th>
                            <th class="p-1 text-start">Subscriber</th>
                            <th class="p-1 text-start">Plan</th>
                            <th class="p-1 text-start">Purchase (USD)</th>
                            <th class="p-1 text-start">Commission (USD)</th>
                            <th class="p-1 text-start">Date</th>
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
