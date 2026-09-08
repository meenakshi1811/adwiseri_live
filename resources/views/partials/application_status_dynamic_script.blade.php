<script>
    (function () {
        if (window.adwiseriApplicationStatusFlows) {
            return;
        }

        window.adwiseriApplicationStatusFlows = {
            flows: @json($statusFlowsByCategory ?? []),
            endDateRequired: @json($endDateRequiredByCategory ?? []),
            defaultKey: @json(\App\Services\ApplicationStatusSettingsService::DEFAULT_CATEGORY_KEY),
        };

        window.adwiseriResolveApplicationStatusFlow = function (visaCategory) {
            const config = window.adwiseriApplicationStatusFlows || {};
            const flows = config.flows || {};
            const key = String(visaCategory || '').trim();
            if (key && Array.isArray(flows[key]) && flows[key].length) {
                return flows[key];
            }

            const defaultFlow = flows[config.defaultKey] || [];
            return defaultFlow.length ? defaultFlow : @json(\App\Support\ApplicationStatuses::FLOW);
        };

        window.adwiseriResolveApplicationEndDateRequired = function (visaCategory) {
            const config = window.adwiseriApplicationStatusFlows || {};
            const map = config.endDateRequired || {};
            const key = String(visaCategory || '').trim();
            if (key && Array.isArray(map[key]) && map[key].length) {
                return map[key];
            }

            const defaults = map[config.defaultKey] || [];
            return defaults.length ? defaults : @json(\App\Support\ApplicationStatuses::END_DATE_REQUIRED);
        };

        window.adwiseriRefreshApplicationStatusSelect = function (options) {
            const settings = Object.assign({
                categoryField: '#job_role',
                statusField: '.js-app-status',
                preserveValue: true,
            }, options || {});

            const $category = $(settings.categoryField);
            const $status = $(settings.statusField).first();
            if (!$status.length) {
                return;
            }

            const visaCategory = $category.length ? String($category.val() || '').trim() : '';
            const flow = window.adwiseriResolveApplicationStatusFlow(visaCategory);
            const previous = settings.preserveValue ? String($status.val() || '') : '';
            const currentStatus = previous || 'Client Registered';
            const currentIndex = flow.indexOf(currentStatus);
            const isTerminal = ['Withdrawn', 'Cancelled', 'Closed'].includes(currentStatus);

            let html = '<option value="">Select Application Status</option>';
            flow.forEach(function (status, index) {
                const selected = currentStatus === status ? ' selected' : '';
                const disabled = (currentIndex !== -1 && index < currentIndex) || (isTerminal && status !== currentStatus)
                    ? ' disabled'
                    : '';
                html += '<option value="' + $('<div>').text(status).html() + '"' + selected + disabled + '>' +
                    $('<div>').text(status).html() + '</option>';
            });

            $status.html(html);

            if (typeof window.adwiseriSyncApplicationEndDateField === 'function') {
                window.adwiseriSyncApplicationEndDateField({
                    visaCategory: visaCategory,
                    statusField: settings.statusField,
                    endDateField: settings.endDateField || '.js-app-end-date',
                });
            }
        };

        window.adwiseriSyncApplicationEndDateField = function (options) {
            const settings = Object.assign({
                visaCategory: '',
                statusField: '.js-app-status',
                endDateField: '.js-app-end-date',
            }, options || {});

            const $status = $(settings.statusField).first();
            const $endDate = $(settings.endDateField).first();
            if (!$status.length || !$endDate.length) {
                return;
            }

            const requiredStatuses = window.adwiseriResolveApplicationEndDateRequired(settings.visaCategory);
            const canEdit = requiredStatuses.includes($status.val());
            $endDate.prop('readonly', !canEdit);
            $endDate.prop('disabled', !canEdit);
            if (!canEdit) {
                $endDate.val('');
            }
        };

        $(document).on('change', '#job_role', function () {
            window.adwiseriRefreshApplicationStatusSelect({
                categoryField: '#job_role',
                statusField: '.js-app-status',
                preserveValue: false,
            });
        });

        $(document).on('change', '.js-app-status', function () {
            const visaCategory = String($('#job_role').val() || '').trim();
            window.adwiseriSyncApplicationEndDateField({
                visaCategory: visaCategory,
                statusField: '.js-app-status',
                endDateField: '.js-app-end-date',
            });
        });
    })();
</script>
