<!-- Subscription History Modal (Signup / Upgrade / Renewal only) -->
<div class="modal fade" id="subscriptionHistoryModal" tabindex="-1" aria-labelledby="subscriptionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#695EEE;color:#fff;">
                <h5 class="modal-title fs-6" id="subscriptionHistoryModalLabel">Subscription History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                @if(!empty($showSubscriberFilter))
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label fw-bold mb-1 small" for="subHistorySubscriber">Select Subscriber</label>
                        <select id="subHistorySubscriber" class="form-select form-select-sm">
                            <option value="">All Subscribers</option>
                            @foreach(($subscriberOptions ?? collect()) as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }} (ID: {{ $sub->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-sm w-100 text-white" style="background:#695EEE;" id="subHistoryApplyBtn">Apply</button>
                    </div>
                </div>
                @endif

                <div id="subscriptionHistoryError" class="alert alert-warning py-2 px-3 small d-none mb-2"></div>

                <div class="table-wrapper">
                    <table class="table table-sm table-hover table-bordered mb-0" id="subscriptionHistoryTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:50px;">No.</th>
                                <th class="text-center">Event</th>
                                <th class="text-center">Plan</th>
                                <th class="text-center" style="width:90px;">DOS</th>
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
    var historyUrl = @json($historyDataUrl ?? route('admin_subscription_history_data'));
    var showSubscriberFilter = @json(!empty($showSubscriberFilter));
    var historyTable = null;
    var modalInitialized = false;

    function showHistoryError(message) {
        var $error = $('#subscriptionHistoryError');
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
            showHistoryError('Unable to load subscription history table.');
            return null;
        }

        historyTable = $('#subscriptionHistoryTable').DataTable({
            processing: true,
            serverSide: false,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            autoWidth: false,
            data: [],
            language: {
                emptyTable: 'No subscription history found.',
                zeroRecords: 'No matching subscription events found.'
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
                { data: 'event', className: 'text-center', defaultContent: '-' },
                { data: 'plan', className: 'text-center', defaultContent: '-' },
                { data: 'dos', className: 'text-center', defaultContent: '-' },
                {
                    data: 'datetime',
                    className: 'text-center',
                    defaultContent: '-',
                    orderData: [5]
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

        return historyTable;
    }

    function loadSubscriptionHistory() {
        showHistoryError('');

        var table = ensureHistoryTable();
        if (!table) {
            return;
        }

        var payload = { draw: 1 };

        if (showSubscriberFilter) {
            payload.subscriber_id = $('#subHistorySubscriber').val();
        }

        $.ajax({
            url: historyUrl,
            type: 'GET',
            data: payload,
            success: function (response) {
                var rows = Array.isArray(response.data) ? response.data : [];
                table.clear();
                table.rows.add(rows);
                table.order([[5, 'desc']]).draw();

                if (rows.length === 0) {
                    showHistoryError('No subscription history found for the selected filter.');
                }
            },
            error: function (xhr) {
                var message = 'Could not load subscription history.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                table.clear().draw();
                showHistoryError(message);
            }
        });
    }

    window.initSubscriptionHistoryModal = function () {
        if (!modalInitialized) {
            modalInitialized = true;
            ensureHistoryTable();

            if (showSubscriberFilter) {
                $('#subHistoryApplyBtn').on('click', loadSubscriptionHistory);
                $('#subHistorySubscriber').on('change', loadSubscriptionHistory);
            }

            var modalEl = document.getElementById('subscriptionHistoryModal');
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', function () {
                    if (historyTable) {
                        historyTable.columns.adjust().draw(false);
                    }
                    loadSubscriptionHistory();
                });
            }
        }

        loadSubscriptionHistory();
    };
})();
</script>
@endpush
