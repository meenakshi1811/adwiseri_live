<!-- Ticket Activity Log Modal -->
<div class="modal fade" id="ticketActivityLogModal" tabindex="-1" aria-labelledby="ticketActivityLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#695EEE;color:#fff;">
                <h5 class="modal-title fs-6 text-center flex-grow-1" id="ticketActivityLogModalLabel">Ticket Activity Log</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div id="ticketActivityLogError" class="alert alert-warning py-2 px-3 small d-none mb-2"></div>

                <div class="table-wrapper">
                    <table class="table table-sm table-hover table-bordered mb-0" id="ticketActivityLogTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:50px;">No.</th>
                                <th class="text-center" style="width:150px;">Date &amp; Time</th>
                                <th class="text-center" style="width:120px;">Action</th>
                                <th class="text-center" style="width:150px;">Worked By</th>
                                <th class="text-center">Details</th>
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
    var activityUrlTemplate = @json($activityDataUrl ?? route('admin_ticket_activity_log_data', ['id' => '__ID__']));
    var activityTable = null;
    var currentTicketId = null;

    function showActivityError(message) {
        var $error = $('#ticketActivityLogError');
        if (!$error.length) {
            return;
        }

        if (message) {
            $error.text(message).removeClass('d-none');
        } else {
            $error.text('').addClass('d-none');
        }
    }

    function ensureActivityTable() {
        if (activityTable) {
            return activityTable;
        }

        if (!$.fn.DataTable) {
            showActivityError('Unable to load ticket activity log table.');
            return null;
        }

        activityTable = $('#ticketActivityLogTable').DataTable({
            processing: true,
            serverSide: false,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            autoWidth: false,
            data: [],
            language: {
                emptyTable: 'No activity log entries found.',
                zeroRecords: 'No matching activity log entries found.'
            },
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
                    data: 'datetime',
                    className: 'text-center',
                    defaultContent: '-',
                    orderData: [5]
                },
                { data: 'action', className: 'text-center', defaultContent: '-' },
                { data: 'worked_by', className: 'text-center', defaultContent: '-' },
                {
                    data: 'detail',
                    className: 'text-start',
                    defaultContent: '-',
                    render: function (data) {
                        if (!data) {
                            return '-';
                        }
                        return $('<div>').text(data).html().replace(/\n/g, '<br>');
                    }
                },
                {
                    data: 'created_at',
                    visible: false,
                    defaultContent: '',
                    render: function (data) {
                        if (!data) {
                            return 0;
                        }
                        var parsed = new Date(data);
                        return isNaN(parsed.getTime()) ? 0 : parsed.getTime();
                    }
                }
            ],
            order: [[5, 'desc']],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });

        return activityTable;
    }

    function loadTicketActivityLog(ticketId) {
        showActivityError('');

        var table = ensureActivityTable();
        if (!table) {
            return;
        }

        var url = activityUrlTemplate.replace('__ID__', ticketId);

        $.ajax({
            url: url,
            type: 'GET',
            data: { draw: 1 },
            success: function (response) {
                var rows = Array.isArray(response.data) ? response.data : [];
                var ticketNo = response.ticket_no || '';

                $('#ticketActivityLogModalLabel').text(
                    ticketNo ? 'Ticket Activity Log — ' + ticketNo : 'Ticket Activity Log'
                );

                table.clear();
                table.rows.add(rows);
                table.order([[5, 'desc']]).draw();

                if (rows.length === 0) {
                    showActivityError('No activity log entries found for this ticket.');
                }
            },
            error: function (xhr) {
                var message = 'Could not load ticket activity log.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                table.clear().draw();
                showActivityError(message);
            }
        });
    }

    window.openTicketActivityLog = function (ticketId, ticketNo) {
        currentTicketId = ticketId;

        if (ticketNo) {
            $('#ticketActivityLogModalLabel').text('Ticket Activity Log — ' + ticketNo);
        } else {
            $('#ticketActivityLogModalLabel').text('Ticket Activity Log');
        }

        var modalEl = document.getElementById('ticketActivityLogModal');
        if (modalEl) {
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        loadTicketActivityLog(ticketId);
    };

    var modalEl = document.getElementById('ticketActivityLogModal');
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
            if (activityTable) {
                activityTable.columns.adjust().draw(false);
            }
            if (currentTicketId) {
                loadTicketActivityLog(currentTicketId);
            }
        });
    }
})();
</script>
@endpush
