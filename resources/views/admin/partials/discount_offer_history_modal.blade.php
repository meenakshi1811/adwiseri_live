<!-- Discounts / Offers History Modal -->
<div class="modal fade" id="discountOfferHistoryModal" tabindex="-1" aria-labelledby="discountOfferHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:#695EEE;color:#fff;">
                <h5 class="modal-title fs-6" id="discountOfferHistoryModalLabel">Discounts / Offers</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                @if(!empty($showSubscriberFilter))
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label fw-bold mb-1 small" for="discountOfferSubscriber">Select Subscriber</label>
                        <select id="discountOfferSubscriber" class="form-select form-select-sm">
                            <option value="">All Subscribers</option>
                            @foreach(($subscriberOptions ?? collect()) as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }} (ID: {{ $sub->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-sm w-100 text-white" style="background:#695EEE;" id="discountOfferApplyBtn">Apply</button>
                    </div>
                </div>
                @endif

                <div id="discountOfferHistoryError" class="alert alert-warning py-2 px-3 small d-none mb-2"></div>

                <div class="table-wrapper">
                    <table class="table table-sm table-hover table-bordered mb-0" id="discountOfferHistoryTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:70px;">ID</th>
                                <th class="text-center">Discount/Offer</th>
                                <th class="text-center" style="width:150px;">DateTime</th>
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
<style>
    #discountOfferHistoryTable .discount-offer-desc {
        white-space: pre-line;
        text-align: left;
    }
</style>
<script>
(function () {
    var historyUrl = @json($historyDataUrl ?? route('admin_discount_offer_history_data'));
    var showSubscriberFilter = @json(!empty($showSubscriberFilter));
    var historyTable = null;
    var modalInitialized = false;

    function showHistoryError(message) {
        var $error = $('#discountOfferHistoryError');
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
            showHistoryError('Unable to load discounts and offers history table.');
            return null;
        }

        historyTable = $('#discountOfferHistoryTable').DataTable({
            processing: true,
            serverSide: false,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            autoWidth: false,
            data: [],
            language: {
                emptyTable: 'No discounts or offers have been applied yet.',
                zeroRecords: 'No matching discounts or offers found.'
            },
            columns: [
                { data: 'id', className: 'text-center', defaultContent: '-' },
                {
                    data: 'discount_offer',
                    className: 'discount-offer-desc',
                    defaultContent: '-',
                    render: function (data, type) {
                        if (!data) {
                            return '-';
                        }

                        if (type === 'display') {
                            return $('<div>').text(data).html().replace(/\n/g, '<br>');
                        }

                        return data;
                    }
                },
                {
                    data: 'datetime',
                    className: 'text-center',
                    defaultContent: '-',
                    orderData: [3]
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
            order: [[3, 'desc']],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });

        return historyTable;
    }

    function loadDiscountOfferHistory() {
        showHistoryError('');

        var table = ensureHistoryTable();
        if (!table) {
            return;
        }

        var payload = { draw: 1 };

        if (showSubscriberFilter) {
            payload.subscriber_id = $('#discountOfferSubscriber').val();
        }

        $.ajax({
            url: historyUrl,
            type: 'GET',
            data: payload,
            success: function (response) {
                var rows = Array.isArray(response.data) ? response.data : [];
                table.clear();
                table.rows.add(rows);
                table.order([[3, 'desc']]).draw();

                if (rows.length === 0) {
                    showHistoryError('No discounts or offers found for the selected filter.');
                }
            },
            error: function (xhr) {
                var message = 'Could not load discounts and offers history.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                table.clear().draw();
                showHistoryError(message);
            }
        });
    }

    window.initDiscountOfferHistoryModal = function () {
        if (!modalInitialized) {
            modalInitialized = true;
            ensureHistoryTable();

            if (showSubscriberFilter) {
                $('#discountOfferApplyBtn').on('click', loadDiscountOfferHistory);
                $('#discountOfferSubscriber').on('change', loadDiscountOfferHistory);
            }

            var modalEl = document.getElementById('discountOfferHistoryModal');
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', function () {
                    if (historyTable) {
                        historyTable.columns.adjust().draw(false);
                    }
                    loadDiscountOfferHistory();
                });
            }
        }

        loadDiscountOfferHistory();
    };
})();
</script>
@endpush
