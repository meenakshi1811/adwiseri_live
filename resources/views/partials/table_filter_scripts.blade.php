@php
    $tableId = $tableId ?? 'clientTable';
    $filterAttribute = $filterAttribute ?? 'data-filter-value';
@endphp
<script>
(function () {
    // Active filters per table: { tableId: { rowAttribute: value } }. A table can have several toolbars
    // (each filtering on its own row attribute); a row must match all of their selections.
    window.tableFilterStates = window.tableFilterStates || {};

    function rowMatchesTableFilters(row, filters) {
        return Object.keys(filters || {}).every(function (attr) {
            var value = filters[attr];
            return !value || (row.getAttribute(attr) || '') === value;
        });
    }

    function installTableFilterSearch() {
        if (window.tableFilterSearchInstalled) {
            return;
        }

        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
            return;
        }

        window.jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var filters = window.tableFilterStates[settings.nTable.id];

            if (!filters) {
                return true;
            }

            var row = settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr;
            if (!row) {
                return false;
            }

            return rowMatchesTableFilters(row, filters);
        });

        window.tableFilterSearchInstalled = true;
    }

    function applyTableFilter(tableId, filterValue, filterAttr) {
        var filters = window.tableFilterStates[tableId] = window.tableFilterStates[tableId] || {};
        filters[filterAttr] = filterValue || '';

        var tableEl = document.getElementById(tableId);
        if (!tableEl) {
            return;
        }

        if (window.jQuery && window.jQuery.fn.dataTable && window.jQuery.fn.dataTable.isDataTable('#' + tableId)) {
            window.jQuery('#' + tableId).DataTable().draw();
            return;
        }

        tableEl.querySelectorAll('tbody tr').forEach(function (row) {
            row.style.display = rowMatchesTableFilters(row, filters) ? '' : 'none';
        });
    }

    function installClickHandler() {
        if (window.tableFilterClickInstalled) {
            return;
        }

        document.addEventListener('click', function (event) {
            var button = event.target.closest('.table-filter-btn');
            if (!button) {
                return;
            }

            var toolbar = button.closest('[data-table-filter-toolbar]');
            if (!toolbar || toolbar.hasAttribute('data-multi-table-filter-toolbar')) {
                return;
            }

            var tableId = toolbar.getAttribute('data-table-id');
            var filterAttr = toolbar.getAttribute('data-filter-attr') || 'data-filter-value';
            var filterValue = button.getAttribute('data-filter-value') || '';

            toolbar.querySelectorAll('.table-filter-btn').forEach(function (item) {
                var isActive = item === button;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            applyTableFilter(tableId, filterValue, filterAttr);
        });

        window.tableFilterClickInstalled = true;
    }

    function initTableFilterToolbar() {
        installTableFilterSearch();
        installClickHandler();
        return true;
    }

    function bootTableFilterToolbar() {
        initTableFilterToolbar();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootTableFilterToolbar);
    } else {
        bootTableFilterToolbar();
    }

    window.addEventListener('load', bootTableFilterToolbar);
})();
</script>
