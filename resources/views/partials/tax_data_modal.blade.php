<!-- Tax Summary Modal -->
<div class="modal fade" id="taxDataModal" tabindex="-1" aria-labelledby="taxDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 position-relative" style="background:#695EEE;color:#fff;">
                <h5 class="modal-title fs-6 w-100 text-center mb-0" id="taxDataModalLabel">Tax Summary</h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div id="taxDataError" class="alert alert-warning py-2 px-3 small d-none mb-2"></div>

                <div class="mb-2 small text-muted text-center">
                    Total Tax Collected (Since Inception):
                    <strong id="taxDataTotalCollected" class="text-dark">0.00</strong>
                </div>

                <h6 class="fw-bold text-primary mb-2 mt-3 text-center">Tax Summary (By Timeline)</h6>
                <div class="table-wrapper mb-4">
                    <table class="table table-sm table-hover table-bordered mb-0" id="taxTimelineTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:60px;">Sr No.</th>
                                <th class="text-center">Duration</th>
                                <th class="text-center" style="width:140px;">Tax Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <h6 class="fw-bold text-primary mb-2 text-center">Tax Summary (By Year)</h6>
                <div class="table-wrapper">
                    <table class="table table-sm table-hover table-bordered mb-0" id="taxYearTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:60px;">Sr No.</th>
                                <th class="text-center">Year</th>
                                <th class="text-center" style="width:140px;">Tax Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var taxDataUrl = @json($taxDataUrl ?? route('payments_tax_data'));
    var timelineTable = null;
    var yearTable = null;
    var modalInitialized = false;

    function showTaxDataError(message) {
        var $error = $('#taxDataError');
        if (!$error.length) {
            return;
        }

        if (message) {
            $error.text(message).removeClass('d-none');
        } else {
            $error.text('').addClass('d-none');
        }
    }

    function serialColumn(tableSelector) {
        return {
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row, meta) {
                return meta.row + 1;
            }
        };
    }

    function ensureTimelineTable() {
        if (timelineTable) {
            return timelineTable;
        }

        if (!$.fn.DataTable) {
            showTaxDataError('Unable to load tax summary tables.');
            return null;
        }

        timelineTable = $('#taxTimelineTable').DataTable({
            processing: true,
            serverSide: false,
            searching: false,
            ordering: false,
            paging: false,
            info: false,
            autoWidth: false,
            data: [],
            language: {
                emptyTable: 'No tax summary found.'
            },
            columns: [
                serialColumn('#taxTimelineTable'),
                { data: 'duration', className: 'text-center', defaultContent: '-' },
                { data: 'tax_amount', className: 'text-center', defaultContent: '0.00' }
            ]
        });

        return timelineTable;
    }

    function ensureYearTable() {
        if (yearTable) {
            return yearTable;
        }

        if (!$.fn.DataTable) {
            showTaxDataError('Unable to load tax summary tables.');
            return null;
        }

        yearTable = $('#taxYearTable').DataTable({
            processing: true,
            serverSide: false,
            searching: false,
            ordering: true,
            paging: true,
            info: true,
            autoWidth: false,
            data: [],
            language: {
                emptyTable: 'No yearly tax summary found.',
                zeroRecords: 'No yearly tax summary found.'
            },
            columns: [
                serialColumn('#taxYearTable'),
                { data: 'year', className: 'text-center', defaultContent: '-' },
                { data: 'tax_amount', className: 'text-center', defaultContent: '0.00' }
            ],
            order: [[1, 'desc']],
            lengthMenu: [[10, 25, 50], [10, 25, 50]],
            pageLength: 10
        });

        return yearTable;
    }

    function loadTaxData() {
        showTaxDataError('');

        var timeline = ensureTimelineTable();
        var year = ensureYearTable();

        if (!timeline || !year) {
            return;
        }

        $.ajax({
            url: taxDataUrl,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: { draw: 1 },
            success: function (response) {
                var timelineRows = Array.isArray(response.by_timeline) ? response.by_timeline : [];
                var yearRows = Array.isArray(response.by_year) ? response.by_year : [];

                $('#taxDataTotalCollected').text(response.total_collected_tax_formatted || '0.00');

                timeline.clear();
                timeline.rows.add(timelineRows);
                timeline.draw();

                year.clear();
                year.rows.add(yearRows);
                year.order([[1, 'desc']]).draw();

                if (timelineRows.length === 0 && yearRows.length === 0) {
                    showTaxDataError('No tax summary found for your account.');
                }
            },
            error: function (xhr) {
                var message = 'Could not load tax summary.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                timeline.clear().draw();
                year.clear().draw();
                $('#taxDataTotalCollected').text('0.00');
                showTaxDataError(message);
            }
        });
    }

    window.initTaxDataModal = function () {
        if (!modalInitialized) {
            modalInitialized = true;
            ensureTimelineTable();
            ensureYearTable();

            var modalEl = document.getElementById('taxDataModal');
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', function () {
                    if (timelineTable) {
                        timelineTable.columns.adjust().draw(false);
                    }
                    if (yearTable) {
                        yearTable.columns.adjust().draw(false);
                    }
                    loadTaxData();
                });
            }
        }

        loadTaxData();
    };
})();
</script>
@endpush
