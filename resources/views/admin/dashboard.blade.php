@extends('admin.layout.main')

@section('main-section')
    <div class="col-lg-7 dash-main-col">
        @if(count($headerCards ?? []) > 0)
        <div class="data-box">
            <div class="row m-0" style="width:100%;">
                @foreach($headerCards as $card)
                <div class="col-6 col-md-3 m-0 p-1">
                    <div class="client-box dash-stat-card col-12 m-0">
                        <div class="dash-stat-head">
                            <i class="{{ $card['icon'] }} p-1 d-flex text-center align-items-center justify-content-center"></i>
                            <p class="dash-stat-label">{{ $card['label'] }}</p>
                        </div>
                        <h4 class="dash-stat-count">{!! $card['value'] !!}</h4>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(count($charts ?? []) > 0)
        <div class="row m-0 p-0 dash-charts-row dash-charts-row--count-{{ (int) ($dashboardChartCount ?? count($charts)) }}">
            @foreach($charts as $chart)
            <div class="col-md-6">
                <div class="dash-chart-panel">
                    <h5 class="dash-chart-title text-primary text-center fw-bold {{ in_array($chart['type'], ['pie', 'doughnut', 'gauge'], true) ? 'dash-chart-title-radial' : '' }}">{{ $chart['title'] }}</h5>
                    <div class="dash-chart-canvas">
                        @if($chart['empty'])
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted" style="font-size:12px;">
                                Not enough data
                            </div>
                        @else
                            <canvas id="{{ $chart['id'] }}"></canvas>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(count($headerCards ?? []) === 0 && count($charts ?? []) === 0)
        <div class="data-box">
            <div class="p-4 text-center text-muted">
                <p class="mb-2">Your dashboard has no headers or charts selected.</p>
                <a href="{{ route('settings') }}#admin-dashboard-settings" class="btn btn-sm btn-primary">
                    Choose what to show
                </a>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-3 activity-box">
        <div class="activebox">
            <h4>Activities</h4>
        </div>
        <div class="doc-files mb-3">
            @foreach(($activities ?? []) as $activity)
                <div class="p-docbox d-flex align-items-start">
                    <span class="activity-fa-icon" aria-hidden="true">
                        <i class="{{ activity_fa_icon($activity) }}"></i>
                    </span>
                    <p style="font-weight: bolder!important;">{{ activity_panel_label($activity) }} <br>
                        <span>{{ date('D M d Y H:i:s', strtotime($activity->created_at)) }}</span></p>
                </div>
            @endforeach
            <a href="{{ route('activity_log') }}">Read More...</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="{{ asset('web_assets/js/adwiseri-chart-types.js') }}?v=20260814b"></script>
    <script src="{{ asset('web_assets/js/analytics-chart-overlap.js') }}?v=20260814b"></script>
    <script>
        window.__DASHBOARD_CHARTS__ = @json($charts ?? []);
    </script>
    <script src="{{ asset('web_assets/js/dashboard-charts.js') }}?v=20260814b"></script>
@endpush
