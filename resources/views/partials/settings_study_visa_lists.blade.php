@php
    $studyVisaLists = $studyVisaLists ?? [];
    $studyInstitutions = $studyVisaLists['institutions'] ?? [];
    // The subscriber's countries (Countries & Categories), plus any already used by a saved institution.
    $studyCountries = collect($studyCountries ?? [])
        ->merge(collect($studyInstitutions)->pluck('country'))
        ->map(fn ($country) => trim((string) $country))
        ->filter()
        ->unique()
        ->values()
        ->all();
    $studyUnlinked = array_filter([
        'Courses' => $studyVisaLists['unlinked']['course_names'] ?? [],
        'Durations' => $studyVisaLists['unlinked']['course_durations'] ?? [],
        'Intakes' => $studyVisaLists['unlinked']['intakes'] ?? [],
    ]);
@endphp
<style>
    #study-visa-lists-panel .study-visa-country-heading {
        color: var(--adwiseri-primary, #695EEE);
        font-weight: 600;
        border-bottom: 1px solid #E5E8FF;
        padding-bottom: 0.25rem;
        margin: 1rem 0 0.75rem;
    }

    #study-visa-lists-panel .study-visa-course {
        border-left: 3px solid #E5E8FF;
    }

    #study-visa-lists-panel .study-visa-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #F7F8FF;
        border: 1px solid #E5E8FF;
        border-radius: 999px;
        padding: 0.15rem 0.6rem;
        margin: 0 0.35rem 0.35rem 0;
        font-size: 0.8rem;
    }

    #study-visa-lists-panel .study-visa-chip .btn-close {
        font-size: 0.5rem;
    }

    #study-visa-lists-panel .study-visa-country-select {
        width: auto;
        max-width: 220px;
    }
</style>
<div class="cc-documents-panel mt-4" id="study-visa-lists-panel">
    <div class="mb-3">
        <div class="panel-title">Study Visa Application Fields</div>
        <div class="panel-subtitle mb-0">
            For each country, add the institutions your clients apply to, the courses each one offers, and each course's
            durations and intakes. On <strong>Add Application</strong> (study visa types) staff choose the Visa Country, then
            Institution → Course → Course Duration → Intake, and each list only shows what belongs to the previous choice.
        </div>
    </div>

    @if (count($studyUnlinked) > 0)
        <div class="alert alert-warning small py-2" id="study-visa-unlinked">
            These were saved before courses were linked to institutions. Add them under the right institution below and save;
            this note then disappears.
            @foreach ($studyUnlinked as $label => $values)
                <div class="mt-1"><strong>{{ $label }}:</strong> {{ implode(', ', $values) }}</div>
            @endforeach
        </div>
    @endif

    <div class="alert alert-warning small py-2" id="study-visa-unassigned-note" style="display: none;">
        Some institutions have no country yet, so they don't appear on Add Application.
        Choose a country for each one under <strong>No country assigned</strong> below, then save.
    </div>

    @if (count($studyCountries) === 0)
        <p class="text-muted small">Select your countries under Countries &amp; Categories above first, then add institutions here.</p>
    @endif

    <div class="input-group input-group-sm mb-3" style="max-width: 720px;">
        <select class="form-select" style="max-width: 220px;" aria-label="Institution country" data-add-country="institution">
            <option value="">Select country</option>
            @foreach ($studyCountries as $country)
                <option value="{{ $country }}">{{ $country }}</option>
            @endforeach
        </select>
        <input type="text" class="form-control" maxlength="255" placeholder="Institution, e.g. University of Example"
            aria-label="Institution name" data-add-input="institution" autocomplete="off">
        <button type="button" class="btn btn-outline-primary" data-action="add-institution">Add Institution</button>
    </div>

    <div id="study-visa-institutions"></div>
    <p class="text-muted small mb-0" id="study-visa-institutions-empty">No institutions yet.</p>

    <div class="cc-settings-actions mt-3">
        <button type="button" class="btn btn-primary" id="save-study-visa-lists">
            <i class="fa fa-save me-1"></i> Save Study Visa Lists
        </button>
    </div>
</div>

