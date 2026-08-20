<div class="modal fade" id="leadFollowUpHistoryModal" tabindex="-1" aria-labelledby="leadFollowUpHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#695EEE;color:#fff;">
                <h5 class="modal-title fs-6" id="leadFollowUpHistoryModalLabel">Lead Follow-ups</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div id="leadFollowUpHistoryError" class="alert alert-warning py-2 px-3 small d-none mb-2"></div>

                <div class="table-wrapper">
                    <table class="table table-sm table-hover table-bordered mb-0" id="leadFollowUpHistoryTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:70px;">ID</th>
                                <th class="text-center">User(ID)</th>
                                <th class="text-center">Client(ID)</th>
                                <th class="text-center">Description</th>
                                <th class="text-center" style="width:160px;">DateTime</th>
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
    var historyUrlTemplate = @json(route('enquiries.follow_up_history', ['id' => '__ENQUIRY_ID__']));
    var historyTable = null;
    var activeEnquiryId = null;
    var modalInitialized = false;

    function historyUrlFor(enquiryId) {
        return historyUrlTemplate.replace('__ENQUIRY_ID__', encodeURIComponent(enquiryId));
    }

    function showHistoryError(message) {
        var $error = $('#leadFollowUpHistoryError');
        if (!$error.length) {
            return;
        }

        if (message) {
            $error.text(message).removeClass('d-none');
        } else {
            $error.text('').addClass('d-none');
        }
    }

    function ensureHistoryTable() {
        if (historyTable) {
            return historyTable;
        }

        if (!$.fn.DataTable) {
            showHistoryError('Unable to load follow-up history table.');
            return null;
        }

        historyTable = $('#leadFollowUpHistoryTable').DataTable({
            processing: true,
            serverSide: false,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            autoWidth: false,
            data: [],
            language: {
                emptyTable: 'No follow-up records found.',
                zeroRecords: 'No matching follow-up records found.'
            },
            columns: [
                { data: 'id', className: 'text-center', defaultContent: '-' },
                { data: 'user', className: 'text-center', defaultContent: '-' },
                { data: 'client', className: 'text-center', defaultContent: '-' },
                { data: 'description', className: 'text-start', defaultContent: '-' },
                { data: 'datetime', className: 'text-center', defaultContent: '-' }
            ],
            order: [[4, 'desc']],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });

        return historyTable;
    }

    function loadLeadFollowUpHistory() {
        showHistoryError('');

        if (!activeEnquiryId) {
            return;
        }

        var table = ensureHistoryTable();
        if (!table) {
            return;
        }

        $.ajax({
            url: historyUrlFor(activeEnquiryId),
            type: 'GET',
            data: { draw: 1 },
            success: function (response) {
                var rows = Array.isArray(response.data) ? response.data : [];
                table.clear();
                table.rows.add(rows);
                table.draw();

                if (rows.length === 0) {
                    showHistoryError('No follow-up records found for this lead.');
                }
            },
            error: function (xhr) {
                var message = 'Could not load follow-up history.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                table.clear().draw();
                showHistoryError(message);
            }
        });
    }

    window.initLeadFollowUpHistoryModal = function (enquiryId) {
        activeEnquiryId = enquiryId;

        if (!modalInitialized) {
            modalInitialized = true;
            ensureHistoryTable();

            var modalEl = document.getElementById('leadFollowUpHistoryModal');
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', function () {
                    if (historyTable) {
                        historyTable.columns.adjust().draw(false);
                    }
                    loadLeadFollowUpHistory();
                });
            }
        }

        loadLeadFollowUpHistory();
    };
})();
</script>
@endpush
