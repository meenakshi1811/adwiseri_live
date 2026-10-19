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

        // Institution → Course → Course Duration → Intake: each dropdown lists only what Settings links to the
        // previous choice, and appears once that choice is made. A level with nothing set up is skipped.
        const studySteps = ['institution', 'course_name', 'course_duration', 'intake'];

        function initStudyVisaCascade(container) {
            let catalog = [];
            try {
                catalog = JSON.parse(container.getAttribute('data-catalog') || '[]');
            } catch (e) {
                catalog = [];
            }

            const steps = {};
            container.querySelectorAll('.js-study-step').forEach(function (row) {
                const select = row.querySelector('select');
                if (select) {
                    steps[row.getAttribute('data-field')] = { row: row, select: select };
                }
            });
            if (!Array.isArray(catalog) || !catalog.length || !steps.institution) {
                return;
            }

            function find(items, name) {
                const key = (name || '').trim().toLowerCase();
                return (items || []).find(function (item) {
                    return item.name.trim().toLowerCase() === key;
                }) || null;
            }

            function optionsFor(field, values) {
                if (field === 'institution') {
                    return catalog.map(function (institution) { return institution.name; });
                }
                const institution = find(catalog, values.institution);
                if (field === 'course_name') {
                    return institution ? institution.courses.map(function (course) { return course.name; }) : [];
                }
                const course = institution ? find(institution.courses, values.course_name) : null;
                if (!course) {
                    return [];
                }
                return field === 'course_duration' ? course.durations : course.intakes;
            }

            // Rebuilds a dropdown, keeping a saved value that is no longer in Settings so older applications
            // don't lose it when edited. Returns how many choices it offers.
            function fill(select, options, value) {
                const prompt = select.options.length ? select.options[0].text : '';
                const choices = options.slice();
                const current = (value || '').trim();
                if (current && !choices.some(function (option) { return option.toLowerCase() === current.toLowerCase(); })) {
                    choices.unshift(current);
                }

                select.innerHTML = '';
                select.appendChild(new Option(prompt, ''));
                choices.forEach(function (option) {
                    select.appendChild(new Option(option, option));
                });
                select.value = choices.find(function (option) {
                    return option.toLowerCase() === current.toLowerCase();
                }) || '';

                return choices.length;
            }

            function render(values) {
                let unlocked = true;
                studySteps.forEach(function (field) {
                    const step = steps[field];
                    if (!step) {
                        return;
                    }

                    const count = fill(step.select, unlocked ? optionsFor(field, values) : [], unlocked ? values[field] : '');
                    const visible = unlocked && count > 0;
                    step.row.style.display = visible ? '' : 'none';
                    values[field] = visible ? step.select.value : '';

                    if (visible && !values[field]) {
                        unlocked = false;
                    }
                });
            }

            function currentValues() {
                const values = {};
                studySteps.forEach(function (field) {
                    values[field] = steps[field] ? steps[field].select.value : '';
                });
                return values;
            }

            function savedValues() {
                const values = {};
                studySteps.forEach(function (field) {
                    values[field] = steps[field] ? (steps[field].select.getAttribute('data-current') || '') : '';
                });
                return values;
            }

            studySteps.forEach(function (field, index) {
                if (!steps[field]) {
                    return;
                }
                steps[field].select.addEventListener('change', function () {
                    const values = currentValues();
                    studySteps.slice(index + 1).forEach(function (later) {
                        values[later] = '';
                    });
                    render(values);
                });
            });

            const form = container.closest('form');
            if (form) {
                form.addEventListener('reset', function () {
                    setTimeout(function () {
                        render(savedValues());
                    }, 0);
                });
            }

            render(savedValues());
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-study-visa-cascade').forEach(initStudyVisaCascade);
            $(document).on('change', '#job_role', window.syncApplicationVisaDetailFields);
            window.syncApplicationVisaDetailFields();
        });
    })();
</script>
