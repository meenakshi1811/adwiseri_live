{{-- Reusable Journey / Activity Log panel --}}
@php
    $panelId = $panelId ?? 'journeyLogPanel';
    $entityFilterId = $entityFilterId ?? 'entityFilter';
    $durationFilterId = $durationFilterId ?? 'durationFilter';
    $tableId = $tableId ?? 'journeyLogTable';
    $chartId = $chartId ?? 'journeyLogChart';
    $dataUrl = $dataUrl ?? '';
    $entityParam = $entityParam ?? 'subscriber_id';
    $entityLabel = $entityLabel ?? 'Select Subscriber';
    $entities = $entities ?? collect();
    $panelTitle = $panelTitle ?? 'Activity Log';
@endphp

<style>
    .journey-log-panel .filter-row {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }
    .journey-log-panel .stat-card {
        background: #fff;
        border: 1px solid #e8ebf3;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
    }
    .journey-log-panel .stat-card .stat-value {
        color: #695EEE;
        font-size: 1.75rem;
        font-weight: 700;
    }
    .journey-log-panel .stat-card .stat-label {
        color: #6b7280;
        font-size: .85rem;
    }
    .journey-log-panel .chart-wrap {
        background: #fff;
        border: 1px solid #e8ebf3;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        max-height: 320px;
    }
    .journey-log-panel .event-badge {
        display: inline-block;
        padding: .2rem .55rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .journey-log-panel .event-registration { background: #dcfce7; color: #166534; }
    .journey-log-panel .event-upgrade { background: #dbeafe; color: #1e40af; }
    .journey-log-panel .event-renewal { background: #e0e7ff; color: #3730a3; }
    .journey-log-panel .event-termination { background: #fee2e2; color: #991b1b; }
    .journey-log-panel .event-status_change { background: #fef3c7; color: #92400e; }
    .journey-log-panel .event-page_visit { background: #f3e8ff; color: #6b21a8; }
    .journey-log-panel .event-action { background: #cffafe; color: #155e75; }
    .journey-log-panel .event-operation { background: #f3f4f6; color: #374151; }
    .module-tabs .nav-link.active {
        background-color: #695EEE !important;
        color: #fff !important;
        border-color: #695EEE !important;
    }
    .module-tabs .nav-link {
        color: #695EEE;
        font-weight: 600;
    }
</style>

<div class="journey-log-panel" id="{{ $panelId }}">
    <div class="filter-row row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label fw-bold mb-1" for="{{ $entityFilterId }}">{{ $entityLabel }}</label>
            <select id="{{ $entityFilterId }}" class="form-select">
                <option value="">All</option>
                @foreach($entities as $entity)
                    <option value="{{ $entity->id }}">{{ $entity->name }} (ID: {{ $entity->id }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold mb-1" for="{{ $durationFilterId }}">Duration</label>
            <select id="{{ $durationFilterId }}" class="form-select">
                <option value="today">Today</option>
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_quarter">Last Quarter</option>
                <option value="last_year">Last Year</option>
                <option value="since_inception" selected>Since Inception</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn w-100 text-white" style="background:#695EEE;" id="{{ $panelId }}ApplyBtn">
                Apply Filters
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-value" id="{{ $panelId }}TotalCount">0</div>
                <div class="stat-label">Total Events</div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="chart-wrap">
                <canvas id="{{ $chartId }}" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="fl-table table table-hover p-0 m-0" id="{{ $tableId }}" width="100%">
            <thead>
                <tr>
                    <th class="text-center">Sr No.</th>
                    <th class="text-center">Category</th>
                    <th class="text-center">Event</th>
                    <th class="text-center">User</th>
                    <th class="text-center">Detail</th>
                    <th class="text-center">Page / Method</th>
                    <th class="text-center">IP</th>
                    <th class="text-center">Date & Time</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
(function () {
    var panelId = @json($panelId);
    var tableId = @json($tableId);
    var chartId = @json($chartId);
    var dataUrl = @json($dataUrl);
    var entityParam = @json($entityParam);
    var entityFilterId = @json($entityFilterId);
    var durationFilterId = @json($durationFilterId);
    var journeyChart = null;
    var journeyTable = null;
    var initialized = false;

    function categoryBadge(category) {
        var safe = (category || 'operation').replace(/[^a-z0-9_]/gi, '_');
        var label = (category || 'operation').replace(/_/g, ' ');
        return '<span class="event-badge event-' + safe + '">' + label + '</span>';
    }

    function renderChart(labels, values) {
        var ctx = document.getElementById(chartId);
        if (!ctx) return;

        if (journeyChart) {
            journeyChart.destroy();
        }

        journeyChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['No data'],
                datasets: [{
                    label: 'Events by Category',
                    data: values.length ? values : [0],
                    backgroundColor: '#695EEE',
                    borderColor: '#5648c7',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            }
        });
    }

    function loadJourneyData() {
        var entityId = $('#' + entityFilterId).val();
        var duration = $('#' + durationFilterId).val();

        $.ajax({
            url: dataUrl,
            type: 'GET',
            data: (function () {
                var payload = { draw: 1, duration: duration };
                payload[entityParam] = entityId;
                return payload;
            })(),
            success: function (response) {
                $('#' + panelId + 'TotalCount').text(response.total || 0);
                renderChart(
                    (response.chart && response.chart.labels) ? response.chart.labels : [],
                    (response.chart && response.chart.values) ? response.chart.values : []
                );

                if (journeyTable) {
                    journeyTable.clear();
                    journeyTable.rows.add(response.data || []);
                    journeyTable.draw();
                }
            }
        });
    }

    window['init' + panelId] = function () {
        if (initialized) {
            loadJourneyData();
            return;
        }

        initialized = true;

        journeyTable = $('#' + tableId).DataTable({
            processing: true,
            serverSide: false,
            searching: true,
            ordering: true,
            data: [],
            columns: [
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'event_category',
                    className: 'text-center',
                    render: function (data) {
                        return categoryBadge(data);
                    }
                },
                { data: 'event_type', className: 'text-center' },
                { data: 'user_name', className: 'text-center' },
                { data: 'event_detail', className: 'text-center' },
                {
                    data: null,
                    className: 'text-center',
                    render: function (row) {
                        var parts = [];
                        if (row.page_url) {
                            parts.push('<small>' + row.page_url + '</small>');
                        }
                        if (row.http_method) {
                            parts.push('<strong>' + row.http_method + '</strong>');
                        }
                        return parts.length ? parts.join('<br>') : '-';
                    }
                },
                {
                    data: 'ip_address',
                    className: 'text-center',
                    render: function (data) {
                        return data || '-';
                    }
                },
                { data: 'created_at_formatted', className: 'text-center' }
            ],
            order: [[7, 'desc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']]
        });

        $('#' + panelId + 'ApplyBtn').on('click', loadJourneyData);
        $('#' + entityFilterId + ', #' + durationFilterId).on('change', loadJourneyData);

        loadJourneyData();
    };
})();
</script>
