@php
    $ebLogHistoryDataUrl = $historyDataUrl ?? route('email_broadcast_log_data');
    $ebLogRecipientsDataUrl = $recipientsDataUrl ?? route('email_broadcast_log_recipients');
    $ebLogShowSubscriberFilter = !empty($showSubscriberFilter);
    $ebLogHistoryDataUrlJson = json_encode($ebLogHistoryDataUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $ebLogRecipientsDataUrlJson = json_encode($ebLogRecipientsDataUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $ebLogShowSubscriberFilterJson = json_encode($ebLogShowSubscriberFilter, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<style>
    #emailBroadcastLogTable th,
    #emailBroadcastLogTable td {
        vertical-align: middle;
    }

    /* Numeric columns stay narrow (their titles wrap) to leave room for Sent By. */
    #emailBroadcastLogTable .eb-log-col-num {
        width: 4.5rem;
    }

    #emailBroadcastLogTable .eb-log-col-sender {
        width: 8rem;
    }

    #emailBroadcastLogTable .eb-log-col-nowrap {
        white-space: nowrap;
    }

    /* DataTables' Bootstrap rows overflowed the wrapper by their gutter and showed a stray horizontal scrollbar. */
    #emailBroadcastLogModal .dataTables_wrapper > .row {
        --bs-gutter-x: 0;
    }

    .eb-log-recipients {
        color: #695EEE;
        font-weight: 600;
        cursor: pointer;
        text-decoration: underline dotted;
        text-underline-offset: 3px;
    }

    .eb-log-recipients:hover,
    .eb-log-recipients:focus-visible,
    .eb-log-recipients[aria-expanded="true"] {
        text-decoration-style: solid;
    }

    .tooltip.eb-log-recipient-tip .tooltip-inner {
        max-width: 22rem;
        white-space: pre-line;
        text-align: left;
    }

    #emailBroadcastLogTable tr.eb-log-child > td {
        background: #F7F6FF;
        padding: .5rem .75rem;
    }

    /* Sticks to the visible left edge so the list stays in view when the table is scrolled sideways (phones). */
    .eb-log-recipient-panel {
        position: sticky;
        left: .75rem;
        max-width: calc(100vw - 5rem);
        text-align: left;
    }

    .eb-log-recipient-head {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: .25rem .75rem;
        margin-bottom: .375rem;
        font-size: .8125rem;
    }

    .eb-log-recipient-head strong {
        color: #695EEE;
    }

    .eb-log-recipient-groups {
        color: #6c757d;
    }

    .eb-log-recipient-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
        gap: .375rem 1rem;
        max-height: 14rem;
        overflow-y: auto;
        margin: 0;
        padding: 0;
        list-style: none;
        counter-reset: eb-log-recipient;
        font-size: .8125rem;
    }

    .eb-log-recipient-list li {
        display: flex;
        gap: .375rem;
        min-width: 0;
        line-height: 1.3;
    }

    .eb-log-recipient-list li::before {
        counter-increment: eb-log-recipient;
        content: counter(eb-log-recipient) ".";
        flex: 0 0 1.75rem;
        color: #6c757d;
        text-align: right;
    }

    .eb-log-recipient-who {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .eb-log-recipient-name {
        font-weight: 600;
    }

    .eb-log-recipient-email {
        color: #6c757d;
        overflow-wrap: anywhere;
    }
</style>

<div class="modal fade" id="emailBroadcastLogModal" tabindex="-1" aria-labelledby="emailBroadcastLogModalLabel" aria-hidden="true">
    <div class="modal-dialog {{ $ebLogShowSubscriberFilter ? 'modal-xl' : 'modal-lg' }} modal-dialog-scrollable">
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
                                <th class="text-center">Sent By</th>
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
    var recipientsUrl = {!! $ebLogRecipientsDataUrlJson !!};
    var showSubscriberFilter = {!! $ebLogShowSubscriberFilterJson !!};
    // The hidden created_at column that DateTime sorts on.
    var sortColumnIndex = showSubscriberFilter ? 7 : 6;
    var historyTable = null;
    var modalInitialized = false;
    var logGeneration = 0;
    var recipientCache = {};

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderText(data, type) {
        var text = data === null || data === undefined || data === '' ? '-' : String(data);

        return type === 'display' ? escapeHtml(text) : text;
    }

    function renderSentBy(data, type) {
        var text = renderText(data, type);

        // Keep "(ID)" on the same line as the end of the name.
        return type === 'display' ? text.replace(/ \((\d+)\)$/, '&nbsp;($1)') : text;
    }

    function recipientLabel(recipient) {
        var name = String(recipient.name || '').trim();
        var email = String(recipient.email || '').trim();

        return name ? name + ' (' + email + ')' : email;
    }

    function recipientTooltip(row, total, preview) {
        var groups = Array.isArray(row.recipient_groups) ? row.recipient_groups : [];
        var lines = [
            total.toLocaleString() + (total === 1 ? ' recipient' : ' recipients')
                + (groups.length ? ' · ' + groups.join(', ') : '')
        ];

        preview.forEach(function (recipient) {
            lines.push(recipientLabel(recipient));
        });

        var more = total - preview.length;
        if (more > 0) {
            lines.push('+ ' + more.toLocaleString() + ' more — click to view all');
        }

        return lines.join('\n');
    }

    function renderRecipients(data, type, row) {
        var total = Number(data || 0);

        if (type !== 'display') {
            return total;
        }

        var preview = Array.isArray(row.recipient_preview) ? row.recipient_preview : [];
        var label = total.toLocaleString();

        if (!preview.length) {
            return total > 0
                ? '<span title="Recipient details are not available for this broadcast.">' + label + '</span>'
                : label;
        }

        return '<span class="eb-log-recipients" role="button" tabindex="0" aria-expanded="false" title="'
            + escapeHtml(recipientTooltip(row, total, preview)) + '">' + label + '</span>';
    }

    function recipientPanel(response) {
        var recipients = response && Array.isArray(response.recipients) ? response.recipients : [];
        var groups = response && Array.isArray(response.recipient_groups) ? response.recipient_groups : [];

        if (!recipients.length) {
            return '<div class="small text-muted">Recipient details are not available for this broadcast.</div>';
        }

        var items = recipients.map(function (recipient) {
            var name = String(recipient.name || '').trim();

            return '<li><span class="eb-log-recipient-who">'
                + (name ? '<span class="eb-log-recipient-name">' + escapeHtml(name) + '</span>' : '')
                + '<span class="eb-log-recipient-email">' + escapeHtml(recipient.email || '') + '</span>'
                + '</span></li>';
        }).join('');

        return '<div class="eb-log-recipient-head"><strong>Recipients (' + recipients.length.toLocaleString() + ')</strong>'
            + (groups.length ? '<span class="eb-log-recipient-groups">' + escapeHtml(groups.join(', ')) + '</span>' : '')
            + '</div>'
            + '<ol class="eb-log-recipient-list">' + items + '</ol>';
    }

    function showRecipientPanel(row, html) {
        row.child('<div class="eb-log-recipient-panel">' + html + '</div>', 'eb-log-child').show();
    }

    function recipientTriggers() {
        return $('#emailBroadcastLogTable tbody .eb-log-recipients');
    }

    function recipientTooltipFor(trigger) {
        return window.bootstrap && bootstrap.Tooltip ? bootstrap.Tooltip.getInstance(trigger) : null;
    }

    function initRecipientTooltips() {
        if (!window.bootstrap || !bootstrap.Tooltip) {
            return;
        }

        recipientTriggers().each(function () {
            if (!recipientTooltipFor(this)) {
                new bootstrap.Tooltip(this, { customClass: 'eb-log-recipient-tip' });
            }
        });
    }

    function disposeRecipientTooltips() {
        recipientTriggers().each(function () {
            var tooltip = recipientTooltipFor(this);
            if (tooltip) {
                tooltip.dispose();
            }
        });
    }

    function toggleRecipientList(trigger) {
        var table = ensureLogTable();
        if (!table) {
            return;
        }

        var row = table.row($(trigger).closest('tr'));
        var rowData = row.data();
        if (!rowData) {
            return;
        }

        var tooltip = recipientTooltipFor(trigger);
        if (tooltip) {
            tooltip.hide();
        }

        if (row.child.isShown()) {
            row.child.hide();
            trigger.setAttribute('aria-expanded', 'false');
            return;
        }

        trigger.setAttribute('aria-expanded', 'true');

        if (recipientCache[rowData.id]) {
            showRecipientPanel(row, recipientPanel(recipientCache[rowData.id]));
            return;
        }

        showRecipientPanel(row, '<div class="small text-muted">Loading recipients...</div>');

        var generation = logGeneration;
        $.ajax({
            url: recipientsUrl,
            type: 'GET',
            data: { broadcast_id: rowData.id },
            success: function (response) {
                recipientCache[rowData.id] = response;
                if (generation === logGeneration && row.child.isShown()) {
                    showRecipientPanel(row, recipientPanel(response));
                }
            },
            error: function (xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Could not load the recipients.';
                if (generation === logGeneration && row.child.isShown()) {
                    showRecipientPanel(row, '<div class="small text-danger">' + escapeHtml(message) + '</div>');
                }
            }
        });
    }

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

        var columns = [
            { data: 'id', className: 'text-center eb-log-col-nowrap', defaultContent: '-', render: renderText }
        ];

        if (showSubscriberFilter) {
            columns.push({ data: 'sub_id', className: 'text-center eb-log-col-nowrap', defaultContent: '-', render: renderText });
        }

        columns.push(
            { data: 'broadcast_name', className: 'text-center', defaultContent: '-', render: renderText },
            {
                data: 'recipients',
                className: 'text-center eb-log-col-num',
                defaultContent: '0',
                render: renderRecipients
            },
            {
                data: 'emails_sent',
                className: 'text-center eb-log-col-num',
                defaultContent: '0',
                render: function (data) {
                    return Number(data || 0).toLocaleString();
                }
            },
            { data: 'sent_by', className: 'text-center eb-log-col-sender', defaultContent: '-', render: renderSentBy },
            {
                data: 'datetime',
                className: 'text-center eb-log-col-nowrap',
                defaultContent: '-',
                orderData: [sortColumnIndex],
                render: renderText
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

        $('#emailBroadcastLogTable')
            .on('preDraw.dt', disposeRecipientTooltips)
            .on('draw.dt', initRecipientTooltips);

        $('#emailBroadcastLogTable tbody')
            .on('click', '.eb-log-recipients', function () {
                toggleRecipientList(this);
            })
            .on('keydown', '.eb-log-recipients', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleRecipientList(this);
                }
            });

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
                logGeneration++;
                table.clear();
                table.rows.add(rows);
                table.order([[sortColumnIndex, 'desc']]).draw();

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
                modalEl.addEventListener('hide.bs.modal', function () {
                    recipientTriggers().each(function () {
                        var tooltip = recipientTooltipFor(this);
                        if (tooltip) {
                            tooltip.hide();
                        }
                    });
                });
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
