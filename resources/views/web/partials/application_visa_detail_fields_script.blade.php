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

        // (Visa Country →) Institution → Course → Course Duration → Intake: dropdowns filled only from the
        // Settings catalogue. Institution lists the institutions set up for the form's Visa Country; each later
        // dropdown appears once the previous one is chosen and lists only what Settings links to that choice.
        // An empty list shows a message instead of options.
        const studySteps = ['institution', 'course_name', 'course_duration', 'intake'];
        const studyEmptyMessages = {
            course_name: 'No courses set up for this institution',
            course_duration: 'No durations set up for this course',
            intake: 'No intakes set up for this course',
        };

        function sameStudyValue(a, b) {
            return (a || '').trim().toLowerCase() === (b || '').trim().toLowerCase();
        }

        function studyVisaCascade(container) {
            if (container.studyVisaCascade) {
                return container.studyVisaCascade;
            }

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

            const form = container.closest('form');
            const countrySelect = form ? form.querySelector('select[name="visa_country"]') : null;
            let lastCountry = null;

            // Null when the form has no Visa Country field, in which case institutions aren't filtered.
            function selectedCountry() {
                return countrySelect ? (countrySelect.value || '').trim() : null;
            }

            function countryInstitutions() {
                const country = selectedCountry();
                if (country === null) {
                    return catalog;
                }
                return country ? catalog.filter(function (institution) {
                    return sameStudyValue(institution.country, country);
                }) : [];
            }

            function find(items, name) {
                return (items || []).find(function (item) {
                    return sameStudyValue(item.name, name);
                }) || null;
            }

            function optionsFor(field, values) {
                const institutions = countryInstitutions();
                if (field === 'institution') {
                    return institutions.map(function (institution) { return institution.name; });
                }
                const institution = find(institutions, values.institution);
                if (field === 'course_name') {
                    return institution ? institution.courses.map(function (course) { return course.name; }) : [];
                }
                const course = institution ? find(institution.courses, values.course_name) : null;
                if (!course) {
                    return [];
                }
                return field === 'course_duration' ? course.durations : course.intakes;
            }

            function emptyMessage(field) {
                if (field !== 'institution') {
                    return studyEmptyMessages[field];
                }
                if (container.getAttribute('data-empty-institutions')) {
                    return container.getAttribute('data-empty-institutions');
                }
                const country = selectedCountry();
                if (country === '') {
                    return 'Select a visa country first';
                }
                if (!catalog.length) {
                    return 'No institutions set up yet. Add them under Settings → Countries & Categories.';
                }
                return 'No institutions set up for ' + country + '. Add them under Settings → Countries & Categories.';
            }

            // Rebuilds a dropdown, keeping a saved value that is no longer in Settings so older applications
            // keep it when edited. Returns how many choices it offers.
            function fill(field, select, options, value) {
                const choices = options.slice();
                const current = (value || '').trim();
                if (current && !choices.some(function (option) { return option.toLowerCase() === current.toLowerCase(); })) {
                    choices.unshift(current);
                }

                select.innerHTML = '';
                select.appendChild(new Option(choices.length ? (select.getAttribute('data-prompt') || '') : emptyMessage(field), ''));
                choices.forEach(function (option) {
                    select.appendChild(new Option(option, option));
                });
                select.value = choices.find(function (option) {
                    return option.toLowerCase() === current.toLowerCase();
                }) || '';
                select.disabled = choices.length === 0;

                return choices.length;
            }

            function render(values) {
                lastCountry = selectedCountry();
                let previousChosen = true;
                studySteps.forEach(function (field) {
                    const step = steps[field];
                    if (!step) {
                        return;
                    }

                    if (!previousChosen) {
                        fill(field, step.select, [], '');
                        step.row.style.display = 'none';
                        return;
                    }

                    const count = fill(field, step.select, optionsFor(field, values), values[field]);
                    step.row.style.display = '';
                    values[field] = step.select.value;

                    // Intake options come from the course, so a course without durations still leads on to Intake.
                    previousChosen = count === 0 ? field === 'course_duration' : values[field] !== '';
                });
            }

            function valuesFrom(attribute) {
                const values = {};
                studySteps.forEach(function (field) {
                    const select = steps[field] ? steps[field].select : null;
                    values[field] = select ? ((attribute ? select.getAttribute(attribute) : select.value) || '') : '';
                });
                return values;
            }

            studySteps.forEach(function (field, index) {
                if (!steps[field]) {
                    return;
                }
                steps[field].select.addEventListener('change', function () {
                    const values = valuesFrom(null);
                    studySteps.slice(index + 1).forEach(function (later) {
                        values[later] = '';
                    });
                    render(values);
                });
            });

            if (form) {
                form.addEventListener('reset', function () {
                    setTimeout(function () {
                        render(valuesFrom('data-current'));
                    }, 0);
                });
            }

            // A different Visa Country starts the study fields over. Some pages replace the country options over
            // Ajax (after choosing a client), so watch for that too; the same country keeps the current choices.
            if (countrySelect) {
                const onCountryUpdate = function () {
                    render(sameStudyValue(selectedCountry(), lastCountry) ? valuesFrom(null) : {});
                };
                countrySelect.addEventListener('change', onCountryUpdate);
                if (window.MutationObserver) {
                    new MutationObserver(onCountryUpdate).observe(countrySelect, { childList: true });
                }
            }

            container.studyVisaCascade = {
                // Swap in another subscriber's catalogue (admin form, after choosing a client).
                setCatalog: function (nextCatalog, keepSavedValues, emptyInstitutionsMessage) {
                    catalog = Array.isArray(nextCatalog) ? nextCatalog : [];
                    if (emptyInstitutionsMessage) {
                        container.setAttribute('data-empty-institutions', emptyInstitutionsMessage);
                    } else {
                        container.removeAttribute('data-empty-institutions');
                    }
                    render(keepSavedValues ? valuesFrom('data-current') : {});
                },
            };

            render(valuesFrom('data-current'));
            return container.studyVisaCascade;
        }

        window.setStudyVisaCatalog = function (catalog, keepSavedValues, emptyInstitutionsMessage) {
            document.querySelectorAll('.js-study-visa-cascade').forEach(function (container) {
                studyVisaCascade(container).setCatalog(catalog, keepSavedValues, emptyInstitutionsMessage);
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-study-visa-cascade').forEach(studyVisaCascade);
            $(document).on('change', '#job_role', window.syncApplicationVisaDetailFields);
            window.syncApplicationVisaDetailFields();
        });
    })();
</script>
