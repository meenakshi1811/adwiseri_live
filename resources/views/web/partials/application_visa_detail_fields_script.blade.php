<script>
    (function () {
        function isStudyVisaCategory(type) {
            const normalized = (type || '').toLowerCase().trim();
            return normalized.includes('study') || normalized.includes('student');
        }

        function isNonSponsoredWorkRelatedCategory(normalized) {
            return normalized.includes('working holiday')
                || normalized.includes('holiday maker');
        }

        function isWorkVisaCategory(type) {
            const normalized = (type || '').toLowerCase().trim();
            if (!normalized || isNonSponsoredWorkRelatedCategory(normalized)) {
                return false;
            }

            return normalized.includes('work visa')
                || normalized.includes('employment')
                || normalized.includes('sponsored')
                || normalized.includes('work permit');
        }

        function currentApplicationType() {
            const select = document.getElementById('job_role');
            if (select && select.value) {
                return select.value;
            }

            const hidden = document.querySelector('input[name="job_role"]');
            return hidden ? hidden.value : '';
        }

        window.syncApplicationVisaDetailFields = function () {
            const type = currentApplicationType();
            const showStudy = isStudyVisaCategory(type);
            const showWork = isWorkVisaCategory(type);

            document.querySelectorAll('.js-study-visa-fields').forEach(function (el) {
                el.style.display = showStudy ? '' : 'none';
            });
            document.querySelectorAll('.js-work-visa-fields').forEach(function (el) {
                el.style.display = showWork ? '' : 'none';
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            $(document).on('change', '#job_role', window.syncApplicationVisaDetailFields);
            window.syncApplicationVisaDetailFields();
        });
    })();
</script>
