<style>
    .reports-module .nav-link.report-tab-disabled {
        opacity: 0.45;
        cursor: not-allowed !important;
        pointer-events: auto;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var availability = @json($reportModuleAvailability ?? []);
        var firstEnabled = null;

        document.querySelectorAll('.reports-module [data-report-module]').forEach(function (button) {
            var moduleKey = button.getAttribute('data-report-module');
            var hasData = availability[moduleKey] === true;

            if (!hasData || button.getAttribute('data-report-disabled') === '1') {
                button.classList.add('report-tab-disabled');
                button.removeAttribute('data-bs-toggle');
                button.removeAttribute('data-bs-target');
                button.setAttribute('aria-disabled', 'true');
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (window.AdwiseriAlert && typeof window.AdwiseriAlert.noData === 'function') {
                        window.AdwiseriAlert.noData('No data is available for this report yet.');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: 'No data is available for this report yet.'
                        });
                    } else {
                        alert('No data is available for this report yet.');
                    }
                });
                return;
            }

            if (!firstEnabled) {
                firstEnabled = button;
            }
        });

        var activeDisabled = document.querySelector('.reports-module .nav-link.active.report-tab-disabled');
        if (activeDisabled && firstEnabled) {
            activeDisabled.classList.remove('active');
            activeDisabled.setAttribute('aria-selected', 'false');
            firstEnabled.classList.add('active');
            firstEnabled.setAttribute('aria-selected', 'true');

            var target = firstEnabled.getAttribute('data-bs-target');
            if (target) {
                document.querySelectorAll('.reports-module .tab-pane').forEach(function (pane) {
                    pane.classList.remove('show', 'active');
                });
                var pane = document.querySelector(target);
                if (pane) {
                    pane.classList.add('show', 'active');
                }
            }
        }
    });
</script>