<script>
    (function () {
        const panel = document.getElementById('study-visa-lists-panel');
        if (!panel) {
            return;
        }

        const list = document.getElementById('study-visa-institutions');
        const emptyNote = document.getElementById('study-visa-institutions-empty');
        const unassignedNote = document.getElementById('study-visa-unassigned-note');
        const countries = @json($studyCountries);
        let institutions = @json($studyInstitutions);

        function el(tag, className, text) {
            const node = document.createElement(tag);
            if (className) {
                node.className = className;
            }
            if (text !== undefined) {
                node.textContent = text;
            }
            return node;
        }

        function same(a, b) {
            return (a || '').trim().toLowerCase() === (b || '').trim().toLowerCase();
        }

        function hasName(items, value) {
            return items.some(function (item) {
                return same(typeof item === 'string' ? item : item.name, value);
            });
        }

        function hasInstitution(name, country, exceptIndex) {
            return institutions.some(function (institution, index) {
                return index !== exceptIndex && same(institution.name, name) && same(institution.country, country);
            });
        }

        function warn(text) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: text });
            } else {
                window.alert(text);
            }
        }

        // Text box + button that adds a value; Enter in the box does the same.
        function addRow(placeholder, buttonText, action, data, focusKey) {
            const group = el('div', 'input-group input-group-sm');
            const input = el('input', 'form-control');
            input.type = 'text';
            input.maxLength = 255;
            input.placeholder = placeholder;
            input.autocomplete = 'off';
            input.setAttribute('aria-label', placeholder);
            input.dataset.focusKey = focusKey;
            const button = el('button', 'btn btn-outline-primary', buttonText);
            button.type = 'button';
            button.dataset.action = action;
            Object.keys(data).forEach(function (key) {
                button.dataset[key] = data[key];
            });
            group.appendChild(input);
            group.appendChild(button);
            return group;
        }

        function countrySelect(institution, i) {
            const select = el('select', 'form-select form-select-sm study-visa-country-select');
            select.setAttribute('aria-label', 'Country of ' + institution.name);
            select.dataset.i = i;
            const choices = countries.slice();
            if (institution.country && !choices.some(function (c) { return same(c, institution.country); })) {
                choices.unshift(institution.country);
            }
            if (!institution.country) {
                select.appendChild(new Option('Select country', ''));
            }
            choices.forEach(function (country) {
                select.appendChild(new Option(country, country));
            });
            select.value = institution.country || '';
            return select;
        }

        function valueList(title, values, kind, i, c) {
            const wrap = el('div', 'col-md-6');
            wrap.appendChild(el('div', 'small fw-semibold mb-1', title));
            const chips = el('div');
            values.forEach(function (value, v) {
                const chip = el('span', 'study-visa-chip');
                chip.appendChild(el('span', 'text-break', value));
                const remove = el('button', 'btn-close');
                remove.type = 'button';
                remove.setAttribute('aria-label', 'Remove ' + value);
                Object.assign(remove.dataset, { action: 'remove-value', kind: kind, i: i, c: c, v: v });
                chip.appendChild(remove);
                chips.appendChild(chip);
            });
            if (!values.length) {
                chips.appendChild(el('div', 'text-muted small mb-1', 'None yet.'));
            }
            wrap.appendChild(chips);
            const singular = kind === 'durations' ? 'duration, e.g. 12 months' : 'intake, e.g. September 2026';
            wrap.appendChild(addRow('Add ' + singular, 'Add', 'add-value', { kind: kind, i: i, c: c }, kind + '-' + i + '-' + c));
            return wrap;
        }

        function institutionCard(institution, i) {
            const card = el('div', 'border rounded p-3 mb-3 bg-white');
            const header = el('div', 'd-flex flex-wrap justify-content-between align-items-center gap-2 mb-2');
            header.appendChild(el('div', 'fw-semibold text-break', institution.name));
            const tools = el('div', 'd-flex align-items-center gap-2');
            tools.appendChild(countrySelect(institution, i));
            const removeInstitution = el('button', 'btn btn-link btn-sm text-danger p-0', 'Remove');
            removeInstitution.type = 'button';
            Object.assign(removeInstitution.dataset, { action: 'remove-institution', i: i });
            tools.appendChild(removeInstitution);
            header.appendChild(tools);
            card.appendChild(header);

            institution.courses.forEach(function (course, c) {
                const block = el('div', 'study-visa-course ps-3 mb-3');
                const courseHeader = el('div', 'd-flex justify-content-between align-items-start gap-2');
                courseHeader.appendChild(el('div', 'small fw-semibold text-break', course.name));
                const removeCourse = el('button', 'btn-close');
                removeCourse.type = 'button';
                removeCourse.setAttribute('aria-label', 'Remove ' + course.name);
                Object.assign(removeCourse.dataset, { action: 'remove-course', i: i, c: c });
                courseHeader.appendChild(removeCourse);
                block.appendChild(courseHeader);

                const values = el('div', 'row g-3 mt-0');
                values.appendChild(valueList('Course Durations', course.durations, 'durations', i, c));
                values.appendChild(valueList('Intakes', course.intakes, 'intakes', i, c));
                block.appendChild(values);
                card.appendChild(block);
            });

            if (!institution.courses.length) {
                card.appendChild(el('p', 'text-muted small mb-2', 'No courses yet.'));
            }

            const addCourse = addRow('Add a course, e.g. MSc Computer Science', 'Add Course', 'add-course', { i: i }, 'course-' + i);
            addCourse.style.maxWidth = '520px';
            card.appendChild(addCourse);
            return card;
        }

        // Countries in the subscriber's order, then any others in use, then institutions without a country.
        function countryGroups() {
            const order = countries.slice();
            institutions.forEach(function (institution) {
                if (institution.country && !order.some(function (c) { return same(c, institution.country); })) {
                    order.push(institution.country);
                }
            });
            order.push('');

            return order.map(function (country) {
                return {
                    country: country,
                    indexes: institutions.reduce(function (found, institution, i) {
                        if (same(institution.country, country)) {
                            found.push(i);
                        }
                        return found;
                    }, []),
                };
            }).filter(function (group) {
                return group.indexes.length > 0;
            });
        }

        function render(focusKey) {
            list.innerHTML = '';
            emptyNote.style.display = institutions.length ? 'none' : '';
            unassignedNote.style.display = institutions.some(function (institution) { return !institution.country; }) ? '' : 'none';

            countryGroups().forEach(function (group) {
                list.appendChild(el('div', 'study-visa-country-heading', group.country || 'No country assigned'));
                group.indexes.forEach(function (i) {
                    list.appendChild(institutionCard(institutions[i], i));
                });
            });

            if (focusKey) {
                panel.querySelector('[data-focus-key="' + focusKey + '"]')?.focus();
            }
        }

        function inputFor(button) {
            return button.closest('.input-group').querySelector('input');
        }

        panel.addEventListener('click', function (event) {
            const button = event.target.closest('[data-action]');
            if (!button || !panel.contains(button)) {
                return;
            }

            const action = button.dataset.action;
            const i = Number(button.dataset.i);
            const c = Number(button.dataset.c);

            if (action === 'add-institution') {
                const input = inputFor(button);
                const countryField = button.closest('.input-group').querySelector('[data-add-country]');
                const name = (input.value || '').trim();
                const country = countryField.value;
                if (!country) {
                    warn('Select the country this institution is in.');
                    countryField.focus();
                    return;
                }
                if (name === '') {
                    input.focus();
                    return;
                }
                if (!hasInstitution(name, country, -1)) {
                    institutions.push({ name: name, country: country, courses: [] });
                }
                input.value = '';
                input.focus();
                render();
                return;
            }

            if (action === 'add-course' || action === 'add-value') {
                const input = inputFor(button);
                const value = (input.value || '').trim();
                if (value === '') {
                    input.focus();
                    return;
                }

                const target = action === 'add-course'
                    ? institutions[i].courses
                    : institutions[i].courses[c][button.dataset.kind];
                if (!hasName(target, value)) {
                    target.push(action === 'add-course' ? { name: value, durations: [], intakes: [] } : value);
                }
                input.value = '';
                render(input.dataset.focusKey);
                return;
            }

            if (action === 'remove-institution') {
                institutions.splice(i, 1);
            } else if (action === 'remove-course') {
                institutions[i].courses.splice(c, 1);
            } else if (action === 'remove-value') {
                institutions[i].courses[c][button.dataset.kind].splice(Number(button.dataset.v), 1);
            }
            render();
        });

        panel.addEventListener('change', function (event) {
            const select = event.target.closest('.study-visa-country-select');
            if (!select) {
                return;
            }

            const i = Number(select.dataset.i);
            if (select.value && hasInstitution(institutions[i].name, select.value, i)) {
                warn(institutions[i].name + ' is already listed under ' + select.value + '.');
            } else {
                institutions[i].country = select.value;
            }
            render();
        });

        panel.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' || !event.target.matches('.input-group input')) {
                return;
            }
            event.preventDefault();
            event.target.closest('.input-group').querySelector('[data-action]')?.click();
        });

        document.getElementById('save-study-visa-lists').addEventListener('click', function () {
            fetch("{{ route('save_cc_study_visa_lists') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                body: JSON.stringify({ study_visa_lists: { institutions: institutions } }),
            })
                .then(function (response) {
                    return response.json().catch(function () {
                        return {};
                    }).then(function (data) {
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to save study visa lists.');
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    if (data.study_visa_lists) {
                        institutions = data.study_visa_lists.institutions || [];
                        render();
                    }
                    document.getElementById('study-visa-unlinked')?.remove();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message || 'Study visa lists saved successfully.',
                    });
                })
                .catch(function (error) {
                    warn(error.message || 'Failed to save study visa lists.');
                });
        });

        render();
    })();
</script>
