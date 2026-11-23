@extends('affiliate.layout.main')

@push('css')
<style>
    /* Affiliate Dashboard: headline totals in theme colours, then four charts of referred subscribers. */
    .aff-dash-tiles { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .aff-dash-tile { border-radius: 12px; padding: 16px; color: #fff; display: flex; align-items: center; gap: 14px; min-width: 0; }
    .aff-dash-tile .aff-dash-icon { flex: 0 0 44px; width: 44px; height: 44px; border-radius: 50%; background: rgba(255, 255, 255, .2); display: grid; place-items: center; font-size: 18px; }
    .aff-dash-tile h4, .aff-dash-tile p { color: #fff !important; margin: 0; }
    .aff-dash-tile h4 { font-size: 24px; font-weight: 800; line-height: 1.1; }
    .aff-dash-tile p { font-size: 13px; font-weight: 700; opacity: .92; }
    .aff-dash-tile--subscribers { background: #4C3BB7; }
    .aff-dash-tile--referrals { background: #695EEE; }
    .aff-dash-tile--commission { background: #15803D; }
    .aff-dash-tile--wallet { background: #B45309; }
    .aff-dash-charts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .aff-dash-chart { background: #fff; border: 1px solid var(--ds-border, #E2E6F0); border-radius: 12px; padding: 12px 12px 4px; min-width: 0; }
    .aff-dash-chart h5 { margin: 0 0 4px; text-align: center; font-size: 14px !important; font-weight: 700; color: var(--ds-primary-700, #4C3BB7) !important; }
    .aff-dash-chart .aff-dash-canvas { width: 100%; height: 280px; }
    /* The chart library's credits link would otherwise pick up the purple link-button style. */
    .aff-dash-chart .anychart-credits { display: none !important; }
    @media (max-width: 1199.98px) { .aff-dash-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767.98px) { .aff-dash-charts { grid-template-columns: 1fr; } }
    @media (max-width: 479.98px) { .aff-dash-tiles { grid-template-columns: 1fr; } }
</style>
@endpush

@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="client-btn d-flex mb-3">
                <h3 class="text-primary text-center flex-grow-1 m-0">Dashboard</h3>
            </div>

            <div class="aff-dash-tiles">
                <div class="aff-dash-tile aff-dash-tile--subscribers">
                    <span class="aff-dash-icon"><i class="fa-solid fa-users"></i></span>
                    <div><h4>{{ number_format($totalSubscribers) }}</h4><p>{{ $totalSubscribers === 1 ? 'Subscriber' : 'Subscribers' }}</p></div>
                </div>
                <div class="aff-dash-tile aff-dash-tile--referrals">
                    <span class="aff-dash-icon"><i class="fa-solid fa-handshake"></i></span>
                    <div><h4>{{ number_format($totalReferrals) }}</h4><p>{{ $totalReferrals === 1 ? 'Referral' : 'Referrals' }}</p></div>
                </div>
                <div class="aff-dash-tile aff-dash-tile--commission">
                    <span class="aff-dash-icon"><i class="fa-solid fa-coins"></i></span>
                    <div><h4>$ {{ number_format($totalCommission, 2) }}</h4><p>Total Commission</p></div>
                </div>
                <div class="aff-dash-tile aff-dash-tile--wallet">
                    <span class="aff-dash-icon"><i class="fa-solid fa-wallet"></i></span>
                    <div><h4>$ {{ number_format($wallet, 2) }}</h4><p>Wallet Balance</p></div>
                </div>
            </div>

            <div class="aff-dash-charts">
                @foreach($charts as $chart)
                    <section class="aff-dash-chart" aria-label="{{ $chart['title'] }}">
                        <h5>{{ $chart['title'] }}</h5>
                        <div id="{{ $chart['id'] }}" class="aff-dash-canvas"></div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('other-scripts')
<script>
    (function () {
        var charts = @json($charts);
        var palette = ['#695EEE', '#4C3BB7', '#D946EF', '#15803D', '#B45309', '#0891B2', '#DB2777', '#65A30D', '#7C3AED', '#EA580C'];

        function drawCharts() {
            if (typeof anychart === 'undefined') {
                return;
            }
            charts.forEach(function (def) {
                var rows = Object.keys(def.data || {}).map(function (label, i) {
                    return { x: label, value: def.data[label], fill: palette[i % palette.length] };
                });
                var chart = anychart.pie();
                if (!rows.length) {
                    chart.noData().label().enabled(true).text('No subscribers yet').fontSize(14);
                } else {
                    chart.data(rows);
                    chart.innerRadius('45%');
                    chart.labels().position('outside').format('{%Value}');
                    chart.connectorStroke({ color: '#C3C9DC' });
                    chart.legend().enabled(true).position('bottom').itemsLayout('horizontal-expandable');
                }
                chart.background().fill('#ffffff');
                chart.credits().enabled(false);
                chart.container(def.id);
                chart.draw();
            });
        }

        if (typeof anychart !== 'undefined' && anychart.onDocumentReady) {
            anychart.onDocumentReady(drawCharts);
        } else {
            document.addEventListener('DOMContentLoaded', drawCharts);
        }
    })();
</script>
@endpush
