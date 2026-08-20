@php
    $tableId = $tableId ?? 'clientTable';
    $filterGroups = $filterGroups ?? [];
    $groupConfig = [];

    foreach ($filterGroups as $group) {
        $groupConfig[$group['key']] = [
            'rowAttribute' => $group['rowAttribute'] ?? ('data-' . $group['key']),
            'match' => $group['match'] ?? 'exact',
            'thresholds' => $group['thresholds'] ?? new stdClass(),
        ];
    }
@endphp
<script>
(function () {
    var tableId = @json($tableId);
    var groupConfig = @json($groupConfig);

    window.multiFilterStates = window.multiFilterStates || {};
    window.multiFilterGroupConfig = window.multiFilterGroupConfig || {};
    window.multiFilterStates[tableId] = window.multiFilterStates[tableId] || {};
    window.multiFilterGroupConfig[tableId] = groupConfig;

    Object.keys(groupConfig).forEach(function (groupKey) {
        if (typeof window.multiFilterStates[tableId][groupKey] === 'undefined') {
            window.multiFilterStates[tableId][groupKey] = '';
        }
    });

    function installMultiFilterSearch() {
        if (window.multiFilterSearchInstalled) {
            return;
        }

        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
            return;
        }

        window.jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var activeTableId = settings.nTable.id;
            var activeFilters = window.multiFilterStates[activeTableId];
            var tableGroupConfig = window.multiFilterGroupConfig[activeTableId];

            if (!activeFilters || !tableGroupConfig) {
                return true;
            }

            var row = settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr;
            if (!row) {
                return false;
            }

            return rowMatchesMultiFilters(row, activeTableId, activeFilters, tableGroupConfig);
        });

        window.multiFilterSearchInstalled = true;
    }

    function rowMatchesMultiFilters(row, activeTableId, activeFilters, tableGroupConfig) {
        return Object.keys(activeFilters).every(function (groupKey) {
            var filterValue = activeFilters[groupKey];
            if (!filterValue) {
                return true;
            }

            var config = tableGroupConfig[groupKey];
            if (!config) {
                return true;
            }

            var rowValue = row.getAttribute(config.rowAttribute) || '';

            if (config.match === 'threshold') {
                var threshold = config.thresholds[filterValue];
                var numericValue = parseInt(rowValue, 10);
                return Number.isFinite(threshold)
                    && Number.isFinite(numericValue)
                    && numericValue <= threshold;
            }

            return rowValue === filterValue;
        });
    }

    function applyMultiFilterToDom(targetTableId) {
        var tableEl = document.getElementById(targetTableId);
        if (!tableEl) {
            return;
        }

        var activeFilters = window.multiFilterStates[targetTableId];
        var tableGroupConfig = window.multiFilterGroupConfig[targetTableId];

        tableEl.querySelectorAll('tbody tr').forEach(function (row) {
            var visible = rowMatchesMultiFilters(row, targetTableId, activeFilters, tableGroupConfig);
            row.style.display = visible ? '' : 'none';
        });
    }

    function redrawMultiFilterTable(targetTableId) {
        var resolvedTableId = targetTableId || tableId;

        if (window.jQuery && window.jQuery.fn.dataTable && window.jQuery.fn.dataTable.isDataTable('#' + resolvedTableId)) {
            window.jQuery('#' + resolvedTableId).DataTable().draw();
            return;
        }

        applyMultiFilterToDom(resolvedTableId);
    }

    function sizeFilterSelect(select) {
        if (!select) {
            return;
        }

        var longest = parseInt(select.getAttribute('data-longest-label-ch') || '0', 10);
        if (!longest) {
            Array.from(select.options || []).forEach(function (option) {
                longest = Math.max(longest, (option.textContent || '').trim().length + 3);
            });
        }

        select.style.width = Math.max(longest, 12) + 'ch';
    }

    function syncFilterPanels(toolbar, selectedGroup) {
        toolbar.querySelectorAll('[data-filter-group-panel]').forEach(function (panel) {
            var isActive = panel.getAttribute('data-filter-group-panel') === selectedGroup;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });
    }

    function bindMultiFilterToolbar() {
        document.querySelectorAll('[data-multi-table-filter-toolbar]').forEach(function (toolbar) {
            if (toolbar.getAttribute('data-bound') === '1') {
                return;
            }

            var toolbarTableId = toolbar.getAttribute('data-table-id');
            if (toolbarTableId !== tableId) {
                return;
            }

            toolbar.setAttribute('data-bound', '1');

            var groupSelect = toolbar.querySelector('[data-filter-group-select]');
            if (groupSelect) {
                sizeFilterSelect(groupSelect);
                syncFilterPanels(toolbar, groupSelect.value);
                groupSelect.addEventListener('change', function () {
                    syncFilterPanels(toolbar, groupSelect.value);
                });
            }

            toolbar.addEventListener('click', function (event) {
                var button = event.target.closest('.table-filter-btn');
                if (!button || !toolbar.contains(button)) {
                    return;
                }

                var groupKey = button.getAttribute('data-filter-group');
                var filterValue = button.getAttribute('data-filter-value') || '';
                var panel = button.closest('[data-filter-group-panel]');

                if (!groupKey || !panel) {
                    return;
                }

                panel.querySelectorAll('.table-filter-btn').forEach(function (item) {
                    var isActive = item === button;
                    item.classList.toggle('is-active', isActive);
                    item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });

                window.multiFilterStates[tableId][groupKey] = filterValue;
                redrawMultiFilterTable(tableId);
            });
        });
    }

    function initMultiTableFilterToolbar() {
        installMultiFilterSearch();
        bindMultiFilterToolbar();
        return true;
    }

    window.initMultiTableFilterToolbar = function () {
        initMultiTableFilterToolbar();
    };

    window.initSubscriberTableFilters = function () {
        initMultiTableFilterToolbar();
        redrawMultiFilterTable(tableId);
    };

    function bootMultiTableFilterToolbar() {
        initMultiTableFilterToolbar();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootMultiTableFilterToolbar);
    } else {
        bootMultiTableFilterToolbar();
    }

    window.addEventListener('load', bootMultiTableFilterToolbar);
})();
</script>
