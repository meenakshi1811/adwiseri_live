@php
    $ebLogHistoryDataUrl = $historyDataUrl ?? route('email_broadcast_log_data');
    $ebLogShowSubscriberFilter = !empty($showSubscriberFilter);
    $ebLogHistoryDataUrlJson = json_encode($ebLogHistoryDataUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $ebLogShowSubscriberFilterJson = json_encode($ebLogShowSubscriberFilter, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<div class="modal fade" id="emailBroadcastLogModal" tabindex="-1" aria-labelledby="emailBroadcastLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#695EEE;color:#fff;">
                <h5 class="modal-title fs-6" id="emailBroadcastLogModalLabel">Email Broadcast Log</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                @if($ebLogShowSubscriberFilter)
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label fw-bold mb-1 small" for="ebLogSubscriber">Select Subscriber</label>
                        <select id="ebLogSubscriber" class="form-select form-select-sm">
                            <option value="">All Subscribers</option>
                            @foreach(($subscriberOptions ?? collect()) as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }} (ID: {{ $sub->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-sm w-100 text-white" style="background:#695EEE;" id="ebLogApplyBtn">Apply</button>
                    </div>
                </div>
                @endif

                <div id="emailBroadcastLogError" class="alert alert-warning py-2 px-3 small d-none mb-2"></div>

                <div class="table-wrapper">
                    <table class="table table-sm table-hover table-bordered mb-0" id="emailBroadcastLogTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">ID</th>
                                @if($ebLogShowSubscriberFilter)
                                <th class="text-center">SUB_ID</th>
                                @endif
                                <th class="text-center">Broadcast Name</th>
                                <th class="text-center">No. of Recipients</th>
                                <th class="text-center">Emails Sent</th>
                                <th class="text-center">DateTime</th>
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
    var historyUrl = {!! $ebLogHistoryDataUrlJson !!};
    var showSubscriberFilter = {!! $ebLogShowSubscriberFilterJson !!};
    var historyTable = null;
    var modalInitialized = false;

    function showLogError(message) {
        var $error = $('#emailBroadcastLogError');
        if (!$error.length) {
            return;
        }

        if (message) {
            $error.text(message).removeClass('d-none');
        } else {
            $error.text('').addClass('d-none');
        }
    }

    function ensureLogTable() {
        if (historyTable) {
            return historyTable;
        }

        if (!$.fn.DataTable) {
            showLogError('Unable to load email broadcast log table.');
            return null;
        }

        var sortColumnIndex = showSubscriberFilter ? 6 : 5;
        var columns = [
            { data: 'id', className: 'text-center', defaultContent: '-' }
        ];

        if (showSubscriberFilter) {
            columns.push({ data: 'sub_id', className: 'text-center', defaultContent: '-' });
        }

        columns.push(
            { data: 'broadcast_name', className: 'text-center', defaultContent: '-' },
            {
                data: 'recipients',
                className: 'text-center',
                defaultContent: '0',
                render: function (data) {
                    return Number(data || 0).toLocaleString();
                }
            },
            {
                data: 'emails_sent',
                className: 'text-center',
                defaultContent: '0',
                render: function (data) {
                    return Number(data || 0).toLocaleString();
                }
            },
            {
                data: 'datetime',
                className: 'text-center',
                defaultContent: '-',
                orderData: [sortColumnIndex]
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
        );

        historyTable = $('#emailBroadcastLogTable').DataTable({
            processing: true,
            serverSide: false,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            autoWidth: false,
            data: [],
            language: {
                emptyTable: 'No email broadcast history found.',
                zeroRecords: 'No matching broadcasts found.'
            },
            columns: columns,
            order: [[sortColumnIndex, 'desc']],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });

        return historyTable;
    }

    function loadEmailBroadcastLog() {
        showLogError('');

        var table = ensureLogTable();
        if (!table) {
            return;
        }

        var payload = { draw: 1 };

        if (showSubscriberFilter) {
            payload.subscriber_id = $('#ebLogSubscriber').val();
        }

        $.ajax({
            url: historyUrl,
            type: 'GET',
            data: payload,
            success: function (response) {
                var rows = Array.isArray(response.data) ? response.data : [];
                table.clear();
                table.rows.add(rows);
                table.order([[showSubscriberFilter ? 6 : 5, 'desc']]).draw();

                if (rows.length === 0) {
                    showLogError('No email broadcast history found for the selected filter.');
                }
            },
            error: function (xhr) {
                var message = 'Could not load email broadcast log.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                table.clear().draw();
                showLogError(message);
            }
        });
    }

    window.initEmailBroadcastLogModal = function () {
        if (!modalInitialized) {
            modalInitialized = true;
            ensureLogTable();

            if (showSubscriberFilter) {
                $('#ebLogApplyBtn').on('click', loadEmailBroadcastLog);
                $('#ebLogSubscriber').on('change', loadEmailBroadcastLog);
            }

            var modalEl = document.getElementById('emailBroadcastLogModal');
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', function () {
                    if (historyTable) {
                        historyTable.columns.adjust().draw(false);
                    }
                    loadEmailBroadcastLog();
                });
            }
        }

        loadEmailBroadcastLog();
    };
})();
</script>
@endpush
