@extends('admin.layout.main')

@section('main-section')
    <style>
        .error {
            border: 2px red solid !important;
        }
    </style>
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="client-btn d-flex justify-content-center mb-4">
                <h3 class="text-primary px-3 text-center">Analytics</h3>
            </div>
        </div>




        <div class="row">
            @if (auth()->user()->user_type == 'Subscriber')
                <div class="col-md-2">
                    <label for="" class="fw-bold">Select Subscriber</label>
                    <select onchange="onChangeSub(this)" id="subscriberName" name="subscriberName" class="form-select"
                        aria-label="Default select example">
                        @foreach ($subscribers as $item => $index)
                            <option value="{{ $index }}">{{ $item }} ({{ $index }})</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="col-md-2">
                    <label for="" class="fw-bold">Select User Type</label>
                    <select id="selectAttribute1" onchange="onChangeUserType(this)" class="form-select"
                        aria-label="Default select example">
                        <option value = "" selected>Select User Type</option>
                        <option value="Affiliate">Affiliates</option>
                        <option value="Subscribers">Subscribers</option>

                    </select>
                </div>
                <div class="col-md-2" id="user_row" style="display:none;">
                    <label id="user_type" for=""></label>
                    <select onchange="onChangeSub(this)" id="subscriberName" name="subscriberName" class="form-select"
                        aria-label="Default select example" style="display:none">
                        <option value="" selected>All</option>
                        @foreach ($subscribers as $item => $index)
                            <option value="{{ $index }}">{{ $item }} ({{ $index }})</option>
                        @endforeach
                    </select>

                    {{-- <label class='affiliate-tab' for="" style="display:none">Select Affiliates</label> --}}
                    <select onchange="onChangeSub(this)" id="affiliateName" name="subscriberName"
                        class="form-select affiliate-tab subscriberName" aria-label="Default select example"
                        style="display:none">
                        <option value="" selected>All</option>
                        @foreach ($affiliates as $item => $index)
                            <option value="{{ $index }}">{{ $item }} ({{ $index }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-2 module-lists" style="display:none;">
                <label class="fw-bold" for="">Select Module</label>
                <select id="selectAttribute" onchange="onChangeAttribute(this)" class="form-select"
                    aria-label="Default select example">
                    <option value = "" selected>Select Module</option>
                    <option value="Subscribers">Subscribers</option>
                    <option value="Clients">Clients</option>
                    <option value="Applications">Applications</option>
                    <option value="Documents">Documents</option>
                    <option value="Users">Users</option>
                    <option value="Invoices">Invoices</option>
                    <option value="Payments">Payments</option>
                    <option value="Communications">Communications</option>
                    <option value="Referrals">Referrals</option>
                    <option value="Wallet">Wallet</option>
                    {{-- <option value="Affiliates" id="affiliatesOption1">Affiliates</option> --}}
                    <option value="Support Tickets">Support Tickets</option>
                    <option value="Demo Requests">Demo Requests</option>
                    <option value="Activity Log">Activity Log</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="fw-bold" for="">Select Attribute (Filter)</label>
                <select id="filters" onchange="onChangeFilter(this)" class="form-select"
                    aria-label="Default select example">
                    <option value="" selected>Select Filter</option>
                </select>
            </div>

            <div class="col-md-2 " style="display:none;" id="fourth-filter">
                <!-- <label id="fourth-filter-country" for=""></label> -->
                <!-- <select id="countries" style="display:none;" class="form-select" aria-label="Default select example">
                    <option value="All">All</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                    @endforeach
                </select> -->
                <select id="price-range" style="display:none;" class="form-select" aria-label="Default select example">
                    <option value="" selected>Select Filter</option>
                    <option value="10">10</option>
                    <option value="11-49">11-49</option>
                    <option value="50-99">50-99</option>
                    <option value="100-249">100-249</option>
                    <option value="250-499">250-499</option>
                    <option value="500-999">500-999</option>
                    <option value="1000-2499">1000-2499</option>
                    <option value="2500-4999">2500-4999</option>
                    <option value="5000-9999">5000-9999</option>
                    <option value="10000+">10,000+</option>
                </select>

                <select id="age-group" name="age-group" style="display:none;" class="form-select"
                    aria-label="Default select example">
                    <option value="0-18">0-18</option>
                    <option value="18-25">18-25</option>
                    <option value="26-35">26-35</option>
                    <option value="36-45">36-45</option>
                    <option value="46-55">46-55</option>
                    <option value="over-55">Over 55</option>
                </select>
                <select id="role" name="role" style="display:none;" class="form-select"
                    aria-label="Default select example">
                    <option value="All">All</option>
                    <option value="Branch Manager">Branch Manager</option>
                    <option value="Consultant">Consultant</option>
                    <option value="Advisor">Advisor</option>
                    <option value="Legal Expert">Legal Expert</option>

                </select>
                <select id="invoiceType" name="invoice_type" style="display:none;" class="form-select"
                    aria-label="Default select example">
                    <option value="All">All</option>
                    <option value="Raised">Raised</option>
                    <option value="Partially_Paid">Partially_Paid</option>
                    <option value="Fully_Paid">Fully_Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Cancelled">Cancelled</option>


                </select>
                <select id="payment_mode" name="payment_mode" style="display:none;" class="form-select"
                    aria-label="Default select example">
                    <option value="All">All</option>
                    <option value="Cash">Cash</option>
                    <option value="Bank Transfer">Bank Transfer (Wire)</option>
                    <option value="Card">Card</option>
                    <option value="Cheque">Cheque</option>
                    <option value="DD">DD</option>
                    <option value="UPI">UPI</option>
                    <option value="Credit Note">Credit Note</option>
                    <option value="Wallet Credit">Wallet Credit</option>
                </select>
                <input id="username" name="username" class="form-control" style="display:none;"
                    placeholder="Search by support staff name" />
            </div>
            <div class="col-md-2" id="date-range">
                <label class="fw-bold" style="width: 180px" for="custom_date_picker">Select Duration</label>
                <input type="text" id="custom_date_picker" name="custom_date_picker" placeholder="Select Duration" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="fw-bold" >Select Chart Type</label>
                <select id="chartType" class="form-select" aria-label="Default select example">
                    <option value="">Select Chart Type</option>
                    <option value="bar">Bar</option>
                    <option value="line" selected>Line</option>
                    <option value="pie">Pie</option>
                    <option value="doughnut">Doughnut</option>
                    <option value="area">Area</option>
                    <option value="scatter">Scatter</option>
                    <option value="bubble">Bubble</option>
                    <option value="gauge">Gauge</option>
                </select>
            </div>
        </div>
        <!-- <div class="row">
            <div class="col-md-6 my-5">
                <button class="login-btn" onclick="onClickGetReport()">View Data-Chart</button>
            </div>
            <div class="col-md-6 my-5">
                <button class="login-btn" id="downloadPdf" style="display: none">Download (PDF)</button>
            </div>

        </div> -->

        <div class="row mt-4 mb-4 d-flex justify-content-center">
            <div class="col-md-3 d-flex justify-content-center">
                <button class="login-btn" onclick="onClickGetReport()">View Data-Chart</button>
            </div>
            <div class="col-md-3 d-flex justify-content-center">
                <button class="login-btn" id="downloadPdf" style="display: none">Download (PDF)</button>
            </div>
        </div>

        {{-- <div class="row"> --}}

        <div class="analytics-chart-wrap">
            <canvas class="container" id="myChart" width="1000" height="500"></canvas>
        </div>
        {{-- </div> --}}
    </div>
    </div>
    </div>
@endsection()
@push('scripts')
    <!-- Required JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <!-- Adds Area / Scatter / Bubble / Gauge on top of Chart.js. Must load after chart.js. -->
    <script src="{{ asset('web_assets/js/adwiseri-chart-types.js') }}?v=20260814b"></script>
    <script src="{{ asset('web_assets/js/analytics-chart-overlap.js') }}?v=20260814b"></script>
<script>
/* Leave breathing room between the first/last category and the chart edges. */
Chart.defaults.scales.category.offset = true;
/* Axis / legend labels default to black so dense printouts stay readable. */
Chart.defaults.color = '#000000';
if (Chart.defaults.scale) {
    Chart.defaults.scale.ticks = Chart.defaults.scale.ticks || {};
    Chart.defaults.scale.ticks.color = '#000000';
    Chart.defaults.scale.border = Object.assign({}, Chart.defaults.scale.border || {}, {
        display: true,
        color: '#000000',
        width: 1
    });
}
['category', 'linear', 'logarithmic', 'time', 'timeseries', 'radialLinear'].forEach(function (scaleId) {
    if (!Chart.defaults.scales[scaleId]) {
        return;
    }
    if (Chart.defaults.scales[scaleId].ticks) {
        Chart.defaults.scales[scaleId].ticks.color = '#000000';
    }
    Chart.defaults.scales[scaleId].border = Object.assign({}, Chart.defaults.scales[scaleId].border || {}, {
        display: true,
        color: '#000000',
        width: 1
    });
});
if (Chart.defaults.plugins && Chart.defaults.plugins.legend && Chart.defaults.plugins.legend.labels) {
    Chart.defaults.plugins.legend.labels.color = '#000000';
}
if (Chart.defaults.plugins && Chart.defaults.plugins.tooltip) {
    Chart.defaults.plugins.tooltip.enabled = false;
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.94)';
    Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
    Chart.defaults.plugins.tooltip.bodyColor = '#ffffff';
    Chart.defaults.plugins.tooltip.footerColor = '#ffffff';
    Chart.defaults.plugins.tooltip.bodyAlign = 'left';
    Chart.defaults.plugins.tooltip.footerAlign = 'left';
    Chart.defaults.plugins.tooltip.boxWidth = 11;
    Chart.defaults.plugins.tooltip.boxHeight = 11;
    Chart.defaults.plugins.tooltip.boxPadding = 1;
    if (window.AdwiseriCharts && typeof window.AdwiseriCharts.renderHtmlTooltip === 'function') {
        Chart.defaults.plugins.tooltip.external = window.AdwiseriCharts.renderHtmlTooltip;
    }
}
if (Chart.defaults.plugins && Chart.defaults.plugins.datalabels) {
    Chart.defaults.plugins.datalabels.rotation = 0;
    Chart.defaults.plugins.datalabels.clamp = true;
    Chart.defaults.plugins.datalabels.clip = false;
}
/* Chart.js legend gaps swatch→text by fontSize/2 (~2 char spaces). Tighten to ~1. */
(function () {
    var plugin = Chart.registry && Chart.registry.plugins && Chart.registry.plugins.get
        ? Chart.registry.plugins.get('legend')
        : null;
    var Legend = plugin && plugin._element;
    if (!Legend || !Legend.prototype || !Legend.prototype._draw || Legend.prototype._adwiseriGapPatched) {
        return;
    }
    Legend.prototype._adwiseriGapPatched = true;
    var origDraw = Legend.prototype._draw;
    Legend.prototype._draw = function () {
        var ctx = this.ctx;
        var fontOpt = this.options.labels && this.options.labels.font;
        var fontSize = (fontOpt && fontOpt.size) || 11;
        if (Chart.helpers && typeof Chart.helpers.toFont === 'function') {
            fontSize = Chart.helpers.toFont(fontOpt).size || fontSize;
        }
        var tighten = fontSize / 4;
        var origFillText = ctx.fillText;
        var titleOn = this.options.title && this.options.title.display;
        var fills = 0;
        ctx.fillText = function (text, x, y, maxWidth) {
            if (titleOn && fills++ === 0) {
                return origFillText.call(this, text, x, y, maxWidth);
            }
            fills++;
            return origFillText.call(this, text, x - tighten, y, maxWidth);
        };
        try {
            return origDraw.apply(this, arguments);
        } finally {
            ctx.fillText = origFillText;
        }
    };
})();
if (typeof ChartDataLabels !== 'undefined' && Chart.registry && typeof Chart.register === 'function') {
    try {
        Chart.register(ChartDataLabels);
    } catch (e) {
        /* already registered */
    }
}

var NativeAnalyticsChart = Chart;

/* Disable Chart.js auto-colors plugin — it was overriding varied indicator colors. */
if (NativeAnalyticsChart.defaults.plugins && NativeAnalyticsChart.defaults.plugins.colors) {
    NativeAnalyticsChart.defaults.plugins.colors.enabled = false;
    NativeAnalyticsChart.defaults.plugins.colors.forceOverride = false;
}

/* High-contrast multi-hue palette for readable chart indicators (NOT Soft Indigo). */
var ANALYTICS_INDICATOR_PALETTE = [
    '#e6194B', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
    '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#fabed4',
    '#469990', '#dcbeff', '#9A6324', '#fffac8', '#800000',
    '#aaffc3', '#808000', '#ffd8b1', '#000075', '#a9a9a9',
    '#e6beff', '#1abc9c', '#e74c3c', '#3498db', '#f39c12',
    '#9b59b6', '#2ecc71', '#e67e22', '#1abc9c', '#c0392b'
];

function generateDistinctColors(count) {
    var colors = [];
    var i;
    for (i = 0; i < count; i++) {
        if (i < ANALYTICS_INDICATOR_PALETTE.length) {
            colors.push(ANALYTICS_INDICATOR_PALETTE[i]);
        } else {
            var hue = Math.round((i * 137.508) % 360);
            colors.push('hsl(' + hue + ', 72%, 45%)');
        }
    }
    return colors;
}

function buildTimelineChartData(result) {
    const timelineOrder = ['Today', 'Last Week', 'Last Month', 'Last Quarter', 'Last Year', 'Since Inception'];
    const timelineCounts = {
        'Today': 0,
        'Last Week': 0,
        'Last Month': 0,
        'Last Quarter': 0,
        'Last Year': 0,
        'Since Inception': 0
    };

    if (Array.isArray(result)) {
        result.forEach(function(currentElement) {
            if (currentElement && Object.prototype.hasOwnProperty.call(timelineCounts, currentElement.type)) {
                timelineCounts[currentElement.type] = currentElement.count;
            }
        });
    }

    return {
        labels: timelineOrder,
        numbers: timelineOrder.map(function(label) {
            return timelineCounts[label];
        })
    };
}

function isCircularAnalyticsChart(type) {
    return ['pie', 'doughnut', 'gauge'].includes(type);
}

/* Prefer the original Analytics style (area/scatter/gauge) over the Chart.js base type. */
function analyticsChartStyle(config) {
    return (config && (config.adwiseriStyle || config.type)) || '';
}

function resolveAnalyticsIndicatorColor(dataset, index) {
    var resolvedIndex = index;
    if (typeof resolvedIndex === 'number' && resolvedIndex >= 0) {
        if (Array.isArray(dataset._indicatorColors) && dataset._indicatorColors[resolvedIndex]) {
            return dataset._indicatorColors[resolvedIndex];
        }
        if (Array.isArray(dataset.pointBackgroundColor) && dataset.pointBackgroundColor[resolvedIndex]) {
            return dataset.pointBackgroundColor[resolvedIndex];
        }
        if (Array.isArray(dataset.backgroundColor) && dataset.backgroundColor[resolvedIndex]) {
            return dataset.backgroundColor[resolvedIndex];
        }
    }
    if (typeof dataset.backgroundColor === 'string' && dataset.backgroundColor !== 'rgba(0,0,0,0)') {
        return dataset.backgroundColor;
    }
    if (typeof dataset.borderColor === 'string' && dataset.borderColor !== '#555555') {
        return dataset.borderColor;
    }
    return ANALYTICS_INDICATOR_PALETTE[Math.abs(resolvedIndex || 0) % ANALYTICS_INDICATOR_PALETTE.length];
}

function removeCircularChartAxes(config) {
    if (!isCircularAnalyticsChart(analyticsChartStyle(config))) {
        return;
    }

    config.options.scales = {
        x: { display: false, grid: { display: false }, ticks: { display: false }, border: { display: false } },
        y: { display: false, grid: { display: false }, ticks: { display: false }, border: { display: false } }
    };
}

function ensureAnalyticsCategoryPadding(config) {
    if (isCircularAnalyticsChart(analyticsChartStyle(config))) {
        return;
    }

    config.options.scales = config.options.scales || {};
    config.options.scales.x = Object.assign({}, config.options.scales.x || {}, {
        offset: true
    });
}

/* Guarantee one distinct color per category for bars/points AND bottom legend. */
function ensureAnalyticsIndicatorColors(config) {
    var style = analyticsChartStyle(config);
    var labels = (config.data && config.data.labels) ? config.data.labels : [];
    var datasets = (config.data && config.data.datasets) ? config.data.datasets : [];
    if (!datasets.length) {
        return;
    }

    if (datasets.length === 1 || isCircularAnalyticsChart(style)) {
        var dataset = datasets[0];
        var count = Math.max(
            labels.length,
            Array.isArray(dataset.data) ? dataset.data.length : 0,
            1
        );
        var colors = null;

        if (Array.isArray(dataset._indicatorColors) && dataset._indicatorColors.length >= count) {
            colors = dataset._indicatorColors.slice(0, count);
        } else if (Array.isArray(dataset.pointBackgroundColor) && dataset.pointBackgroundColor.length >= count) {
            colors = dataset.pointBackgroundColor.slice(0, count);
        } else if (Array.isArray(dataset.backgroundColor) && dataset.backgroundColor.length >= count) {
            colors = dataset.backgroundColor.slice(0, count);
        } else if (Array.isArray(dataset.borderColor) && dataset.borderColor.length >= count) {
            colors = dataset.borderColor.slice(0, count);
        } else {
            colors = generateDistinctColors(count);
        }

        dataset._indicatorColors = colors;

        if (style === 'line' || style === 'scatter' || style === 'bubble' || style === 'area') {
            dataset.pointBackgroundColor = colors;
            dataset.pointBorderColor = colors;
            if (style === 'scatter' || style === 'bubble') {
                dataset.backgroundColor = colors;
                dataset.borderColor = colors;
            }
        } else {
            dataset.backgroundColor = colors;
            dataset.hoverBackgroundColor = colors;
            if (style === 'bar') {
                dataset.borderColor = colors;
            }
        }

        if (isCircularAnalyticsChart(style)) {
            dataset.borderColor = '#ffffff';
            dataset.borderWidth = typeof dataset.borderWidth === 'number' ? dataset.borderWidth : 1;
        }

        return;
    }

    datasets.forEach(function (dataset, datasetIndex) {
        var color = ANALYTICS_INDICATOR_PALETTE[datasetIndex % ANALYTICS_INDICATOR_PALETTE.length];
        dataset._indicatorColors = [color];
        if (!dataset.backgroundColor || dataset.backgroundColor === 'rgba(0,0,0,0)') {
            dataset.backgroundColor = color;
        }
        if (!dataset.borderColor || Array.isArray(dataset.borderColor)) {
            dataset.borderColor = color;
        }
        if (style === 'line' || style === 'scatter' || style === 'bubble' || style === 'area') {
            dataset.pointBackgroundColor = color;
            dataset.pointBorderColor = color;
        }
    });
}

var ANALYTICS_POINT_RADIUS = 6;
var ANALYTICS_POINT_HOVER_RADIUS = 8;

function isPointStyleAnalyticsChart(type) {
    return type === 'line' || type === 'scatter' || type === 'bubble' || type === 'area';
}

/* Line: solid connecting stroke + curved tension; per-point colors stay on the dots/legend.
 * Scatter: match line indicator dot size. */
function fixAnalyticsLinePointColors(config) {
    var style = analyticsChartStyle(config);
    if (!isPointStyleAnalyticsChart(style)) {
        return;
    }

    var datasets = (config.data && config.data.datasets) ? config.data.datasets : [];
    var isLine = style === 'line';
    var isScatter = style === 'scatter';
    var isScatterLike = isScatter || style === 'bubble';

    /* Collapse broken multi-dataset line charts (each dataset held the full values array). */
    if (isLine && datasets.length > 1) {
        var labels = (config.data && config.data.labels) ? config.data.labels : [];
        var firstData = Array.isArray(datasets[0].data) ? datasets[0].data : [];
        var allSameFullSeries = firstData.length === labels.length && labels.length > 0 && datasets.every(function (ds) {
            var raw = Array.isArray(ds.data) ? ds.data : [];
            if (raw.length !== firstData.length) {
                return false;
            }
            var i;
            for (i = 0; i < raw.length; i++) {
                if (raw[i] !== firstData[i]) {
                    return false;
                }
            }
            return true;
        });

        if (allSameFullSeries) {
            var collapsedColors = generateDistinctColors(firstData.length);
            config.data.datasets = [{
                label: datasets[0].label || 'Value',
                data: firstData.slice(),
                borderWidth: 2,
                borderColor: '#555555',
                backgroundColor: 'rgba(0,0,0,0)',
                fill: false,
                tension: 0.35,
                cubicInterpolationMode: 'monotone',
                pointRadius: ANALYTICS_POINT_RADIUS,
                pointHoverRadius: ANALYTICS_POINT_HOVER_RADIUS,
                pointBackgroundColor: collapsedColors,
                pointBorderColor: collapsedColors,
                _indicatorColors: collapsedColors,
                spanGaps: true
            }];
            datasets = config.data.datasets;
        }
    }

    datasets.forEach(function (dataset) {
        var pointColors = Array.isArray(dataset._indicatorColors)
            ? dataset._indicatorColors
            : (Array.isArray(dataset.pointBackgroundColor)
                ? dataset.pointBackgroundColor
                : (Array.isArray(dataset.backgroundColor) ? dataset.backgroundColor : null));

        if (pointColors) {
            dataset._indicatorColors = pointColors;
            dataset.pointBackgroundColor = pointColors;
            dataset.pointBorderColor = pointColors;
        }

        if (isLine) {
            /* Transparent area fill — keep _indicatorColors / point colors for legend dots. */
            dataset.backgroundColor = 'rgba(0,0,0,0)';
            dataset.fill = false;

            if (Array.isArray(dataset.borderColor) || !dataset.borderColor) {
                dataset.borderColor = '#555555';
            }

            dataset.borderWidth = dataset.borderWidth || 2;
            dataset.tension = (typeof dataset.tension === 'number') ? dataset.tension : 0.35;
            dataset.cubicInterpolationMode = dataset.cubicInterpolationMode || 'monotone';
            dataset.spanGaps = true;
        }

        if (isScatterLike && pointColors) {
            dataset.backgroundColor = pointColors;
            dataset.borderColor = pointColors;
        }

        /* Keep scatter dots the same size as line chart dots. */
        if (isLine || isScatter) {
            dataset.pointRadius = ANALYTICS_POINT_RADIUS;
            dataset.pointHoverRadius = ANALYTICS_POINT_HOVER_RADIUS;
            dataset.radius = ANALYTICS_POINT_RADIUS;
            dataset.hoverRadius = ANALYTICS_POINT_HOVER_RADIUS;
        } else if (style === 'area') {
            dataset.pointRadius = dataset.pointRadius || ANALYTICS_POINT_RADIUS;
            dataset.pointHoverRadius = dataset.pointHoverRadius || ANALYTICS_POINT_HOVER_RADIUS;
        }
    });
}

function analyticsTooltipRawValue(raw) {
    if (raw && typeof raw === 'object') {
        return raw.y != null ? raw.y : (raw.x != null ? raw.x : 0);
    }
    return raw;
}

function isAnalyticsAmountRangeLabel(label) {
    var text = String(label == null ? '' : label).trim().replace(/\s+/g, '');
    if (!text) {
        return false;
    }
    /* Only known money buckets — not age groups like 18-24 / 55+. */
    var known = {
        '1-99': true,
        '100-249': true,
        '250-499': true,
        '500-999': true,
        '1000-2499': true,
        '2500-4999': true,
        '5000-9999': true,
        '10,000+': true,
        '10000+': true
    };
    return !!known[text];
}

function isAnalyticsAgeGroupLabel(label) {
    var text = String(label == null ? '' : label).trim().toLowerCase().replace(/\s+/g, ' ');
    if (!text) {
        return false;
    }
    if (text === 'under 18') {
        return true;
    }
    if (/^(18-24|25-34|35-44|45-55)$/.test(text)) {
        return true;
    }
    return /^55\s*\+$/.test(text);
}

/* Tooltip caption: "1-99" → "1 - 99", "10,000+" stays readable */
function formatAnalyticsAmountRangeCaption(label) {
    var text = String(label == null ? '' : label).trim();
    if (!text) {
        return '';
    }
    if (text.indexOf('+') >= 0) {
        return text.replace(/\s*\+\s*$/, '+');
    }
    return text.replace(/\s*-\s*/g, ' - ');
}

function normalizeAnalyticsTooltipLines(lines) {
    if (!Array.isArray(lines)) {
        return lines;
    }
    return lines.map(function (line) {
        return String(line)
            .replace(/^Percent Value:\s*/i, 'Percentage : ')
            .replace(/^Percentage:\s*/i, 'Percentage : ')
            .replace(/^Value:\s*/i, 'Value : ')
            .replace(/(\d+(?:\.\d+)?)%\s*$/i, '$1 %');
    });
}

function sumAnalyticsDatasetValues(data) {
    if (!Array.isArray(data)) {
        return 0;
    }
    return data.reduce(function (acc, val) {
        return acc + (Number(analyticsTooltipRawValue(val)) || 0);
    }, 0);
}

function fixAnalyticsTooltip(config) {
    config.options.interaction = {
        mode: 'nearest',
        intersect: true,
        axis: 'xy'
    };
    config.options.hover = {
        mode: 'nearest',
        intersect: true
    };

    var existingTooltip = config.options.plugins.tooltip || {};
    var existingCallbacks = existingTooltip.callbacks || {};
    var labels = (config.data && config.data.labels) ? config.data.labels : [];

    config.options.plugins.tooltip = Object.assign({}, existingTooltip, {
        enabled: false,
        external: (window.AdwiseriCharts && window.AdwiseriCharts.renderHtmlTooltip) || existingTooltip.external,
        mode: 'nearest',
        intersect: true,
        position: 'nearest',
        backgroundColor: 'rgba(15, 23, 42, 0.94)',
        titleColor: '#ffffff',
        bodyColor: '#ffffff',
        footerColor: '#ffffff',
        bodyAlign: 'left',
        footerAlign: 'left',
        boxWidth: 11,
        boxHeight: 11,
        boxPadding: 1,
        filter: function (tooltipItem, index, tooltipItems) {
            if (Array.isArray(tooltipItems) && tooltipItems.length) {
                return tooltipItems.indexOf(tooltipItem) === 0;
            }
            return !index;
        },
        callbacks: Object.assign({}, existingCallbacks, {
            title: function (items) {
                /* Return null (not '') so Chart.js does not draw a title/body divider above the color square. */
                return null;
            },
            beforeBody: function () {
                /* Separator is drawn in footer, below the color + category line. */
                return null;
            },
            label: function (context) {
                var index = context.dataIndex;
                if (context.parsed && context.parsed.x !== undefined && labels[context.parsed.x] !== undefined) {
                    index = context.parsed.x;
                }
                var pointLabel = labels[index] !== undefined ? String(labels[index]) : '';
                if (!pointLabel && context.label) {
                    pointLabel = String(context.label);
                }
                if (!pointLabel && context.formattedValue != null) {
                    pointLabel = String(context.formattedValue);
                }
                var isAmountRange = isAnalyticsAmountRangeLabel(pointLabel);

                if (config._formatByteValues) {
                    return 'Doc : ' + pointLabel;
                }

                if (config._formatFileTypeChart) {
                    return 'FileType : ' + pointLabel;
                }

                if (config._formatAgeGroupChart || isAnalyticsAgeGroupLabel(pointLabel)) {
                    return 'Age Group : ' + pointLabel;
                }

                if (isAmountRange) {
                    return 'Amount (Range) : ' + formatAnalyticsAmountRangeCaption(pointLabel);
                }

                /* Color square + category name appear above the separator. */
                return pointLabel;
            },
            afterLabel: function () {
                return null;
            },
            afterBody: function () {
                return [];
            },
            footer: function (tooltipItems) {
                if (!tooltipItems || !tooltipItems.length) {
                    return [];
                }
                var item = tooltipItems[0];
                var dataValue = Number(analyticsTooltipRawValue(item.raw)) || 0;

                if (config._formatByteValues) {
                    return [
                        '-----------------------',
                        'File Size : ' + formatBytes(dataValue, 2)
                    ];
                }

                var total = sumAnalyticsDatasetValues(item.dataset && item.dataset.data);
                var percentage = total > 0 ? ((dataValue / total) * 100).toFixed(1) : '0.0';
                return [
                    '-----------------------',
                    'Value : ' + dataValue,
                    'Percentage : ' + percentage + ' %'
                ];
            },
            labelColor: function (context) {
                var index = context.dataIndex;
                if (context.parsed && context.parsed.x !== undefined && !isNaN(context.parsed.x)) {
                    index = Math.round(Number(context.parsed.x));
                }
                var color = resolveAnalyticsIndicatorColor(context.dataset || {}, index);
                return { borderColor: color, backgroundColor: color };
            },
            labelTextColor: function () {
                return '#ffffff';
            }
        })
    });
    if (window.AdwiseriCharts && typeof window.AdwiseriCharts.completeTooltipCallbacks === 'function') {
        config.options.plugins.tooltip.callbacks = window.AdwiseriCharts.completeTooltipCallbacks(
            config.options.plugins.tooltip.callbacks
        );
    }
}

function analyticsPointCount(config) {
    var labels = (config.data && config.data.labels) ? config.data.labels : [];
    var datasets = (config.data && config.data.datasets) ? config.data.datasets : [];
    var dataLen = 0;
    if (datasets[0] && Array.isArray(datasets[0].data)) {
        dataLen = datasets[0].data.length;
    }
    return Math.max(labels.length, dataLen, 0);
}

/* Keep value counts horizontal for readability; overlap helper handles dense charts. */
function shouldRotateAnalyticsDataLabels(config) {
    return false;
}

function formatBytes(bytes, decimals) {
    decimals = decimals === undefined ? 2 : decimals;
    bytes = Number(bytes) || 0;
    if (bytes === 0) {
        return '0 Bytes';
    }

    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
}

/*
 * Value labels stay horizontal on every chart type so counts remain readable.
 */
function cleanAnalyticsDataLabels(config) {
    var style = analyticsChartStyle(config);
    var count = analyticsPointCount(config);
    var plugins = config.options.plugins;
    var existing = plugins.datalabels || {};
    var customFormatter = existing.formatter;
    var isPointChart = style === 'line' || style === 'scatter';
    var showLabels = false;
    var rotateVertical = false;

    if (isCircularAnalyticsChart(style)) {
        showLabels = count > 0 && count <= 10;
    } else {
        showLabels = count > 0;
        rotateVertical = shouldRotateAnalyticsDataLabels(config);
    }

    plugins.datalabels = Object.assign({}, existing, {
        display: showLabels,
        clamp: true,
        clip: false,
        anchor: isCircularAnalyticsChart(style) ? 'center' : 'end',
        align: isCircularAnalyticsChart(style) ? 'center' : (rotateVertical ? 'right' : 'top'),
        offset: rotateVertical ? 8 : 6,
        rotation: rotateVertical ? -90 : 0,
        font: {
            size: rotateVertical ? 9 : (count > 10 ? 10 : 12),
            weight: '700'
        },
        color: '#000000',
        formatter: function (value, context) {
            if (config._formatByteValues) {
                var raw = value;
                if (value && typeof value === 'object') {
                    raw = value.y != null ? value.y : value;
                }
                return formatBytes(Number(raw) || 0, 2);
            }
            if (typeof customFormatter === 'function') {
                return customFormatter(value, context);
            }
            if (value && typeof value === 'object') {
                return value.y != null ? value.y : '';
            }
            return value;
        }
    });

    if (showLabels) {
        config.options.layout = config.options.layout || {};
        config.options.layout.padding = Object.assign(
            {},
            config.options.layout.padding || {},
            {
                top: rotateVertical ? 32 : 24,
                right: rotateVertical ? 20 : 14,
                bottom: 8,
                left: 8
            }
        );
    }
}

function cleanAnalyticsAxes(config) {
    var style = analyticsChartStyle(config);
    if (isCircularAnalyticsChart(style)) {
        return;
    }

    var count = analyticsPointCount(config);
    var scales = config.options.scales || (config.options.scales = {});
    var x = scales.x || (scales.x = {});
    var y = scales.y || (scales.y = {});
    var xTicks = x.ticks || (x.ticks = {});
    var yTicks = y.ticks || (y.ticks = {});

    x.offset = true;

    /* Axis line itself (not grid) must be black for print/screen clarity. */
    x.border = Object.assign({}, x.border || {}, {
        display: true,
        color: '#000000',
        width: 1
    });
    y.border = Object.assign({}, y.border || {}, {
        display: true,
        color: '#000000',
        width: 1
    });

    /* Always show every category name (no skipping / no ellipsis truncation). */
    xTicks.autoSkip = false;
    xTicks.maxRotation = count > 6 ? 70 : 45;
    xTicks.minRotation = count > 6 ? 50 : 0;
    /* Black labels print clearly when font size is reduced for dense charts. */
    xTicks.color = '#000000';
    xTicks.font = Object.assign({}, xTicks.font || {}, {
        size: count > 12 ? 8 : (count > 6 ? 9 : 10),
        weight: '600'
    });

    y.beginAtZero = true;
    yTicks.precision = typeof yTicks.precision === 'number' ? yTicks.precision : 0;
    yTicks.color = '#000000';
    yTicks.font = Object.assign({}, yTicks.font || {}, { size: 10, weight: '600' });
    if (config._formatByteValues) {
        yTicks.callback = function (value) {
            return formatBytes(Number(value) || 0, Number(value) >= 1048576 ? 1 : 0);
        };
        delete yTicks.stepSize;
    }
    y.ticks = yTicks;
    y.grace = y.grace || '10%';

    /* Force axis title / border text black too when present. */
    if (x.title) {
        x.title.color = '#000000';
    }
    if (y.title) {
        y.title.color = '#000000';
    }
}

/* Area charts need a visible non-black fill under the line. */
function fixAnalyticsAreaFill(config) {
    if (analyticsChartStyle(config) !== 'area') {
        return;
    }

    var AREA_STROKE = '#4363d8';
    var AREA_FILL = 'rgba(67, 99, 216, 0.35)';
    var datasets = (config.data && config.data.datasets) ? config.data.datasets : [];

    datasets.forEach(function (dataset) {
        var pointColors = Array.isArray(dataset._indicatorColors)
            ? dataset._indicatorColors
            : (Array.isArray(dataset.pointBackgroundColor) ? dataset.pointBackgroundColor : null);

        dataset.fill = 'origin';
        dataset.tension = typeof dataset.tension === 'number' ? dataset.tension : 0.35;
        dataset.borderColor = AREA_STROKE;
        dataset.backgroundColor = AREA_FILL;
        dataset.borderWidth = dataset.borderWidth || 2;
        dataset.pointRadius = dataset.pointRadius || ANALYTICS_POINT_RADIUS;
        dataset.pointHoverRadius = dataset.pointHoverRadius || ANALYTICS_POINT_HOVER_RADIUS;

        if (pointColors && pointColors.length) {
            dataset.pointBackgroundColor = pointColors;
            dataset.pointBorderColor = pointColors;
            dataset._indicatorColors = pointColors;
        } else {
            dataset.pointBackgroundColor = AREA_STROKE;
            dataset.pointBorderColor = AREA_STROKE;
        }
    });
}

function isMeaningfulAnalyticsLabel(label) {
    var text = String(label == null ? '' : label).trim();
    if (!text) {
        return false;
    }
    var lower = text.toLowerCase();
    return lower !== 'null' && lower !== 'undefined' && lower !== 'n/a' && lower !== '-' && lower !== 'none';
}

function buildAnalyticsLegendItems(chart) {
    var datasets = chart.data.datasets || [];
    var labels = chart.data.labels || [];

    /* One color swatch per category/label — skip blank / placeholder names (orphan color dots). */
    if (datasets.length === 1 && labels.length) {
        var dataset = datasets[0];
        var items = [];
        labels.forEach(function (label, index) {
            if (!isMeaningfulAnalyticsLabel(label)) {
                return;
            }
            var color = resolveAnalyticsIndicatorColor(dataset, index);
            items.push({
                text: ' ' + String(label).trim(),
                fillStyle: color,
                strokeStyle: color,
                fontColor: '#000000',
                color: '#000000',
                lineWidth: 1,
                pointStyle: 'circle',
                hidden: typeof chart.getDataVisibility === 'function'
                    ? !chart.getDataVisibility(index)
                    : false,
                index: index,
                datasetIndex: 0
            });
        });
        return items;
    }

    /* Multi-series charts: one swatch per dataset. */
    return datasets.map(function (dataset, datasetIndex) {
        var color = resolveAnalyticsIndicatorColor(dataset, 0);
        return {
            text: ' ' + String(dataset.label || ('Series ' + (datasetIndex + 1))).trim(),
            fillStyle: color,
            strokeStyle: color,
            fontColor: '#000000',
            color: '#000000',
            lineWidth: 1,
            pointStyle: 'circle',
            hidden: typeof chart.isDatasetVisible === 'function'
                ? !chart.isDatasetVisible(datasetIndex)
                : false,
            datasetIndex: datasetIndex
        };
    });
}

function enableAnalyticsLegend(config) {
    var count = analyticsPointCount(config);

    /* Always show the full legend (all names). Chart plot keeps reserved height via layout. */
    config.options.layout = Object.assign({}, config.options.layout || {}, {
        padding: Object.assign(
            { top: 16, right: 14, bottom: 18, left: 10 },
            (config.options.layout && config.options.layout.padding) || {}
        )
    });

    config.options.plugins.legend = {
        display: true,
        position: 'bottom',
        align: 'center',
        fullSize: true,
        labels: {
            padding: count > 12 ? 8 : 12,
            boxWidth: 11,
            boxHeight: 11,
            usePointStyle: true,
            pointStyle: 'circle',
            color: '#000000',
            font: {
                size: count > 12 ? 10 : 11,
                weight: '500'
            },
            generateLabels: function (chart) {
                return buildAnalyticsLegendItems(chart);
            }
        },
        onClick: function (event, legendItem, legend) {
            var chart = legend.chart;
            if (typeof legendItem.index !== 'number' || legendItem.index < 0) {
                return;
            }
            if (chart.data.datasets.length === 1) {
                chart.toggleDataVisibility(legendItem.index);
                chart.update();
                return;
            }
            if (typeof legendItem.datasetIndex === 'number') {
                chart.setDatasetVisibility(
                    legendItem.datasetIndex,
                    !chart.isDatasetVisible(legendItem.datasetIndex)
                );
                chart.update();
            }
        }
    };
}

function pruneEmptyAnalyticsLabels(config) {
    var labels = (config.data && config.data.labels) ? config.data.labels : null;
    var datasets = (config.data && config.data.datasets) ? config.data.datasets : null;
    if (!Array.isArray(labels) || !Array.isArray(datasets) || !datasets.length) {
        return;
    }

    var keepIndexes = [];
    labels.forEach(function (label, index) {
        if (isMeaningfulAnalyticsLabel(label)) {
            keepIndexes.push(index);
        }
    });

    if (keepIndexes.length === labels.length) {
        return;
    }

    config.data.labels = keepIndexes.map(function (index) {
        return labels[index];
    });

    datasets.forEach(function (dataset) {
        if (Array.isArray(dataset.data)) {
            dataset.data = keepIndexes.map(function (index) {
                return dataset.data[index];
            });
        }
        ['_indicatorColors', 'pointBackgroundColor', 'pointBorderColor', 'backgroundColor', 'borderColor', 'hoverBackgroundColor'].forEach(function (key) {
            if (Array.isArray(dataset[key])) {
                dataset[key] = keepIndexes.map(function (index) {
                    return dataset[key][index];
                });
            }
        });
    });
}

function sanitizeAnalyticsChartConfig(config) {
    config.options = config.options || {};
    config.options.plugins = config.options.plugins || {};
    config.options.plugins.colors = { enabled: false, forceOverride: false };

    /* Keep the plot area visible even when the legend lists many names. */
    config.options.maintainAspectRatio = false;
    config.options.responsive = true;

    pruneEmptyAnalyticsLabels(config);
    removeCircularChartAxes(config);
    ensureAnalyticsCategoryPadding(config);
    ensureAnalyticsIndicatorColors(config);
    fixAnalyticsLinePointColors(config);
    ensureAnalyticsIndicatorColors(config); /* re-assert colors after line normalization */
    fixAnalyticsAreaFill(config);
    cleanAnalyticsAxes(config);
    cleanAnalyticsDataLabels(config);
    fixAnalyticsTooltip(config);
    enableAnalyticsLegend(config);

    if (window.AdwiseriAnalyticsOverlap && typeof window.AdwiseriAnalyticsOverlap.enhance === 'function') {
        window.AdwiseriAnalyticsOverlap.enhance(config);
    }

    /* Final pass: axis tick/label/line color must stay black for print readability. */
    if (config.options.scales) {
        Object.keys(config.options.scales).forEach(function (scaleId) {
            var scale = config.options.scales[scaleId];
            if (!scale || scale.display === false) {
                return;
            }
            scale.ticks = scale.ticks || {};
            scale.ticks.color = '#000000';
            scale.border = Object.assign({}, scale.border || {}, {
                display: true,
                color: '#000000',
                width: 1
            });
            if (scale.title) {
                scale.title.color = '#000000';
            }
        });
    }
}

function AnalyticsChart(ctx, config) {
    /*
     * Adapt virtual types (area/scatter/bubble/gauge) FIRST, then sanitize so
     * black axes, matched point sizes, and value labels always win.
     */
    if (window.AdwiseriCharts && typeof window.AdwiseriCharts.adaptConfig === 'function') {
        window.AdwiseriCharts.adaptConfig(config);
    }
    sanitizeAnalyticsChartConfig(config);

    var RealChart = Object.getPrototypeOf(NativeAnalyticsChart);
    if (typeof RealChart === 'function' && RealChart !== NativeAnalyticsChart && RealChart !== Function.prototype) {
        return new RealChart(ctx, config);
    }
    return new NativeAnalyticsChart(ctx, config);
}
Object.setPrototypeOf(AnalyticsChart, NativeAnalyticsChart);
AnalyticsChart.prototype = NativeAnalyticsChart.prototype;
window.Chart = AnalyticsChart;

/* Re-assert defaults on the live Chart constructor after wrapping. */
Chart.defaults.color = '#000000';
if (Chart.defaults.scale) {
    Chart.defaults.scale.ticks = Chart.defaults.scale.ticks || {};
    Chart.defaults.scale.ticks.color = '#000000';
    Chart.defaults.scale.border = Object.assign({}, Chart.defaults.scale.border || {}, {
        display: true,
        color: '#000000',
        width: 1
    });
}
['category', 'linear', 'logarithmic', 'time', 'timeseries', 'radialLinear'].forEach(function (scaleId) {
    if (!Chart.defaults.scales[scaleId]) {
        return;
    }
    if (Chart.defaults.scales[scaleId].ticks) {
        Chart.defaults.scales[scaleId].ticks.color = '#000000';
    }
    Chart.defaults.scales[scaleId].border = Object.assign({}, Chart.defaults.scales[scaleId].border || {}, {
        display: true,
        color: '#000000',
        width: 1
    });
});
if (Chart.defaults.plugins && Chart.defaults.plugins.legend && Chart.defaults.plugins.legend.labels) {
    Chart.defaults.plugins.legend.labels.color = '#000000';
}
if (Chart.defaults.plugins && Chart.defaults.plugins.tooltip) {
    Chart.defaults.plugins.tooltip.enabled = false;
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.94)';
    Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
    Chart.defaults.plugins.tooltip.bodyColor = '#ffffff';
    Chart.defaults.plugins.tooltip.footerColor = '#ffffff';
    Chart.defaults.plugins.tooltip.bodyAlign = 'left';
    Chart.defaults.plugins.tooltip.footerAlign = 'left';
    Chart.defaults.plugins.tooltip.boxWidth = 11;
    Chart.defaults.plugins.tooltip.boxHeight = 11;
    Chart.defaults.plugins.tooltip.boxPadding = 1;
    if (window.AdwiseriCharts && typeof window.AdwiseriCharts.renderHtmlTooltip === 'function') {
        Chart.defaults.plugins.tooltip.external = window.AdwiseriCharts.renderHtmlTooltip;
    }
}
if (Chart.defaults.plugins && Chart.defaults.plugins.datalabels) {
    Chart.defaults.plugins.datalabels.rotation = 0;
    Chart.defaults.plugins.datalabels.clamp = true;
    Chart.defaults.plugins.datalabels.clip = false;
}
</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script>
    window.clientVisaChartFilters = @json($clientVisaChartFilters ?? []);
    </script>
    <script src="{{ asset('web_assets/js/analytics-client-visa-charts.js') }}?v=20260814b"></script>

    <script>
        function onChangeSub() {
            var s = $('#subscriberName').val();

            if (s && $('#selectAttribute').val() == 'Activity Log') {

                $("#filters option[value='By Top 10 Subscribers']").remove();
            } else {
                
               document.getElementById('selectAttribute1').addEventListener('change', function () {
                    const selectedValue = this.value;

                    // Remove previously added option to avoid duplicates
                    $("#filters option[value='By Top 10 Subscribers']").remove();

                    if (selectedValue === 'Subscribers') {
                        // Insert the option only if it's not already there (though we remove it above as safety)
                        $('<option value="By Top 10 Subscribers">By Top 10 Subscribers</option>').insertAfter(
                            '#filters option:nth-child(3)'
                        );
                    }
                });

            }
            var s = $('#subscriberName').val();
            if (s) {
                $("#selectAttribute option[value='Subscribers']").remove();
                $("#selectAttribute option[value='Referrals']").remove();
                $("#selectAttribute option[value='Support Tickets']").remove();
                $("#selectAttribute option[value='Affiliates']").remove();
            } else {
                $('<option value="Subscribers">Subscribers</option>').insertAfter('#selectAttribute option:nth-child(1)');
                $('<option value="Support Tickets">Support Tickets</option>').insertAfter(
                    '#selectAttribute option:nth-child(12)');
                $('<option value="Referrals">Referrals</option>').insertAfter('#selectAttribute option:nth-child(9)');
                $('<option value="Affiliates">Affiliates</option>').insertAfter('#selectAttribute option:nth-child(11)');
            }

        }

        function onChangeUserType(select) {
            resetOnChangeUserType();
            const selectedValue = select.value;
            const moduleLists = document.getElementById('module-lists');
            const row = document.getElementById('user_row');
            const label = document.getElementById('user_type');
            row.style.display = 'block';

            if (selectedValue == 'Subscribers') {
                const element = document.getElementById('subscriberName');
                element.style.display = 'block';
                label.textContent = 'Select Subscriber';
                $('.module-lists').css('display', 'block');

            } else {
                const element = document.getElementById('affiliateName');
                element.style.display = 'block';
                label.textContent = 'Select Affiliates';
                // var selectValue = attribute.value;
                $('.module-lists').css('display', 'none');
                let select_elem = document.getElementById('filters');
                var langArray = [

                    {
                        text: "By No. of Subscribers Referred",
                        value: "By Affiliates No. of Subscribers Referred"
                    },
                    {
                        text: "By Amount of Commissions Earnt",
                        value: "By Amount of Affiliates Commissions Earnt"
                    },
                    {
                        text: "By Country",
                        value: "By Affiliate Country"
                    },

                    {
                        text: "By Subscribed Plan",
                        value: "By Affiliate Subscribed Plan"
                    },
                    {
                        text: "By Current Wallet Credits",
                        value: "By Affiliate Current Wallet Credits"
                    }

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });

            }

        }

        function resetOnChangeUserType() {
            const label = document.getElementById('user_type');
            const element1 = document.getElementById('subscriberName');
            const element2 = document.getElementById('affiliateName');
            $("#filters").empty();
            element1.style.display = 'none';
            element2.style.display = 'none';
            label.textContent = ''
        }

        function resetFourthColFilter() {
            const idsToReset = [
                'fourth-filter',
                'countries',
                'age-group',
                'price-range',
                'role',
                'payment_mode',
                'username'
            ];

            idsToReset.forEach((id) => {
                const element = document.getElementById(id);
                if (element) {
                    element.style.display = 'none';
                }
            });

            // Reset label or perform other operations
            const label = document.getElementById('fourth-filter-country');
            if (label) {
                label.textContent = ''; // Reset label content
            }
        }

        function onChangeAttribute(attribute) {
            var user_type = @json(auth()->user()->user_type);

            var selectValue = attribute.value;
            let select_elem = document.getElementById('filters');
            $("#filters").empty();

            resetFourthColFilter()
            if (selectValue == 'Subscribers') {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Country",
                        value: "By Subscriber Country"
                    },
                    {
                        text: "By Profile/Sub-category",
                        value: "By Subscriber Category"
                    },
                    // {
                    //     text: "By Sub Category",
                    //     value: "By Sub Category"
                    // },
                    {
                        text: "By Subscribed Plan Type",
                        value: "By Subscriber Plan Type"
                    },
                    {
                        text: "By Total Subscription Duration (Loyalty)",
                        value: "By Subscription Duration"
                    },
                    {
                        text: "By No. of Clients",
                        value: "By Subscription No. of Clients"
                    },
                    {
                        text: "By No. of Referrals",
                        value: "By Subscriber Referrals"
                    },
                    {
                        text: "By Wallet Amount",
                        value: "By Subscriber Wallet Amount"
                    },
                    {
                        text: "By No. of Applications",
                        value: "By Subscriber No. of Applications"
                    },
                    {
                        text: "By No. of Documents Stored",
                        value: "By Subscriber  Document Store"
                    },
                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == 'Clients') {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Home Country",
                        value: "By Client Home Country"
                    },
                    {
                        text: "By Visa Country (Destinations)",
                        value: "By Client Visa Country"
                    },
                    {
                        text: "By Age Group ",
                        value: "By Client Age Group"
                    },
                    {
                        text: "By Application Type",
                        value: "By Client Application Type"
                    },
                    {
                        text: " By Total No. of Applications (Loyalty)",
                        value: "By Client Total No. of Applications"
                    },
                    {
                        text: "By No. of Dependents",
                        value: "By No. of Dependents"
                    },
                    {
                        text: "By Payment Mode",
                        value: "By Client Payment Mode"
                    },
                    {
                        text: "By Outstanding Payments Amount",
                        value: "By Client Payments Amount"
                    },
                    {
                        text: "By Number of Documents Stored",
                        value: "By Client Number of Documents Stored"
                    }
                    // , {
                    //     text: "No. of Clients",
                    //     value: "By Client No. of Clients"
                    // }

                ];
                var subID = $('#subscriberName').val() || '';
                fetchClientVisaChartFilters(subID, "{{ route('subscribersReport') }}").always(function () {
                    var filters = langArray.slice();
                    appendAvailableClientVisaFilters(filters, 'admin');
                    filters.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                    });
                });
            } else if (selectValue == "Applications") {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Visa Country",
                        value: "By Application Visa Country"
                    },
                    {
                        text: "By Application Country",
                        value: "By Application Country"
                    },
                    {
                        text: "By Application Type",
                        value: "By Application Type"
                    },
                    {
                        text: "By Application Status",
                        value: "By Application Status"
                    },
                    {
                        text: "By Application Counts By No. of Dependants",
                        value: "ByApplicationCountsByDependants"
                    },
                    {
                        text: "By Payment Mode",
                        value: "By Application Payment Mode"
                    },
                    {
                        text: "By Outstanding Payments Amount",
                        value: "By Outstanding Payments Amount"
                    },
                    {
                        text: "By Number of Documents Stored",
                        value: "By Number of Application Documents Stored"
                    },
                    {
                        text: "By No. of Applications",
                        value: "By No. per Application"
                    },

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });

            } else if (selectValue == "Documents") {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Application (top 20)",
                        value: "By Application  (top 20)  Docs"
                    },
                    {
                        text: "By Subscriber (Top 20)",
                        value: "By Subscriber  (top 20)  Docs"
                    },
                    {
                        text: "By Size (Top 50)",
                        value: "By Docs Size (Top 50)"
                    },
                    {
                        text: "By Filetype",
                        value: "By Document Filetype"
                    },
                    {
                        text: "By Year",
                        value: "ByDocumentYear"
                    },
                    {
                        text: "By Timeline (Duration)",
                        value: "ByDocumentTimeline(Duration)"
                    },

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });

            } else if (selectValue == "Users") {

                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Role",
                        value: "By User Role"
                    },
                    {
                        text: "By Age Group",
                        value: "By User Age Group"
                    },
                    {
                        text: "By Gender",
                        value: "By User Gender"
                    },
                    {
                        text: "By Application Processed",
                        value: "By User Application Processed"
                    },

                    {
                        text: "By Meeting Notes",
                        value: "By User Meeting Notes"
                    },
                    {
                        text: "By Mode of Communication",
                        value: "By User Mode of Communication"
                    },
                    {
                        text: "By No. of Messages",
                        value: "By User No. of Messages"
                    },
                    {
                        text: "No. of Users ",
                        value: "No. of Users"
                    },


                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == "Invoices") {

                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    }, {
                        text: "By Amount",
                        value: "By Invoice Amount"
                    },
                    {
                        text: "By Invoice Type",
                        value: "By Invoice Type"
                    },
                    {
                        text: "By Client Country",
                        value: "By Invoice Client Country"
                    },
                    {
                        text:'By Invoice Visa Country',
                        value:'By Invoice Visa Country'
                    },
                    {
                        text: "By Application Types",
                        value: "By Application_Types"
                    },
                    {
                        text: "By Services Offered",
                        value: "By Invoice Services Offered"
                    }

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == "Payments") {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Payment Mode",
                        value: "By Payment Mode"
                    },
                    {
                        text: "By Payment Amount ",
                        value: "By Payment Mode Payment Amount"
                    },
                    {
                        text: " By Payment Status (Raised, Partially_Paid, Fully_Paid, Cancelled, Unpaid)",
                        value: " By Payment Status (Raised, Partially_Paid, Fully_Paid, Cancelled, Unpaid)"
                    },
                    {
                        text: "By Client Country",
                        value: "By Payment Client Country"
                    },
                    {
                        text: "By Visa Country",
                        value: "By Payment Visa Country"
                    },
                    {
                        text: "By Application Type",
                        value: "By Payemnt Application Type"
                    }
                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == "Communications") {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By No. of Messages",
                        value: "By Communication No. of Messages"
                    },
                    {
                        text: " By No. of Meeting Notes",
                        value: "By No. of Communication Meeting Notes"
                    },
                    {
                        text: "By Type ",
                        value: "By Communication Type"
                    },
                    {
                        text: "By Meeting Note Type ",
                        value: "By Communication Meeting Note Type"
                    },
                    {
                        text: "By No. of Messages Sent by User",
                        value: "By No. of Communication Messages Sent by User"
                    }

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == "Referrals") {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By No. of Subscribers",
                        value: "By No. of Subscribers Referred"
                    },

                    {
                        text: "By Subscribed Plan",
                        value: "By Referral Subscribed Plan"
                    },
                    {
                        text: "Gross Report (Group) By Year",
                        value: "Gross Report (Group) By Year"
                    }
                ];
                if (user_type === "Subscriber") {
                    // Filter out the item with the value "By No. of Subscribers"
                    langArray = langArray.filter(function(item) {
                        return item.value !== "By No. of Subscribers";
                    });
                }

                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == "Wallet") {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Wallet Amount",
                        value: "By Wallet Amount"
                    },
                    {
                        text: "By No. of Wallet Transactions",
                        value: "By No. of Wallet Transactions"
                    },
                    {
                        text: "Total No. of Transactions",
                        value: "Total No. of Transactions"
                    }
                ];
                if (user_type === "Subscriber" || $('#subscriberName').val()) {

                    // Filter out the item with the value "By No. of Subscribers"
                    langArray = langArray.filter(function(item) {
                        return item.value !== "By Wallet Amount";
                    });
                }
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });

            } else if (selectValue == "Affiliates") {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By No. of Subscribers Referred",
                        value: "By Affiliates No. of Subscribers Referred"
                    },
                    {
                        text: "By Amount of Commissions Earnt",
                        value: "By Amount of Affiliates Commissions Earnt"
                    },
                    {
                        text: "By Country",
                        value: "By Affiliate Country"
                    },

                    {
                        text: "By Subscribed Plan",
                        value: "By Affiliate Subscribed Plan"
                    },
                    {
                        text: "By Current Wallet Credits",
                        value: "By Affiliate Current Wallet Credits"
                    }

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == "Support Tickets") {
                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Ticket Type",
                        value: "By Ticket Type"
                    },
                    {
                        text: "By Time",
                        value: "By Time"
                    },
                    {
                        text: "By Time Taken",
                        value: "By Support Time Taken"
                    },

                    {
                        text: "By Support Staff",
                        value: "By Support Staff"
                    },
                    {
                        text: "By Support Staff Name ",
                        value: "By Support Staff Name"
                    }

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == "Demo Requests") {

                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: " By Status",
                        value: "By Demo Request Status"
                    },
                    {
                        text: "By Country ",
                        value: "By Country Demo Requests"
                    },
                    {
                        text: "By Timeline ",
                        value: "By Timeline  No. of Demo Requests",
                    },
                    {
                        text: " By Time Taken ",
                        value: "By Demo Request Time Taken",
                    }, ,
                    // {
                    //     text: "By Support Staff ",
                    //     value: "By Demo Support Staff"
                    // }

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            } else if (selectValue == "Activity Log") {

                var langArray = [{
                        text: "Select Attribute",
                        value: ""
                    },
                    {
                        text: "By Activity Type",
                        value: "By Activity Type"
                    },
                    {
                        text: "Total No. of Activities By Time ",
                        value: "By Total Number No. of Activities By Time"
                    },
                    {
                        text: "Top 10 Subscribers",
                        value: "By Top 10 Activity Subscribers"
                    },

                ];
                langArray.forEach((text) => {
                    let option_elem = document.createElement('option');

                    // Add index to option_elem
                    option_elem.value = text.value;

                    // Add element HTML
                    option_elem.textContent = text.text;

                    // Append option_elem to select_elem
                    select_elem.appendChild(option_elem);
                });
            }

            // var s = $('#subscriberName').val();
            // if (s && $('#selectAttribute').val() == 'Activity Log') {

            //     $("#filters option[value='By Top 10 Subscribers']").remove();
            // } else {
            //     $('<option value="By Top 10 Subscribers">By Top 10 Subscribers</option>').insertAfter(
            //         '#filters option:nth-child(3)');

            // }


        }

        function onChangeFilter(select) {
            const selectedValue = select.value;
            resetFourthColFilter()
            console.log(`Selected Filter: ${selectedValue}`);
            // Perform your logic here, e.g., make an API call, update UI, etc.
            if (selectedValue == 'By Country' || selectedValue == 'By Client Home Country' || selectedValue ==
                'By Client Visa Country' || selectedValue == 'By Invoice Client Country' || selectedValue ==
                'By Affiliate Country' || selectedValue == 'By Country Demo Requests') {
                // const elements = document.getElementById('fourth-filter'); // Get ele
                // const element = document.getElementById('countries');
                // const label = document.getElementById('fourth-filter-country');
                // elements.style.display = 'block';
                // element.style.display = 'block';
                // label.textContent = 'Select Country';
                // $('#date-range').addClass('mt-3')
            } else if (selectedValue == 'By User Age Group') {
                const elements = document.getElementById('fourth-filter'); // Get ele
                const label = document.getElementById('fourth-filter-country');
                const element = document.getElementById('age-group');
                elements.style.display = 'block';
                element.style.display = 'block';
                label.textContent = 'Select Age Group';
                // $('#date-range').addClass('mt-3')

            } else if (selectedValue == 'By Client Payments Amount' || selectedValue == 'By Invoice Amount' ||
                selectedValue == "By Wallet Amount" || selectedValue == 'By Payment Mode Payment Amount') {
                const elements = document.getElementById('fourth-filter'); // Get ele
                const label = document.getElementById('fourth-filter-country');
                const element = document.getElementById('price-range');
                elements.style.display = 'block';
                element.style.display = 'block';
                label.textContent = 'Select Price ';
            } else if (selectedValue == 'By User Role' ) {
                const elements = document.getElementById('fourth-filter'); // Get ele
                const label = document.getElementById('fourth-filter-country');
                const element = document.getElementById('role');
                elements.style.display = 'block';
                element.style.display = 'block';
                label.textContent = 'Select Role ';
                // $('#date-range').addClass('mt-3')
            } else if (selectedValue == 'By Payment Mode') {
                const elements = document.getElementById('fourth-filter'); // Get ele
                const label = document.getElementById('fourth-filter-country');
                const element = document.getElementById('payment_mode');
                elements.style.display = 'block';
                element.style.display = 'block';
                label.textContent = 'Select Role ';
                // $('#date-range').addClass('mt-3')
            } else if (selectedValue == 'By Support Staff Name' || selectedValue == 'By Demo Support Staff') {
                const elements = document.getElementById('fourth-filter'); // Get ele
                const label = document.getElementById('fourth-filter-country');
                const element = document.getElementById('username');
                elements.style.display = 'block';
                element.style.display = 'block';
                label.textContent = 'Select Role ';
                // $('#date-range').addClass('mt-3')
            }

        }


        function onClickGetReport() {


            var selectedFilter = $('#filters').val();
            console.log(selectedFilter);
            var subID = $('#subscriberName').val();
            var selectedFilterTitle = $('#filters option:selected').text();
            var affiID = $('#affiliateName').val();
            var country = $('#countries').val();
            var age = $('#age-group').val();
            var price = $('#price-range').val();
            var role = $('#role').val();
            var invoiceType = $('#invoiceType').val();
            var paymentMode = $('#payment_mode').val();
            var username = $('#username').val();

            var selectedAttribute = $('#selectAttribute').val()
                ? $('#selectAttribute').val()
                : $('#filters').val();

            var selectedDate = $('#custom_date_picker').val();

            var dateForTitle = selectedDate;
            var chartType = $('#chartType').val();

            var dateRangeParts = selectedDate.split(' - ');
            var startDate = (dateRangeParts[0] || '').trim();
            var endDate = (dateRangeParts[1] || '').trim();

            let hasError = false;
            let title = selectedAttribute + ' : ' + selectedFilterTitle + (!selectedFilterTitle.includes('By Timeline (Duration)') && !selectedFilterTitle.includes('By Year') ? ' (' + startDate + ' - ' + endDate + ')' : '');

            if (!selectedAttribute) {
                // alert("Please Select Attribute");
                $('#selectAttribute').addClass("error");
                hasError = true;

            } else {
                $('#selectAttribute').removeClass("error")

            }
            if (!selectedFilter) {
                $('#filters').addClass("error");
                hasError = true;

            } else {
                $('#filters').removeClass("error")

            }
            if (!chartType) {
                $('#chartType').addClass("error");
                hasError = true;


            } else {
                $('#chartType').removeClass("error")

            }

            if (hasError) {
                return;
            }

            $("#downloadPdf").show();
            // const dynamicColors = Array.from({
            //     length: 255
            // }, () => {
            //     const r = Math.floor(Math.random() * 256); // Random red component
            //     const g = Math.floor(Math.random() * 256); // Random green component
            //     const b = Math.floor(Math.random() * 256); // Random blue component
            //     const a = 1; // Random alpha component between 0 and 1, rounded to 2 decimal places
            //     return `rgba(${r},${g},${b},${a})`;
            // });
            // const dynamicColorsSent = Array.from({
            //     length: 255
            // }, () => {
            //     const r = Math.floor(Math.random() * 256); // Random red component
            //     const g = Math.floor(Math.random() * 256); // Random green component
            //     const b = Math.floor(Math.random() * 256); // Random blue component
            //     const a = 1; // Random alpha component between 0 and 1, rounded to 2 decimal places
            //     return `rgba(${r},${g},${b},${a})`;
            // });
            // const dynamicColorsReceive = Array.from({
            //     length: 255
            // }, () => {
            //     const r = Math.floor(Math.random() * 256); // Random red component
            //     const g = Math.floor(Math.random() * 256); // Random green component
            //     const b = Math.floor(Math.random() * 256); // Random blue component
            //     const a = 1; // Random alpha component between 0 and 1, rounded to 2 decimal places
            //     return `rgba(${r},${g},${b},${a})`;
            // });

            function formatClientApplicationLabel(clientName, applicationName) {
                if (!clientName) {
                    return applicationName || '';
                }

                const parts = String(clientName).trim().split(/\s+/);
                let shortName = parts[0];

                if (parts.length > 1) {
                    shortName += ' ' + parts[parts.length - 1].charAt(0).toUpperCase() + '.';
                }

                if (applicationName) {
                    return shortName + ' - ' + applicationName;
                }

                return shortName;
            }

            function buildClientDocumentChartLabel(row) {
                if (row && row.chart_label) {
                    return row.chart_label;
                }

                const applicationName = row.application_name
                    || (row.application_id ? 'Application ' + row.application_id : '');

                return formatClientApplicationLabel(row.client_name, applicationName);
            }

            // Function to generate distinct HSL colors
            function generateDistinctColors(count) {
                const colors = [];
                for (let i = 0; i < count; i++) {
                    if (i < ANALYTICS_INDICATOR_PALETTE.length) {
                        colors.push(ANALYTICS_INDICATOR_PALETTE[i]);
                    } else {
                        const hue = Math.round((i * 137.508) % 360);
                        colors.push(`hsl(${hue}, 72%, 45%)`);
                    }
                }
                return colors;
            }

            // Later in your chart code:
            // const dynamicColors = generateDistinctColors(labels.length); // <- generate only what you need



            var clientVisaChartDefinition = findClientVisaChartDefinition(selectedFilter, 'admin');
            if (clientVisaChartDefinition) {
                renderClientVisaDetailChart({
                    reportUrl: "{{ route('subscribersReport') }}",
                    apiType: clientVisaChartDefinition.apiType,
                    labelField: clientVisaChartDefinition.labelField,
                    subId: subID,
                    country: country,
                    startDate: startDate,
                    endDate: endDate,
                    title: title,
                    chartType: chartType,
                    checkIfDataIsEmpty: checkIfDataIsEmpty,
                    sortChartResult: sortChartResult,
                    generateDistinctColors: generateDistinctColors,
                });
                return;
            }

            if (selectedFilter == "By Subscriber Country") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscriberCountryChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return

                        }

                        var result = data.data;
                        console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                            if(currentElement.No_of_Subscribers !== 0){

                            labels.push(currentElement.country);
                            numbers.push(currentElement.No_of_Subscribers);
                            }
                            
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {

                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors, // Ensure enough colors for all datasets

                                }],
                                labels: labels,
                            },
                            options: {
                                responsive: false,
                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },

                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        // Add a title
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });

            } else if (selectedFilter == "By Subscriber Category") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                function decodeHtmlEntities(str) {
                    const txt = document.createElement("textarea");
                    txt.innerHTML = str;
                    return txt.value;
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscriberCategoryChart',
                        subid: subID,
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                            if(currentElement.userCount !== 0){

                            labels.push(decodeHtmlEntities(currentElement.category));
                            numbers.push(currentElement.userCount);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });






                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Sub Category") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'subCategoryChart',
                        subid: subID,
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                            if(currentElement.userCount !== 0){

                            labels.push(currentElement.sub_category);
                            numbers.push(currentElement.userCount);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });






                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscriber Plan Type") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscriberplanTypeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                            if(currentElement.userCount !== 0){

                            labels.push(currentElement.membership_type);
                            numbers.push(currentElement.userCount);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscription Duration") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bysubscriptionDurationChart',
                        subid: subID,
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                            if(currentElement.total_subscribers !== 0){

                            labels.push(currentElement.duration);
                            numbers.push(currentElement.total_subscribers);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscription No. of Clients") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscriberNoOfClientsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                            if(currentElement.clients_count !== 0){
                                labels.push(currentElement.membership_type);
                                numbers.push(currentElement.clients_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscriber Referrals") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscriberReferrals',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        console.log(data.data);
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];

                        result.forEach(function(currentElement, index) {
                            if(currentElement.referrals_count !== 0){

                            labels.push(currentElement.membership_type);
                            numbers.push(currentElement.referrals_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscriber Wallet Amount") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscriberWalletAmountChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.wallet_balance !== 0){

                            labels.push(currentElement.membership_type);
                            numbers.push(currentElement.wallet_balance);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscriber No. of Applications") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscriberNoOfApplicationChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.applications_count !== 0){
                            labels.push(currentElement.membership_type);
                            numbers.push(currentElement.applications_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscriber  Document Store") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscribeDocumentStore',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.docs_count !== 0){


                            labels.push(currentElement.membership_type);
                            numbers.push(currentElement.docs_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Home Country") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byClientHomeCountry',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                            if(currentElement.No_of_clients !== 0){

                                labels.push(currentElement.nationality);
                                console.log(currentElement.nationality);

                                numbers.push(currentElement.No_of_clients);
                                console.log(currentElement.No_of_clients);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);
                        const datasets = [{
                            label: selectedAttribute + ' ' + selectedFilter,
                            data: numbers,
                            backgroundColor: dynamicColors,
                            borderWidth: 1
                        }];

                        new Chart(ctx, {
                            type: chartType,
                            // data: {
                            //     labels: labels,
                            //     datasets: [{
                            //         label: selectedAttribute + ' ' + selectedFilter,
                            //         data: numbers,
                            //         borderWidth: 1,
                            //         backgroundColor: dynamicColors,
                            //     }]
                            // },
                            data: { labels: labels, datasets },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Visa Country") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byVisaCountryClient',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        
                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.No_of_clients !== 0){

                            labels.push(currentElement.visa_country);

                            numbers.push(currentElement.total_clients);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);
                        const datasets = [{
                            label: selectedAttribute + ' ' + selectedFilter,
                            data: numbers,
                            backgroundColor: dynamicColors,
                            borderWidth: 1
                        }];

                        new Chart(ctx, {
                            type: chartType,
                            // data: {
                            //     labels: labels,
                            //     datasets: [{
                            //         label: selectedAttribute + ' ' + selectedFilter,
                            //         data: numbers,
                            //         borderWidth: 1,
                            //         backgroundColor: dynamicColors,
                            //     }]
                            // },
                              data: { labels: labels, datasets },

                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Age Group") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byAgeGroupClient',
                        subid: subID,
                        age: age,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.count !== 0){

                            labels.push(currentElement.age_group);

                            numbers.push(currentElement.count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);
                        const datasets = [{
                            label: selectedAttribute + ' ' + selectedFilter,
                            data: numbers,
                            backgroundColor: dynamicColors,
                            borderWidth: 1
                        }];

                        new Chart(ctx, {
                            _formatAgeGroupChart: true,
                            type: chartType,
                            // data: {
                            //     labels: labels,
                            //     datasets: [{
                            //         label: '',
                            //         data: numbers,
                            //         borderWidth: 1,
                            //         backgroundColor: dynamicColors,
                            //     }]
                            // }
                            // ,
                            data: { labels: labels, datasets },
                            options: {
                                responsive: false, // Makes the chart responsive
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Application Type") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byClientApplicationType',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.applications_count !== 0){

                            labels.push(currentElement.name);

                            numbers.push(currentElement.applications_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Total No. of Applications") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byTotalNoOfApplications',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data; // Extract the data array from the response
                        var labels = [];
                        var subscriberNames = [];
                        var clientNames = [];
                        var numbers = [];
                        var applicationNames = []; // New array for storing application names

                        // Loop through the result and push values to respective arrays
                        result.forEach(function(currentElement) {
                            labels.push(currentElement.client_name); // Collect client names for labels
                            subscriberNames.push(currentElement
                                .subscriber_name); // Collect subscriber names
                            clientNames.push(currentElement.client_name); // Collect client names
                            numbers.push(currentElement
                            
                                .no_of_applications); // Collect number of applications
                        
                            applicationNames.push(currentElement
                                .application_names); // Collect application names
                        });



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            title: function(tooltipItems) {
                                                // Display subscriber name in the tooltip title
                                                return (
                                                    'Subscriber: ' +
                                                    subscriberNames[tooltipItems[0].dataIndex]
                                                );
                                            },
                                            label: function(tooltipItem) {
                                                var index = tooltipItem
                                                    .dataIndex; // Get the index of the current item
                                                var applicationNames = result[index]
                                                    .application_names.split(
                                                        ','
                                                    ); // Split application names into an array

                                                // Build the tooltip text as an array for multi-line support
                                                var tooltipText = [
                                                    'Client: ' + tooltipItem
                                                    .label, // First line: Client name
                                                    'Applications: ' + tooltipItem
                                                    .raw, // Second line: Application count
                                                    'Names:' // Third line: Header for application names
                                                ];

                                                // Add each application name as a separate line
                                                applicationNames.forEach(app => {
                                                    tooltipText.push('- ' + app
                                                        .trim()
                                                    ); // Format with a list-style prefix
                                                });

                                                return tooltipText; // Return an array for multi-line display
                                            },
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Payment Mode") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",
                    data: {
                        type: 'byPaymentModeClientChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.no_of_applications !== 0){

                            labels.push(currentElement.payment_mode);

                            numbers.push(currentElement.no_of_applications);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);
                        const datasets = [{
                            label: selectedAttribute + ' ' + selectedFilter,
                            data: numbers,
                            backgroundColor: dynamicColors,
                            borderWidth: 1
                        }];

                        new Chart(ctx, {
                            type: chartType,
                            // data: {
                            //     labels: labels,
                            //     datasets: [{
                            //         label: selectedAttribute + ' ' + selectedFilter,
                            //         data: numbers,
                            //         borderWidth: 1,
                            //         backgroundColor: dynamicColors,
                            //     }]
                            // },
                              data: { labels: labels, datasets },

                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Payments Amount") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byOutstandingPaymentsAmountChart',
                        subid: subID,
                        price: price,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_invoices !== 0){

                            labels.push(currentElement.amount_range);

                            numbers.push(currentElement.number_of_invoices);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Number of Documents Stored") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byClientNumberofDocumentsStoredChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.no_of_docs !== 0){

                            labels.push(buildClientDocumentChartLabel(currentElement));

                            numbers.push(currentElement.no_of_docs);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client No. of Clients") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byClientNoOfClientsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        console.log(1);
                        var result = data;
                        console.log(result);
                        var labels = [];
                        var numbers = [];
                        // result.forEach(function(currentElement, index) {
                        labels.push('Total Client');
                        numbers.push(result.data);

                        // })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Application Visa Country") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byApplicationVisaCountryChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.application_count !== 0){

                            labels.push(currentElement.country);

                            numbers.push(currentElement.application_count);
                            }

                        })


                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Application Country") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byApplicationCountryChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.application_count !== 0){

                            labels.push(currentElement.country);

                            numbers.push(currentElement.application_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Application Status") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byApplicationStatus',
                        subid: subID,
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData();
                            return;
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];

                        result.forEach(function(currentElement) {
                            labels.push(currentElement.application_status);
                            numbers.push(currentElement.status_count);
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20,
                                            weight: 800
                                        },
                                        padding: {
                                            bottom: 50
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            padding: 30
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return `No. Of Applications: ${dataValue}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const dataValue = tooltipItem[0].raw || '';
                                                const total = tooltipItem[0].dataset.data.reduce((acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100).toFixed(1);
                                                return ['Percent Value: ' + percentage + '%'];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => value,
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault();
                    let downloadButton = this;
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true;
                    html2canvas(document.getElementById('myChart')).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        const { jsPDF } = window.jspdf;
                        const pdf = new jsPDF({ orientation: 'portrait', unit: 'px', format: 'a4' });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" + dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30);
                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                    }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading');
                            downloadButton.disabled = false;
                        }, 1000);
                    });
                });
            } else if (selectedFilter == "By Application Type") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byApplicationType',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        console.log(result)
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_clients !== 0){
                            labels.push(currentElement.application_name);

                            numbers.push(currentElement.number_of_clients);
                            }

                        })

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                console.log(tooltipItem);
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset
                                                    .label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data
                                                    .reduce((
                                                        acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "ByApplicationCountsByDependants") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byApplicationCountsByDependantsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];

                        result.forEach(function(currentElement) {
                            labels.push(currentElement.dependant_bucket);
                            numbers.push(currentElement.application_count);
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: applicantsChartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Application Payment Mode") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byApplicationPaymentModeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.no_of_applications !== 0){

                            labels.push(currentElement.payment_mode);

                            numbers.push(currentElement.no_of_applications);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Client Application Type") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byApplicationTypeClient',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_clients !== 0){

                            labels.push(currentElement.application_name);

                            numbers.push(currentElement.number_of_clients);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset
                                                    .label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data
                                                    .reduce((
                                                        acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By No. per Application") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byNoofApplicantsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data;
                        var labels = [];
                        var numbers = [];
                        const monthNames = ["January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December"
                        ];

                        labels.push('Total Application');

                        numbers.push(result.data);



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Outstanding Payments Amount") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byOutstandingAplicationPaymentsAmountChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement) {
                            const outstanding = Number(currentElement.amount_to_pay) || 0;
                            if (outstanding > 0) {
                                labels.push(currentElement.application_name);
                                numbers.push(outstanding);
                            }
                        });
                        const totalOutstanding = numbers.reduce(function(total, amount) {
                            return total + amount;
                        }, 0);



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: '',
                                    data: numbers,
                                    borderWidth: 2,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return Number(value).toLocaleString(undefined, {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                });
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20,
                                            weight: 800
                                        },
                                        padding: {
                                            bottom: 50
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            padding: 30
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = Number(tooltipItem.raw) || 0;
                                                return `Amount: ${dataValue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                            },
                                            afterLabel: function(tooltipItem) {
                                                const dataValue = Number(tooltipItem.raw) || 0;
                                                if (totalOutstanding <= 0) {
                                                    return 'Percent Value: 0.00%';
                                                }

                                                const percentage = (dataValue / totalOutstanding) * 100;
                                                const formattedPercentage = percentage > 0 && percentage < 0.01
                                                    ? '<0.01'
                                                    : percentage.toFixed(2);

                                                return `Percent Value: ${formattedPercentage}%`;
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => Number(value).toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }),
                                        font: {
                                            size: 12,
                                            weight: 600
                                        },
                                        color: 'black',
                                        offset: 8
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Number of Application Documents Stored") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byNumberOfApplicationDocumentStoreChart',
                        subid: subID,
                        start: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        //    console.log(data.data);
                        //    var numbers = [] ;
                        //     const labels = data.map(item => item.application_name); // Labels for the chart
                        //     const docsCounts = data.map(item => item.docs_count);   // Data for the chart
                        var result = data;
                        var labels = [];
                        var numbers = [];
                        const monthNames = ["January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December"
                        ];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.docs_count !== 0){
                            labels.push(currentElement.application_name);

                            numbers.push(currentElement.docs_count);
                            }

                        })


                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return `Applications: ${dataValue}`;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const clientName = tooltipItem[0].label || '';
                                                return `Client: ${clientName}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const dataValue = tooltipItem[0].raw || '';
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    `Applications: ${dataValue}`,
                                                    `Percentage: ${percentage}%`
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Application  (top 20)  Docs") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byDocumentNoofApplicationsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data;
                        var labels = [];
                        var numbers = [];
                        var noOfDocs = [];
                        var applicationNames = [];

                        // Extract data for the chart
                        result.forEach(function(currentElement, index) {
                        if(currentElement.no_of_applications !== 0){
                            labels.push(currentElement.client_name);
                            numbers.push(currentElement.no_of_applications);
                            }
                            noOfDocs.push(currentElement.no_of_docs);
                            applicationNames.push(currentElement.application_names);
                        });



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Display the number of applications
                                                return `Applications: ${tooltipItem.raw}`;
                                            },
                                            afterLabel: function(tooltipItem) {
                                                // Fetch additional details
                                                const index = tooltipItem.dataIndex;
                                                const docs = noOfDocs[index];
                                                const apps = applicationNames[index];

                                                // Format the tooltip with no_of_docs and application_names
                                                return [
                                                    `Number of Docs: ${docs}`,
                                                    `Applications: ${apps}`
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscriber  (top 20)  Docs") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscriberTopDocsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data;
                        var labels = [];
                        var numbers = [];

                        result.forEach(function(currentElement, index) {
                        if(currentElement.no_of_docs !== 0){
                            labels.push(currentElement.user_name);

                            numbers.push(currentElement.no_of_docs);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Docs Size (Top 50)") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byFileSizeDocsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data;
                        var labels = [];
                        var numbers = [];

                        // Extract labels (document names) and numbers (file sizes)
                        result.forEach(function(currentElement) {
                        if(currentElement.file_size !== 0){
                            labels.push(currentElement.docs_name); // Document names for labels
                            numbers.push(currentElement
                            
                                .file_size); // Use raw file size for chart data (in bytes)
                                }
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            _formatByteValues: true,
                            type: chartType, // Specify chart type (e.g., 'bar', 'line')
                            data: {
                                labels: labels, // Use the processed labels array (user names)
                                datasets: [{
                                    label: selectedAttribute + ' ' +
                                        selectedFilter, // Chart label
                                    data: numbers, // Raw file sizes for data (in bytes)
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors, // Dynamic colors for bars/points
                                }]
                            },
                            options: {
                                responsive: false, // Makes the chart responsive
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            // Convert raw value to formatted size for the chart label
                                            let formattedValue = '';
                                            if (value < 1024) {
                                                formattedValue = value + ' B';
                                            } else if (value < 1048576) {
                                                formattedValue = (value / 1024).toFixed(2) + ' KB';
                                            } else if (value < 1073741824) {
                                                formattedValue = (value / 1048576).toFixed(2) +
                                                    ' MB';
                                            } else {
                                                formattedValue = (value / 1073741824).toFixed(2) +
                                                    ' GB';
                                            }
                                            return formattedValue; // Return formatted size for data label
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Document Filetype") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byFileTypeDocsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data;
                        var labels = [];
                        var numbers = [];

                        // Extract labels (user names) and numbers (file sizes)
                        result.forEach(function(currentElement) {
                        if(currentElement.total_files !== 0){
                            labels.push(currentElement.doc_type); // User names for labels
                            numbers.push(currentElement
                            
                                .total_files); // Use raw file size for chart data (in bytes)
                        }
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            _formatFileTypeChart: true,
                            type: chartType, // Specify chart type (e.g., 'bar', 'line')
                            data: {
                                labels: labels, // Use the processed labels array (user names)
                                datasets: [{
                                    label: selectedAttribute + ' ' +
                                        selectedFilter, // Chart label
                                    data: numbers, // Raw file sizes for data (in bytes)
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors, // Dynamic colors for bars/points
                                }]
                            },
                            options: {
                                responsive: false, // Makes the chart responsive
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',

                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "ByDocumentYear") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byDocumentYear',
                        subid: subID,
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                            if (currentElement.count !== 0) {
                                labels.push(currentElement.year);
                                numbers.push(currentElement.count);
                            }
                        })

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20,
                                            weight: 800
                                        },
                                        padding: {
                                            bottom: 50
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            padding: 30
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                const dataValue = tooltipItem[0].raw || '';
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault();
                    let downloadButton = this;
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true;
                    html2canvas(document.getElementById('myChart')).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30);
                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                    }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading');
                            downloadButton.disabled = false;
                        }, 1000);
                    });
                });
            } else if (selectedFilter == "ByDocumentTimeline(Duration)") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byDocumentTimeline(Duration)',
                        subid: subID,
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var timelineChartData = buildTimelineChartData(result);
                        var labels = timelineChartData.labels;
                        var numbers = timelineChartData.numbers;

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20,
                                            weight: 800
                                        },
                                        padding: {
                                            bottom: 50
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            padding: 30
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                const dataValue = tooltipItem[0].raw || '';
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault();
                    let downloadButton = this;
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true;
                    html2canvas(document.getElementById('myChart')).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30);
                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                    }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading');
                            downloadButton.disabled = false;
                        }, 1000);
                    });
                });
            } else if (selectedFilter == "By User Role") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byUserRoleChart',
                        subid: subID,
                        role: role,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.users !== 0){

                            labels.push(currentElement.designation);

                            numbers.push(currentElement.users);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By User Age Group") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byUserAgeGroupChart',
                        age: age,
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.count !== 0){

                            labels.push(currentElement.age_group);

                            numbers.push(currentElement.count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            _formatAgeGroupChart: true,
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By User Gender") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byUserGenderChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.count !== 0){

                            labels.push(currentElement.age_group);

                            numbers.push(currentElement.count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By User Application Processed") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byUserApplicationProcessedChart',
                        subid: subID,
                        role: role,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.application_count !== 0){

                            labels.push(currentElement.user_name);

                            numbers.push(currentElement.application_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By User Meeting Notes") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byUserMeetingNotesChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.discussion !== 0){

                            labels.push(currentElement.user_name);

                            numbers.push(currentElement.discussion);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                ////console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By User Mode of Communication") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byUserModeofCommunicationChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        console.log('hi');
                        var result = data.data;
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_users !== 0){
                            labels.push(currentElement.communication_type);

                            numbers.push(currentElement.total_users);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                ////console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By User No. of Messages") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byUserNoofMessagesChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;


                        const labels = Object.values(result).map(item => item.name);
                        const receivedData = Object.values(result).map(item => item.received);
                        const sendData = Object.values(result).map(item => parseInt(item.send));



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                        label: 'Received',
                                        data: receivedData,
                                        backgroundColor: dynamicColorsReceive,
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Sent',
                                        data: sendData,
                                        backgroundColor: dynamicColorsSent,
                                        borderColor: 'rgba(153, 102, 255, 1)',
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: false,

                                scales: {
                                    x: {
                                        stacked: true
                                    },
                                    y: {
                                        stacked: true,
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },

                                    tooltip: {
                                        enabled: true, // Enable tooltips for all types, customize visibility within callbacks

                                        callbacks: {
                                            label: function(tooltipItem) {
                                                if (chartType === 'bar' || chartType === 'line') {
                                                    const dataValue = tooltipItem.raw || '';
                                                    return ``;
                                                }
                                                // For other chart types, fall back to default behavior
                                                return '';
                                            },
                                            beforeBody: function(tooltipItems) {
                                                if (chartType === 'bar' || chartType === 'line') {
                                                    const datasetLabel = tooltipItems[0].dataset
                                                        .label || '';
                                                    const dataLabel = tooltipItems[0].label || '';
                                                    return `-----------------`;
                                                }
                                                // For other chart types, no additional text
                                                return `-----------------`;
                                            },
                                            afterBody: function(tooltipItems) {
                                                if (chartType === 'bar' || chartType === 'line') {
                                                    const dataValue = tooltipItems[0].raw || '';
                                                    const total = tooltipItems[0].dataset.data
                                                        .reduce((acc, val) => acc + val, 0);
                                                    const percentage = ((dataValue / total) * 100)
                                                        .toFixed(1);

                                                    return [
                                                        `Messages Sent: ${tooltipItems[0].raw}`,
                                                        `Messages Received: ${tooltipItems[0].parsed._stacks.y[0]}`,
                                                        `Percent Value: ${percentage}%`
                                                    ];
                                                }
                                                // For other chart types, no additional text
                                                if (tooltipItems[0].dataset.label == 'Received') {
                                                    tooltipItems[0].dataset.label =
                                                        'Messages Received: ';
                                                }
                                                if (tooltipItems[0].dataset.label == 'Sent') {
                                                    tooltipItems[0].dataset.label =
                                                        'Messages Sent: ';

                                                }
                                                return tooltipItems[0].dataset.label + ": " +
                                                    tooltipItems[0].raw;
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            // plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "No. of Users") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byUserChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }

                        console.log(1);
                        var result = data;
                        console.log(result);
                        var labels = [];
                        var numbers = [];
                        // result.forEach(function(currentElement, index) {
                        
                        labels.push('Total Users');
                        numbers.push(result.data);


                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By No. of Meetings") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> idBy No. of Messages Sent by User
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byNoOfMeetingsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_activities !== 0){
                            labels.push(currentElement.period);
                            numbers.push(currentElement.total_activities);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Meeting Notes Type") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> idBy No. of Messages Sent by User
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byMeetingNotesTypeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_communication !== 0){
                            labels.push(currentElement.communication_type);
                            numbers.push(currentElement.number_of_communication);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By No. of Messages Sent by User") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byNoOfMessagesSentByUserChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_communication !== 0){
                            labels.push(currentElement.user_id);
                            numbers.push(currentElement.number_of_communication);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Invoice Amount") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> idBy No. of Messages Sent by User
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byInvoiceAmountChart',
                        price: price,
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_invoices !== 0){
                            labels.push(currentElement.amount_range);
                            numbers.push(currentElement.number_of_invoices);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Get the index of the hovered data point
                                                const index = tooltipItem.dataIndex;

                                                // Get the current data point's total_amount_sum from result
                                                const totalAmountSum = result[index]
                                                    .total_amount_sum;

                                                // Return the custom tooltip with total_amount_sum
                                                return [
                                                    'Number of Invoices: ' + tooltipItem.raw,
                                                    'Total Amount Sum: ' + totalAmountSum
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Invoice Type") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> idBy No. of Messages Sent by User
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byInvoiceTypeChart',
                        price: price,
                        subid: subID,
                        invoiceType: invoiceType,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_invoices !== 0){
                            labels.push(currentElement.status);
                            numbers.push(currentElement.number_of_invoices);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Get the index of the hovered data point
                                                const index = tooltipItem.dataIndex;

                                                // Get the current data point's total_amount_sum from result
                                                const totalAmountSum = result[index]
                                                    .total_amount_sum;

                                                // Return the custom tooltip with total_amount_sum
                                                return [
                                                    'Number of Invoices: ' + tooltipItem.raw,
                                                    'Total Amount Sum: ' + totalAmountSum
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Payment Amount") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byPaymentAmountChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.application_count !== 0){
                            labels.push(currentElement.paymentAmount);
                            numbers.push(currentElement.application_count);
                            }

                        })

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Invoice Client Country") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byInvoiceClientCountryChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_invoices !== 0){
                            labels.push(currentElement.country);
                            numbers.push(currentElement.number_of_invoices);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Get the index of the hovered data point
                                                const index = tooltipItem.dataIndex;

                                                // Get the current data point's total_amount_sum from result
                                                const totalAmountSum = result[index]
                                                    .total_amount_sum;

                                                // Return the custom tooltip with total_amount_sum
                                                return [
                                                    'Number of Invoices: ' + tooltipItem.raw,
                                                    'Total Amount Sum: ' + totalAmountSum
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Invoice Visa Country") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byInvoiceVisaCountryChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_invoices !== 0){
                            labels.push(currentElement.to_country);
                            numbers.push(currentElement.number_of_invoices);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Get the index of the hovered data point
                                                const index = tooltipItem.dataIndex;

                                                // Get the current data point's total_amount_sum from result
                                                const totalAmountSum = result[index]
                                                    .total_amount_sum;

                                                // Return the custom tooltip with total_amount_sum
                                                return [
                                                    'Number of Invoices: ' + tooltipItem.raw,
                                                    'Total Amount Sum: ' + totalAmountSum
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Invoice Services Offered") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byInvoiceServiceOfferedChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_invoices !== 0){
                            labels.push(currentElement.detail);
                            numbers.push(currentElement.number_of_invoices);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Get the index of the hovered data point
                                                const index = tooltipItem.dataIndex;

                                                // Get the current data point's total_amount_sum from result
                                                const totalAmountSum = result[index]
                                                    .total_amount_sum;

                                                // Return the custom tooltip with total_amount_sum
                                                return [
                                                    'Number of Invoices: ' + tooltipItem.raw,
                                                    'Total Amount Sum: ' + totalAmountSum
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Application Types") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byInvoiceApplicationTypeChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_invoices !== 0){
                            labels.push(currentElement.detail);
                            numbers.push(currentElement.number_of_invoices);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Get the index of the hovered data point
                                                const index = tooltipItem.dataIndex;

                                                // Get the current data point's total_amount_sum from result
                                                const totalAmountSum = result[index]
                                                    .total_amount_sum;

                                                // Return the custom tooltip with total_amount_sum
                                                return [
                                                    'Number of Invoices: ' + tooltipItem.raw,
                                                    'Total Amount Sum: ' + totalAmountSum
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Payment Mode") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        payment_mode: paymentMode,
                        type: 'byPaymentModeChart',
                        subid: subID,

                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_payment !== 0){
                            labels.push(currentElement.payment_mode);
                            numbers.push(currentElement.number_of_payment);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);
                        const datasets = [{
                            label: selectedAttribute + ' ' + selectedFilter,
                            data: numbers,
                            backgroundColor: dynamicColors,
                            borderWidth: 1
                        }];

                        new Chart(ctx, {
                            type: chartType,
                            // data: {
                            //     labels: labels,
                            //     datasets: [{
                            //         label: selectedAttribute + ' ' + selectedFilter,
                            //         data: numbers,
                            //         borderWidth: 1,
                            //         backgroundColor: dynamicColors,
                            //     }]
                            // }
                            // ,
                            data: { labels: labels, datasets },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Payment Mode Payment Amount") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        payment_mode: paymentMode,
                        type: 'byPaymentModePaymentAmountChart',
                        subid: subID,
                        price:price,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_invoices !== 0){
                            labels.push(currentElement.amount_range);
                            numbers.push(currentElement.number_of_invoices);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Payment Client Country") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        payment_mode: paymentMode,
                        type: 'byPaymentClientCountryChart',
                        subid: subID,
                        price:price,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_payment !== 0){
                            labels.push(currentElement.country);
                            numbers.push(currentElement.number_of_payment);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Payment Visa Country") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        payment_mode: paymentMode,
                        type: 'byPaymentVisaCountryChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_payment !== 0){
                            labels.push(currentElement.country);
                            numbers.push(currentElement.number_of_payment);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            }else if (selectedFilter == "By Payemnt Application Type") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        payment_mode: paymentMode,
                        type: 'byPaymentApplicationTypeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_application !== 0){
                            labels.push(currentElement.application_type);
                            numbers.push(currentElement.number_of_application);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            }

            else if (selectedFilter == "By Communication No. of Messages") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byCommunicationNoOfMessageChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_messages !== 0){
                            labels.push(currentElement.name);
                            numbers.push(currentElement.total_messages);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By No. of Communication Meeting Notes") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byCommunicationMeetingNotesChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.discussion !== 0){
                            labels.push(currentElement.user_name);
                            numbers.push(currentElement.discussion);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Communication Type") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byCommunicationTypeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_messages !== 0){
                            labels.push(currentElement.communication_type);
                            numbers.push(currentElement.total_messages);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Communication Meeting Note Type") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byCommunicationMeetingNoteTypeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_count !== 0){
                            labels.push(currentElement.communication_type);
                            numbers.push(currentElement.total_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By No. of Communication Messages Sent by User") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byCommunicationMessageSentChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.max_recipients !== 0){
                            labels.push(currentElement.sender_name);
                            numbers.push(currentElement.max_recipients);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Get the index of the hovered data point
                                                const index = tooltipItem.dataIndex;

                                                // Get the current data point's total_amount_sum from result
                                                const totalMessages = result[index].total_messages;

                                                // Return the custom tooltip with total_amount_sum
                                                return [
                                                    'Max Recipients: ' + tooltipItem.raw,
                                                    'Total Number of Message ' + totalMessages
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By No. of Subscribers Referred") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byNoOfSubscribersReferredChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.count !== 0){
                            labels.push(currentElement.name);
                            numbers.push(currentElement.count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Referral Subscribed Plan") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byReferralSubscriberTypeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.referral_count !== 0){
                            labels.push(currentElement.referrer_membership);
                            numbers.push(currentElement.referral_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "Gross Report (Group) By Year") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'grossReportGroupByYearChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.referral_count !== 0){
                            var mem = currentElement.referrer_membership + '--' + currentElement
                                .referral_year
                            console.log(mem);
                            labels.push(mem);
                            numbers.push(currentElement.referral_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Wallet Amount") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byWalletCreditsChart',
                        subid: subID,
                        wallet: price,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.wallet !== 0){
                            labels.push(currentElement.name);
                            numbers.push(currentElement.wallet);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By No. of Wallet Transactions") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",
                    data: {
                        type: 'byCurrentWalletCreditsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        // Prepare labels and numbers
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_balance_change !== 0){
                            // Concatenate user_name with operation_type (Credit/Debit)
                            labels.push(currentElement.user_name + " (" + currentElement
                                .operation_type + ")");
                            numbers.push(currentElement.total_balance_change); // Display total balance change as the number
                        }
                        })

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    // You can configure scales here (e.g., Y-axis if needed)
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return `Balance Change: ${dataValue}`;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return `User: ${dataLabel}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const dataValue = tooltipItem[0].raw || '';
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    `Value: ${dataValue}`,
                                                    `Percentage: ${percentage}%`
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Affiliates No. of Subscribers Referred") {
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                console.log('inside');
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",
                    data: {
                        type: 'byAffiliatesNoofSubscribersReferredsChart',
                        subid: affiID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        // Prepare labels and numbers
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.count !== 0){
                            // Concatenate user_name with operation_type (Credit/Debit)
                            labels.push(currentElement.name);
                            numbers.push(currentElement.count); // Display total balance change as the number
                        }
                        })

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    // You can configure scales here (e.g., Y-axis if needed)
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return `Balance Change: ${dataValue}`;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return `User: ${dataLabel}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const dataValue = tooltipItem[0].raw || '';
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    `Value: ${dataValue}`,
                                                    `Percentage: ${percentage}%`
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Amount of Affiliates Commissions Earnt") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byAmountOfCommissionsEarntChart',
                        subid: affiID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_wallet_balance !== 0){
                            labels.push(currentElement.affiliate_name);
                            numbers.push(currentElement.total_wallet_balance);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Affiliate Country") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byAffiliateCountryChart',
                        subid: affiID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.No_of_Affiliate !== 0){
                            labels.push(currentElement.country);
                            numbers.push(currentElement.No_of_Affiliate);
                            }
                        })
                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Affiliate Subscribed Plan") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byAffiliateSubscribedPlanChart',
                        subid: affiID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.subscriber_count !== 0){
                            labels.push(currentElement.membership);
                            numbers.push(currentElement.subscriber_count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Subscribed Plan") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySubscribedPlanChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.count !== 0){
                            labels.push(currentElement.plan);
                            numbers.push(currentElement.count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Affiliate Current Wallet Credits") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byAffiliateCurrentWalletCreditsChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_wallet_balance !== 0){
                            labels.push(currentElement.name);
                            numbers.push(currentElement.total_wallet_balance);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Ticket Type") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byTicketTypeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.number_of_tickets !== 0){
                            labels.push(currentElement.support);
                            numbers.push(currentElement.number_of_tickets);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Time") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byTimeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_tickets !== 0){
                            var year = currentElement.support + '--' + currentElement.ticket_year;
                            labels.push(year);
                            numbers.push(currentElement.total_tickets);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Support Time Taken") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byTimeTakenChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_tickets !== 0){
                            labels.push(currentElement.time_interval);
                            numbers.push(currentElement.total_tickets);
                            }
                        })
                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Support Staff") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySupportStaffChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.no_of_tickets_solved !== 0){
                            labels.push(currentElement.username);
                            numbers.push(currentElement.no_of_tickets_solved);
                            }
                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Support Staff Name") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bySupportStaffNameChart',
                        subid: subID,
                        username: username,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData();
                            return;
                        }

                        var result = data.data;
                        var labels = [];
                        var totalTickets = [];
                        var openTickets = [];
                        var closedTickets = [];

                        result.forEach(function(currentElement, index) {
                        
                            labels.push(currentElement.support);
                            totalTickets.push(currentElement.total_tickets);
                            openTickets.push(currentElement.open_tickets);
                            closedTickets.push(currentElement.closed_tickets);
                            
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: totalTickets, // Display total tickets
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                    stack: 'stack1'
                                }, {
                                    label: 'Open Tickets',
                                    data: openTickets, // Display open tickets
                                    borderWidth: 1,
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)', // Optional color for open tickets
                                    stack: 'stack1'
                                }, {
                                    label: 'Closed Tickets',
                                    data: closedTickets, // Display closed tickets
                                    borderWidth: 1,
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Optional color for closed tickets
                                    stack: 'stack1'
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    x: {
                                        stacked: true // Stack the bars
                                    },
                                    y: {
                                        stacked: true // Stack the bars
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return `Tickets: ${dataValue}`;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return `Support: ${dataLabel}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const dataValue = tooltipItem[0].raw || '';
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    'Value: ' + dataValue,
                                                    'Percentage: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Demo Request Status") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byDemoRequestStatusChart',
                        subid: subID,
                        username: username,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData();
                            return;
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];


                        result.forEach(function(currentElement, index) {
                        if(currentElement.status_count !== 0){
                            labels.push(currentElement.status);
                            numbers.push(currentElement.status_count);
                            }
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    x: {
                                        stacked: true // Stack the bars
                                    },
                                    y: {
                                        stacked: true // Stack the bars
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return `Tickets: ${dataValue}`;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return `Support: ${dataLabel}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const dataValue = tooltipItem[0].raw || '';
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    'Value: ' + dataValue,
                                                    'Percentage: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Country Demo Requests") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byCounrtyDemoRequestChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData();
                            return;
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];


                        result.forEach(function(currentElement, index) {
                        if(currentElement.demo_request_count !== 0){
                            labels.push(currentElement.country);
                            numbers.push(currentElement.demo_request_count);
                            }
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    x: {
                                        stacked: true // Stack the bars
                                    },
                                    y: {
                                        stacked: true // Stack the bars
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return `Tickets: ${dataValue}`;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return `Support: ${dataLabel}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const dataValue = tooltipItem[0].raw || '';
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    'Value: ' + dataValue,
                                                    'Percentage: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Timeline  No. of Demo Requests") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bytimelineDemoRequestChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData();
                            return;
                        }

                        var result = data.data;
                        var labels = [];
                        var numbers = [];


                        result.forEach(function(currentElement, index) {
                        if(currentElement.demo_request_count !== 0){
                            labels.push(currentElement.country);
                            numbers.push(currentElement.demo_request_count);
                            }
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    x: {
                                        stacked: true // Stack the bars
                                    },
                                    y: {
                                        stacked: true // Stack the bars
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataValue = tooltipItem.raw || '';
                                                return `Tickets: ${dataValue}`;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return `Support: ${dataLabel}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const dataValue = tooltipItem[0].raw || '';
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    'Value: ' + dataValue,
                                                    'Percentage: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Demo Request Time Taken") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'bytimeTakenDemoRequestChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData();
                            return;
                        }
                        var result = data
                        .data; // Example data: [{ country: 'India', time_interval: '1 Week', total_tickets: 3 }]
                        var labels = [];
                        var numbers = [];
                        var time_intervals = [];

                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_tickets !== 0){
                            console.log(currentElement); // Debugging to ensure correct data structure
                            labels.push(currentElement.country); // Push country to labels
                            numbers.push(currentElement.total_tickets); // Push total tickets to numbers
                            }
                            time_intervals.push(currentElement
                            .time_interval); // Push time intervals to a separate array
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        // Create the chart
                        new Chart(ctx, {
                            type: chartType, // e.g., 'bar'
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' +
                                    selectedFilter, // Customize label as needed
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors, // Add your dynamic colors array here
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    x: {
                                        stacked: true, // Stack the bars on the x-axis
                                    },
                                    y: {
                                        stacked: true, // Stack the bars on the y-axis
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            // Display `time_interval` in the tooltip
                                            label: function(tooltipItem) {
                                                const index = tooltipItem
                                                .dataIndex; // Get the current index of the hovered item
                                                const totalTickets = tooltipItem
                                                .raw; // Access total_tickets
                                                const timeInterval = result[index]
                                                .time_interval; // Access time_interval

                                                // Customize the tooltip content
                                                return [
                                                    `Country: ${tooltipItem.label}`, // Display country
                                                    `Tickets: ${totalTickets}`, // Display total_tickets
                                                    `Time Interval: ${timeInterval}` // Display time_interval
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const dataLabel = tooltipItem[0].label || '';
                                                return `Support for: ${dataLabel}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const dataValue = tooltipItem[0].raw || '';
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    `Value: ${dataValue}`,
                                                    `Percentage: ${percentage}%`
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels] // Ensure ChartDataLabels plugin is included
                        });

                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Demo Support Staff") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byDemoStaffNameChart',
                        subid: subID,
                        country: country,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData();
                            return;
                        }
                        var result = data
                        .data; // Example data: [{ country: 'India', time_interval: '1 Week', total_tickets: 3 }]
                        var labels = [];
                        var numbers = [];
                        var time_intervals = [];

                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_tickets !== 0){
                            console.log(currentElement); // Debugging to ensure correct data structure
                            labels.push(currentElement.country); // Push country to labels
                            numbers.push(currentElement.total_tickets); // Push total tickets to numbers
                            }
                            time_intervals.push(currentElement
                            .time_interval); // Push time intervals to a separate array
                        });

                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);

                        // Create the chart
                        new Chart(ctx, {
                            type: chartType, // e.g., 'bar'
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' +
                                    selectedFilter, // Customize label as needed
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors, // Add your dynamic colors array here
                                }]
                            },
                            options: {
                                responsive: false,
                                scales: {
                                    x: {
                                        stacked: true, // Stack the bars on the x-axis
                                    },
                                    y: {
                                        stacked: true, // Stack the bars on the y-axis
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            // Display `time_interval` in the tooltip
                                            label: function(tooltipItem) {
                                                const index = tooltipItem
                                                .dataIndex; // Get the current index of the hovered item
                                                const totalTickets = tooltipItem
                                                .raw; // Access total_tickets
                                                const timeInterval = result[index]
                                                .time_interval; // Access time_interval

                                                // Customize the tooltip content
                                                return [
                                                    `Country: ${tooltipItem.label}`, // Display country
                                                    `Tickets: ${totalTickets}`, // Display total_tickets
                                                    `Time Interval: ${timeInterval}` // Display time_interval
                                                ];
                                            },
                                            beforeBody: function(tooltipItem) {
                                                const dataLabel = tooltipItem[0].label || '';
                                                return `Support for: ${dataLabel}`;
                                            },
                                            afterBody: function(tooltipItem) {
                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const dataValue = tooltipItem[0].raw || '';
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);
                                                return [
                                                    `Value: ${dataValue}`,
                                                    `Percentage: ${percentage}%`
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels] // Ensure ChartDataLabels plugin is included
                        });

                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Activity Type") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byActivityTypeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.count !== 0){
                            labels.push(currentElement.activity_name);
                            numbers.push(currentElement.count);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Total Number No. of Activities By Time") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byTotalNumberNoOfActivitiesByTimeChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_activities !== 0){
                            labels.push('Total Activities');
                            numbers.push(currentElement.total_activities);
                            }

                        })



                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            } else if (selectedFilter == "By Top 10 Activity Subscribers") {

                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                $.ajax({
                    type: 'GET',
                    url: "{{ route('subscribersReport') }}",

                    data: {
                        type: 'byTop10SubscribersChart',
                        subid: subID,
                        startDate: startDate,
                        endDate : endDate
                    },
                    success: function(data) {
                        if (data.data.length === 0) {
                            AdwiseriAlert.noData()
                            return
                        }
                        var result = data.data;
                        //console.log(result);
                        var labels = [];
                        var numbers = [];
                        result.forEach(function(currentElement, index) {
                        if(currentElement.total_activities !== 0){
                            labels.push(currentElement.subscriber);
                            numbers.push(currentElement.total_activities);
                            }

                        })


                        const ctx = document.getElementById('myChart');
                        const dynamicColors = generateDistinctColors(labels.length);


                        new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: selectedAttribute + ' ' + selectedFilter,
                                    data: numbers,
                                    borderWidth: 1,
                                    backgroundColor: dynamicColors,
                                }]
                            },
                            options: {
                                responsive: false,

                                 scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: title,
                                        font: {
                                            size: 20, // Font size
                                            weight: 800 // Bold font weight
                                        },
                                        padding: {
                                            bottom: 50 // Adds space between title and chart
                                        },
                                        color: 'black',
                                        align: 'center'
                                    },
                                     legend: {
                                        display: true, // Hide the legend box
                                        position: 'bottom',
                                        labels: {
                                            padding: 30 // Add space between legend and chart
                                        }
                                    },
                                    colors: {
                                        forceOverride: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Return the data value
                                                const dataValue = tooltipItem.raw || '';
                                                return ``;
                                            },
                                            beforeBody: function(tooltipItem) {
                                                //console.log(tooltipItem[0]);
                                                // Return the dataset label and data label
                                                const datasetLabel = tooltipItem[0].dataset.label ||
                                                    '';
                                                const dataLabel = tooltipItem[0].label || '';
                                                return '-----------------';
                                            },
                                            afterBody: function(tooltipItem) {
                                                // Return a horizontal line
                                                const dataValue = tooltipItem[0].raw || '';

                                                const total = tooltipItem[0].dataset.data.reduce((
                                                    acc, val) => acc + val, 0);
                                                const percentage = ((dataValue / total) * 100)
                                                    .toFixed(1);

                                                return ['Value: ' + tooltipItem[0].raw,
                                                    'Percent Value: ' + percentage + '%'
                                                ];
                                            }
                                        }
                                    },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'top',
                                        formatter: (value) => {
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold'
                                        },
                                        color: 'black'
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]

                        });

                    },
                    error: function(xhr, status, error) {
                        console.error("Error occurred: " + status + " - " + error);
                    }
                });
                document.getElementById('downloadPdf').addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent any default action
                    let downloadButton = this;

                    // Check if download is already in progress
                    if (downloadButton.getAttribute('data-downloading') === 'true') {
                        return;
                    }

                    // Mark as downloading
                    downloadButton.setAttribute('data-downloading', 'true');
                    downloadButton.disabled = true; // Disable button to prevent multiple clicks
                    html2canvas(document.getElementById('myChart')).then(canvas => {

                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'px',
                            format: 'a4'
                        });
                        const title = $('#selectAttribute').val() + " " + $('#filters').val() + " (" +
                            dateForTitle + ")";
                        pdf.setFontSize(16);
                        pdf.text(title, 20, 30); // Set your desired x and y position for the title

                        pdf.addImage(imgData, 'PNG', 10, 50, 410, 410);
                        pdf.save(title + '.pdf');
                     }).catch(error => {
                        console.error("Error generating PDF: ", error);
                    }).finally(() => {
                        // Re-enable button after completion
                        setTimeout(() => {
                            downloadButton.removeAttribute('data-downloading'); // Reset flag
                            downloadButton.disabled = false;
                        }, 1000); // Small delay to ensure smooth UX
                    });
                });
            }

        }

        jQuery.noConflict();
        (function($) {
            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#custom_date_picker').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
            }

            // Initialize daterangepicker
            $('#custom_date_picker').daterangepicker({
                startDate: start,
                endDate: end,
                maxDate: moment(),
                locale: {
                    format: 'DD-MM-YYYY',
                    customRangeLabel: 'Custom Duration' // Rename Custom Range
                },
                ranges: {
                    'Since Inception': [
                        moment('2000-01-01', 'YYYY-MM-DD').startOf('day'),
                        moment().endOf('day')
                    ],
                    'Today': [
                        moment().startOf('day'),
                        moment().endOf('day')
                    ],
                    'Last Week': [
                        moment().subtract(6, 'days').startOf('day'),
                        moment().endOf('day')
                    ],
                    'Last Month': [
                        moment().subtract(1, 'months').startOf('month'),
                        moment().subtract(1, 'months').endOf('month')
                    ],
                    'Last Quarter': [
                        moment().subtract(3, 'months').startOf('month'),
                        moment().subtract(1, 'months').endOf('month')
                    ],
                    'Last Year': [
                        moment().subtract(1, 'year').startOf('year'),
                        moment().subtract(1, 'year').endOf('year')
                    ]
                }
            }, cb);

            // Set the initial value
            cb(start, end);

            // Capture the apply event
            $('#custom_date_picker').on('apply.daterangepicker', function(ev, picker) {
                var StartDate = picker.startDate.format('DD-MM-YYYY');
                var EndDate = picker.endDate.format('DD-MM-YYYY');
                console.log("Selected Date Range: " + StartDate + " - " + EndDate);
            });
        })(jQuery);

        //         $(document).ready(function() {
        //
        // });
    </script>
@endpush
