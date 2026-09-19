@php
    $dashboardHeaderSlots = $dashboardHeaderSlots ?? \App\Services\DashboardPreferenceService::HEADER_SLOTS;
    $dashboardChartSlots = $dashboardChartSlots ?? \App\Services\DashboardPreferenceService::CHART_SLOTS;
    $dashboardChartCount = $dashboardChartCount ?? \App\Services\DashboardPreferenceService::DEFAULT_CHART_COUNT;
    $dashboardUsingDefaults = $dashboardUsingDefaults ?? true;
    $dashboardHeaderOptions = $dashboardHeaderOptions ?? [];
    $dashboardHeaders = $dashboardHeaders ?? array_fill(0, $dashboardHeaderSlots, '');
    $dashboardChartModules = $dashboardChartModules ?? [];
    $dashboardCharts = $dashboardCharts ?? array_fill(0, $dashboardChartSlots, null);
    $dashboardDurations = $dashboardDurations ?? [];
    $dashboardChartTypes = $dashboardChartTypes ?? [];
    $dashboardChartAvailability = $dashboardChartAvailability ?? [];
@endphp

<div class="tab-pane fade" id="admin-dashboard-settings" role="tabpanel" aria-labelledby="admin-dashboard-settings-tab">
    <form id="admin-dashboard-settings-form">
        @csrf
        <div class="cc-settings-shell">
            <div class="cc-settings-hero">
                <h5><i class="fa fa-gauge-high me-1"></i> Dashboard Preferences</h5>
                <p class="mb-0">
                    Choose which headers and charts appear on your admin dashboard, and how each chart is drawn.
                    Leave any position blank to hide it.
                </p>
                <div class="cc-stat-row mt-3">
                    <span class="cc-stat-pill"><i class="fa fa-table-columns"></i> <span id="dashHeroHeadersCount">0</span> of {{ $dashboardHeaderSlots }} headers</span>
                    <span class="cc-stat-pill"><i class="fa fa-chart-pie"></i> <span id="dashHeroChartsCount">0</span> of <span id="dashHeroChartsMax">{{ $dashboardChartCount }}</span> charts</span>
                </div>
                @if($dashboardUsingDefaults)
                    <div class="cc-defaults-notice" id="dashDefaultsNotice">
                        <i class="fa fa-circle-info"></i>
                        <div>
                            <strong>Standard dashboard defaults are pre-selected.</strong>
                            Adjust the dropdowns below and click <strong>Save Dashboard Preferences</strong> to lock in your own layout.
                        </div>
                    </div>
                @else
                    <div class="cc-defaults-notice d-none" id="dashDefaultsNotice" aria-hidden="true"></div>
                @endif
            </div>

            <div class="row g-3 dash-pref-columns">
                <div class="col-12 col-xl-5">
                    <div class="cc-picker-card">
                        <div class="cc-picker-header">
                            <div>
                                <h6><i class="fa fa-table-columns"></i> Set Header Preferences</h6>
                                <span class="cc-picker-count">Pick up to {{ $dashboardHeaderSlots }} stat cards, in the order you want them shown.</span>
                            </div>
                            <div class="dash-section-actions">
                                <button type="button" class="btn btn-primary btn-sm" id="reset-dashboard-headers">Reset to Default</button>
                            </div>
                        </div>
                        <div class="row g-2 p-2">
                            @for($i = 0; $i < $dashboardHeaderSlots; $i++)
                                <div class="col-12 col-sm-6">
                                    <label class="form-label dash-pref-label mb-1" for="dashHeader{{ $i }}">
                                        {{ $i + 1 }}{{ [1=>'st',2=>'nd',3=>'rd'][$i + 1] ?? 'th' }} Header
                                    </label>
                                    <select class="form-select dash-header-select" id="dashHeader{{ $i }}" name="headers[]">
                                        <option value="">— None —</option>
                                        @foreach($dashboardHeaderOptions as $key => $opt)
                                            <option value="{{ $key }}" {{ ($dashboardHeaders[$i] ?? '') === $key ? 'selected' : '' }}>
                                                {{ $opt['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-7">
                    <div class="cc-picker-card">
                        <div class="cc-picker-header">
                            <div>
                                <h6><i class="fa fa-chart-pie"></i> Set Chart Preferences</h6>
                                <span class="cc-picker-count">Each position takes a module, filter, duration, and chart type.</span>
                            </div>
                            <div class="dash-section-actions">
                                <button type="button" class="btn btn-primary btn-sm" id="reset-dashboard-charts">Reset to Default</button>
                            </div>
                        </div>
                        <div class="p-2" id="dashChartSlots">
                            @for($i = 0; $i < $dashboardChartSlots; $i++)
                                @php
                                    $slot = $dashboardCharts[$i] ?? null;
                                    $slot = is_array($slot) ? $slot : null;
                                @endphp
                                <div class="dash-chart-slot border rounded" data-slot-index="{{ $i }}">
                                    <div class="dash-chart-slot-title">Chart Position {{ $i + 1 }}</div>
                                    <div class="dash-chart-fields">
                                        <div>
                                            <label class="form-label dash-pref-label mb-1" for="dashChartModule{{ $i }}">Module</label>
                                            <select class="form-select dash-chart-module" id="dashChartModule{{ $i }}"
                                                name="charts[{{ $i }}][module]" data-slot-index="{{ $i }}">
                                                <option value="">— None —</option>
                                                @foreach($dashboardChartModules as $key => $mod)
                                                    <option value="{{ $key }}" {{ ($slot['module'] ?? '') === $key ? 'selected' : '' }}>
                                                        {{ $mod['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label dash-pref-label mb-1" for="dashChartFilter{{ $i }}">Filter</label>
                                            <select class="form-select dash-chart-filter" id="dashChartFilter{{ $i }}"
                                                name="charts[{{ $i }}][filter]" data-slot-index="{{ $i }}"
                                                data-selected="{{ $slot['filter'] ?? '' }}">
                                                <option value="">— Select Module first —</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label dash-pref-label mb-1" for="dashChartDuration{{ $i }}">Duration</label>
                                            <select class="form-select dash-chart-duration" id="dashChartDuration{{ $i }}"
                                                name="charts[{{ $i }}][duration]">
                                                @foreach($dashboardDurations as $key => $label)
                                                    <option value="{{ $key }}" {{ ($slot['duration'] ?? 'since_inception') === $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label dash-pref-label mb-1" for="dashChartType{{ $i }}">Chart Type</label>
                                            <select class="form-select dash-chart-type" id="dashChartType{{ $i }}"
                                                name="charts[{{ $i }}][chart_type]">
                                                @foreach($dashboardChartTypes as $key => $label)
                                                    <option value="{{ $key }}" {{ ($slot['chart_type'] ?? 'doughnut') === $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <div class="cc-settings-actions cc-settings-actions--center">
                <button type="button" class="btn btn-primary" id="save-admin-dashboard-settings">
                    <i class="fa fa-save me-1"></i> Save Dashboard Preferences
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function ($) {
    const adminDashSaveUrl = @json(route('save_admin_dashboard_settings'));
    const dashChartModules = @json($dashboardChartModules);
    const dashChartAvailability = @json($dashboardChartAvailability);
    const dashHeaderSlots = {{ (int) $dashboardHeaderSlots }};
    const dashChartSlots = {{ (int) $dashboardChartSlots }};
    const dashActiveChartSlots = dashChartSlots;

    function dashGetActiveChartSlots() {
        return dashActiveChartSlots;
    }

    function dashPairKey(module, filter) {
        return module + '|' + filter;
    }

    function dashFilterHasData(module, filter) {
        if (!module || !filter) return true;
        const key = dashPairKey(module, filter);
        if (!Object.prototype.hasOwnProperty.call(dashChartAvailability, key)) {
            return true;
        }
        return !!dashChartAvailability[key];
    }

    function dashTakenPairs(exceptIndex) {
        const taken = [];
        for (let i = 0; i < dashGetActiveChartSlots(); i++) {
            if (i === exceptIndex) continue;
            const module = $('#dashChartModule' + i).val();
            const filter = $('#dashChartFilter' + i).val();
            if (module && filter) taken.push(dashPairKey(module, filter));
        }
        return taken;
    }

    function dashPopulateFilters(index, keepValue) {
        const $module = $('#dashChartModule' + index);
        const $filter = $('#dashChartFilter' + index);
        const module = $module.val();
        const desired = keepValue !== undefined ? keepValue : ($filter.attr('data-selected') || '');

        $filter.empty();

        if (!module || !dashChartModules[module]) {
            $filter.append('<option value="">— Select Module first —</option>');
            return;
        }

        const taken = dashTakenPairs(index);
        const filters = dashChartModules[module].filters || {};

        $filter.append('<option value="">— Select Filter —</option>');

        Object.keys(filters).forEach(function (key) {
            if (taken.indexOf(dashPairKey(module, key)) !== -1) return;
            const hasData = dashFilterHasData(module, key);
            const selected = key === desired && hasData ? ' selected' : '';
            const disabled = !hasData ? ' disabled' : '';
            const suffix = !hasData ? ' (no data)' : '';
            $filter.append('<option value="' + key + '"' + selected + disabled + '>' + filters[key] + suffix + '</option>');
        });

        if (desired && !dashFilterHasData(module, desired)) {
            $filter.val('');
            $filter.attr('data-selected', '');
        }
    }

    function dashRefreshAllFilters() {
        for (let i = 0; i < dashGetActiveChartSlots(); i++) {
            const current = $('#dashChartFilter' + i).val();
            dashPopulateFilters(i, current == null ? '' : current);
        }
        dashUpdateCounts();
    }

    function dashRefreshHeaderOptions() {
        const chosen = [];
        $('.dash-header-select').each(function () {
            const val = $(this).val();
            if (val) chosen.push(val);
        });

        $('.dash-header-select').each(function () {
            const current = $(this).val();
            $(this).find('option').each(function () {
                const val = $(this).val();
                if (!val) return;
                $(this).prop('disabled', val !== current && chosen.indexOf(val) !== -1);
            });
        });

        dashUpdateCounts();
    }

    function dashUpdateCounts() {
        let headers = 0;
        $('.dash-header-select').each(function () {
            if ($(this).val()) headers++;
        });

        let charts = 0;
        for (let i = 0; i < dashGetActiveChartSlots(); i++) {
            if ($('#dashChartModule' + i).val() && $('#dashChartFilter' + i).val()) charts++;
        }

        $('#dashHeroHeadersCount').text(headers);
        $('#dashHeroChartsCount').text(charts);
        $('#dashHeroChartsMax').text(dashGetActiveChartSlots());
    }

    function dashCollectPayload() {
        const headers = [];
        $('.dash-header-select').each(function () {
            headers.push($(this).val() || '');
        });

        const charts = [];
        for (let i = 0; i < dashGetActiveChartSlots(); i++) {
            const module = $('#dashChartModule' + i).val();
            const filter = $('#dashChartFilter' + i).val();
            if (!module || !filter) continue;
            charts.push({
                module: module,
                filter: filter,
                duration: $('#dashChartDuration' + i).val() || 'since_inception',
                chart_type: $('#dashChartType' + i).val() || 'doughnut'
            });
        }

        return { headers: headers, charts: charts, chart_count: dashGetActiveChartSlots() };
    }

    function dashApplyResponse(response) {
        const headers = response.headers || [];
        for (let i = 0; i < dashHeaderSlots; i++) {
            $('#dashHeader' + i).val(headers[i] || '');
        }

        const charts = response.charts || [];
        for (let i = 0; i < dashChartSlots; i++) {
            const slot = charts[i];
            $('#dashChartModule' + i).val(slot ? slot.module : '');
            $('#dashChartFilter' + i).attr('data-selected', slot ? slot.filter : '');
            $('#dashChartDuration' + i).val(slot ? slot.duration : 'since_inception');
            $('#dashChartType' + i).val(slot ? slot.chart_type : 'doughnut');
            dashPopulateFilters(i, slot ? slot.filter : '');
        }

        dashRefreshHeaderOptions();
        dashUpdateCounts();
    }

    function dashPost(data, successMessageFallback) {
        $.ajax({
            url: adminDashSaveUrl,
            method: 'POST',
            data: Object.assign({ _token: @json(csrf_token()) }, data),
            success: function (response) {
                dashApplyResponse(response);
                $('#dashDefaultsNotice').addClass('d-none');
                Swal.fire({ icon: 'success', title: 'Success', text: response.message || successMessageFallback });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Oops!',
                    text: xhr?.responseJSON?.message || successMessageFallback.replace('saved', 'save').replace('reset', 'reset')
                });
            }
        });
    }

    $(document).on('change', '.dash-chart-module', function () {
        const index = parseInt($(this).attr('data-slot-index'), 10);
        $('#dashChartFilter' + index).attr('data-selected', '');
        dashPopulateFilters(index, '');
        dashRefreshAllFilters();
    });

    $(document).on('change', '.dash-chart-filter', function () {
        $(this).attr('data-selected', $(this).val() || '');
        dashRefreshAllFilters();
    });

    $(document).on('change', '.dash-header-select', dashRefreshHeaderOptions);

    $('#save-admin-dashboard-settings').on('click', function () {
        const payload = dashCollectPayload();
        if (!payload.headers.filter(Boolean).length && !payload.charts.length) {
            Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Select at least one header or one chart before saving.'
            });
            return;
        }
        dashPost(payload, 'Dashboard preferences saved.');
    });

    $('#reset-dashboard-headers').on('click', function () {
        dashPost({ reset_headers: 1 }, 'Header preferences reset to defaults.');
    });

    $('#reset-dashboard-charts').on('click', function () {
        dashPost({ reset_charts: 1 }, 'Chart preferences reset to defaults.');
    });

    dashRefreshAllFilters();
    dashRefreshHeaderOptions();
})(jQuery);
</script>
@endpush
