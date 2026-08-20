@php
    $tableId = $tableId ?? 'clientTable';
    $filterAttribute = $filterAttribute ?? 'data-filter-value';
@endphp
<script>
(function () {
    window.tableFilterStates = window.tableFilterStates || {};

    function installTableFilterSearch() {
        if (window.tableFilterSearchInstalled) {
            return;
        }

        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
            return;
        }

        window.jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var tableId = settings.nTable.id;
            var activeFilter = window.tableFilterStates[tableId];

            if (!activeFilter) {
                return true;
            }

            var row = settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr;
            if (!row) {
                return false;
            }

            var attr = activeFilter.attr || 'data-filter-value';
            var rowValue = row.getAttribute(attr) || '';

            return rowValue === activeFilter.value;
        });

        window.tableFilterSearchInstalled = true;
    }

    function applyTableFilter(tableId, filterValue, filterAttr) {
        window.tableFilterStates[tableId] = filterValue
            ? { value: filterValue, attr: filterAttr }
            : null;

        var tableEl = document.getElementById(tableId);
        if (!tableEl) {
            return;
        }

        if (window.jQuery && window.jQuery.fn.dataTable && window.jQuery.fn.dataTable.isDataTable('#' + tableId)) {
            window.jQuery('#' + tableId).DataTable().draw();
            return;
        }

        tableEl.querySelectorAll('tbody tr').forEach(function (row) {
            if (!filterValue) {
                row.style.display = '';
                return;
            }

            var rowValue = row.getAttribute(filterAttr) || '';
            row.style.display = rowValue === filterValue ? '' : 'none';
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
