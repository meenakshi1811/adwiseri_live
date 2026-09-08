@php
    $appStatusPayload = $applicationStatusSettings ?? [];
    $appStatusDefault = $appStatusPayload['default'] ?? ['statuses' => \App\Support\ApplicationStatuses::FLOW, 'end_date_required' => \App\Support\ApplicationStatuses::END_DATE_REQUIRED, 'has_custom' => false];
    $appStatusByCategory = $appStatusPayload['by_category'] ?? [];
    $appStatusVisaCategories = $appStatusPayload['visa_categories'] ?? [];
    $appStatusSystemDefault = $appStatusPayload['system_default_statuses'] ?? \App\Support\ApplicationStatuses::FLOW;
@endphp

<div class="tab-pane fade" id="application-status-settings" role="tabpanel" aria-labelledby="application-status-tab">
    <form id="application-status-settings-form">
        @csrf
        <div class="cc-settings-shell">
            <div class="cc-settings-hero">
                <h5><i class="fa fa-list-check me-1"></i> Application Status</h5>
                <p class="mb-0">
                    Customize the application status workflow for each visa category. The default list matches the standard flow
                    from <strong>Client Registered</strong> through <strong>Closed</strong>. Reorder, add, or remove statuses to match
                    long-term processes such as Canada or Australia PR (WES, EOI, Nomination, and so on).
                </p>
                <div class="cc-stat-row mt-3">
                    <span class="cc-stat-pill"><i class="fa fa-passport"></i> <span id="appStatusCategoryLabel">Default workflow</span></span>
                    <span class="cc-stat-pill"><i class="fa fa-list-ol"></i> <span id="appStatusCount">{{ count($appStatusDefault['statuses'] ?? []) }}</span> statuses</span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="cc-picker-card">
                        <div class="cc-picker-header">
                            <div>
                                <h6><i class="fa fa-id-card"></i> Visa Category</h6>
                                <span class="cc-picker-count">Choose which workflow to edit</span>
                            </div>
                        </div>
                        <div class="cc-picker-list">
                            <label class="cc-picker-item">
                                <input type="radio" name="app_status_category" class="app-status-category-radio" value="" checked>
                                <span>Default (all visa categories)</span>
                            </label>
                            @foreach ($appStatusVisaCategories as $category)
                                <label class="cc-picker-item" data-label="{{ strtolower($category) }}">
                                    <input type="radio" name="app_status_category" class="app-status-category-radio" value="{{ $category }}">
                                    <span>{{ $category }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="form-text px-2 pb-2">
                            Category-specific lists override the default workflow for that visa type only.
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="cc-picker-card">
                        <div class="cc-picker-header">
                            <div>
                                <h6><i class="fa fa-stream"></i> Status Sequence</h6>
                                <span class="cc-picker-count">Drag or use arrows to reorder</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 px-2 pb-2">
                            <button type="button" class="cc-tool-btn cc-tool-primary" id="appStatusUseDefaultsBtn">Use system defaults</button>
                            <button type="button" class="cc-tool-btn" id="appStatusCopyDefaultBtn">Copy from default workflow</button>
                        </div>

                        <ul class="list-group list-group-flush app-status-list mb-2" id="appStatusList"></ul>

                        <div class="input-group px-2 pb-3">
                            <input type="text" class="form-control" id="appStatusNewInput" maxlength="120" placeholder="Add new status (e.g. WES, EOI, Nomination)">
                            <button type="button" class="btn btn-outline-primary" id="appStatusAddBtn">Add</button>
                        </div>

                        <div class="px-2 pb-3">
                            <div class="form-text mb-2">
                                Check <strong>End date required</strong> for statuses that should capture an application end date.
                                Terminal statuses (<em>Withdrawn</em>, <em>Cancelled</em>, <em>Closed</em>) cannot be changed once set.
                            </div>
                            <button type="button" class="btn btn-primary" id="appStatusSaveBtn">
                                <i class="fa fa-save me-1"></i> Save Application Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .app-status-list .list-group-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: grab;
        user-select: none;
    }

    .app-status-list .list-group-item.dragging {
        opacity: 0.55;
    }

    .app-status-handle {
        color: #6c757d;
        width: 1.25rem;
        text-align: center;
    }

    .app-status-name {
        flex: 1 1 auto;
        font-weight: 500;
    }

    .app-status-actions {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .app-status-end-date-label {
        font-size: 0.85rem;
        color: #495057;
        white-space: nowrap;
    }
</style>

<script>
    (function () {
        const systemDefaultStatuses = @json($appStatusSystemDefault);
        const systemDefaultEndDateRequired = @json($appStatusPayload['system_default_end_date_required'] ?? \App\Support\ApplicationStatuses::END_DATE_REQUIRED);
        const initialPayload = @json($appStatusPayload);
        const saveUrl = @json(route('save_application_status_settings'));
        const csrfToken = @json(csrf_token());

        const state = {
            default: {
                statuses: initialPayload.default?.statuses || systemDefaultStatuses,
                end_date_required: initialPayload.default?.end_date_required || systemDefaultEndDateRequired,
            },
            by_category: initialPayload.by_category || {},
        };

        const $list = $('#appStatusList');
        const $categoryRadios = $('.app-status-category-radio');
        const $categoryLabel = $('#appStatusCategoryLabel');
        const $statusCount = $('#appStatusCount');
        const $newInput = $('#appStatusNewInput');

        function selectedCategory() {
            const value = $categoryRadios.filter(':checked').val();
            return value ? String(value) : '';
        }

        function currentConfig() {
            const category = selectedCategory();
            if (!category) {
                return state.default;
            }

            if (!state.by_category[category]) {
                state.by_category[category] = {
                    statuses: [...state.default.statuses],
                    end_date_required: [...state.default.end_date_required],
                };
            }

            return state.by_category[category];
        }

        function persistCurrentFromDom() {
            const config = currentConfig();
            const statuses = [];
            const endDateRequired = [];

            $list.find('.app-status-item').each(function () {
                const name = String($(this).data('status') || '').trim();
                if (!name) {
                    return;
                }

                statuses.push(name);
                if ($(this).find('.app-status-end-date').is(':checked')) {
                    endDateRequired.push(name);
                }
            });

            config.statuses = statuses;
            config.end_date_required = endDateRequired;
        }

        function renderList() {
            const config = currentConfig();
            const statuses = config.statuses || [];
            const endDateRequired = new Set(config.end_date_required || []);

            $list.empty();

            statuses.forEach(function (status) {
                const item = $(`
                    <li class="list-group-item app-status-item" draggable="true" data-status="${$('<div>').text(status).html()}">
                        <span class="app-status-handle" title="Drag to reorder"><i class="fa fa-grip-vertical"></i></span>
                        <span class="app-status-name"></span>
                        <div class="app-status-actions">
                            <label class="app-status-end-date-label mb-0">
                                <input type="checkbox" class="form-check-input app-status-end-date me-1">
                                End date required
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary app-status-move-up" title="Move up"><i class="fa fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-secondary app-status-move-down" title="Move down"><i class="fa fa-arrow-down"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger app-status-remove" title="Remove"><i class="fa fa-trash"></i></button>
                        </div>
                    </li>
                `);

                item.find('.app-status-name').text(status);
                item.find('.app-status-end-date').prop('checked', endDateRequired.has(status));
                $list.append(item);
            });

            $statusCount.text(statuses.length);
            const category = selectedCategory();
            $categoryLabel.text(category ? category : 'Default workflow');
        }

        function addStatus(name) {
            name = String(name || '').trim();
            if (!name) {
                return;
            }

            const config = currentConfig();
            if ((config.statuses || []).includes(name)) {
                Swal.fire({ icon: 'info', title: 'Already added', text: 'This status is already in the list.' });
                return;
            }

            config.statuses = [...(config.statuses || []), name];
            renderList();
            $newInput.val('');
        }

        function bindDragAndDrop() {
            let draggedItem = null;

            $list.on('dragstart', '.app-status-item', function () {
                draggedItem = this;
                $(this).addClass('dragging');
            });

            $list.on('dragend', '.app-status-item', function () {
                $(this).removeClass('dragging');
                draggedItem = null;
                persistCurrentFromDom();
            });

            $list.on('dragover', '.app-status-item', function (event) {
                event.preventDefault();
                if (!draggedItem || draggedItem === this) {
                    return;
                }

                const rect = this.getBoundingClientRect();
                const after = event.originalEvent.clientY > rect.top + rect.height / 2;
                if (after) {
                    this.after(draggedItem);
                } else {
                    this.before(draggedItem);
                }
            });
        }

        $categoryRadios.on('change', function () {
            persistCurrentFromDom();
            renderList();
        });

        $('#appStatusAddBtn').on('click', function () {
            addStatus($newInput.val());
        });

        $newInput.on('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addStatus($newInput.val());
            }
        });

        $list.on('click', '.app-status-remove', function () {
            persistCurrentFromDom();
            const item = $(this).closest('.app-status-item');
            const name = String(item.data('status') || '');
            const config = currentConfig();
            config.statuses = (config.statuses || []).filter(function (status) { return status !== name; });
            config.end_date_required = (config.end_date_required || []).filter(function (status) { return status !== name; });
            renderList();
        });

        $list.on('click', '.app-status-move-up', function () {
            const item = $(this).closest('.app-status-item');
            const prev = item.prev('.app-status-item');
            if (prev.length) {
                item.insertBefore(prev);
                persistCurrentFromDom();
            }
        });

        $list.on('click', '.app-status-move-down', function () {
            const item = $(this).closest('.app-status-item');
            const next = item.next('.app-status-item');
            if (next.length) {
                item.insertAfter(next);
                persistCurrentFromDom();
            }
        });

        $list.on('change', '.app-status-end-date', function () {
            persistCurrentFromDom();
        });

        $('#appStatusUseDefaultsBtn').on('click', function () {
            const config = currentConfig();
            config.statuses = [...systemDefaultStatuses];
            config.end_date_required = [...systemDefaultEndDateRequired];
            renderList();
        });

        $('#appStatusCopyDefaultBtn').on('click', function () {
            if (!selectedCategory()) {
                Swal.fire({ icon: 'info', title: 'Default selected', text: 'You are already editing the default workflow.' });
                return;
            }

            const config = currentConfig();
            config.statuses = [...state.default.statuses];
            config.end_date_required = [...state.default.end_date_required];
            renderList();
        });

        $('#appStatusSaveBtn').on('click', function () {
            persistCurrentFromDom();
            const config = currentConfig();

            if (!config.statuses || config.statuses.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No statuses', text: 'Please add at least one application status.' });
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: saveUrl,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    visa_category: selectedCategory(),
                    statuses: config.statuses,
                    end_date_required: config.end_date_required || [],
                },
                success: function (response) {
                    if (response && response.payload) {
                        state.default = response.payload.default || state.default;
                        state.by_category = response.payload.by_category || state.by_category;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: response.message || 'Application status settings saved.',
                        timer: 1800,
                        showConfirmButton: false,
                    });
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to save application status settings.';
                    Swal.fire({ icon: 'error', title: 'Save failed', text: message });
                },
                complete: function () {
                    $btn.prop('disabled', false);
                },
            });
        });

        bindDragAndDrop();
        renderList();
    })();
</script>
