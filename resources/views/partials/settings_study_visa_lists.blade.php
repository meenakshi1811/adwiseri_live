@php
    $studyVisaLists = $studyVisaLists ?? [
        'course_names' => [],
        'course_durations' => [],
        'institutions' => [],
        'intakes' => [],
    ];
    $studyListFields = [
        'course_names' => ['label' => 'Course Name', 'placeholder' => 'e.g. MSc Computer Science'],
        'course_durations' => ['label' => 'Course Duration', 'placeholder' => 'e.g. 12 months, 2 years'],
        'institutions' => ['label' => 'Institution', 'placeholder' => 'e.g. University of Example'],
        'intakes' => ['label' => 'Intake', 'placeholder' => 'e.g. September 2025'],
    ];
@endphp
<div class="cc-documents-panel mt-4" id="study-visa-lists-panel">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
        <div>
            <div class="panel-title">Study Visa Application Fields</div>
            <div class="panel-subtitle mb-0">
                Build dropdown suggestions for study visa applications (Course Name, Duration, Institution, and Intake).
                These appear on <strong>Add Application</strong> when the application type is a study visa.
            </div>
        </div>
    </div>
    <div class="row g-3">
        @foreach ($studyListFields as $key => $meta)
            @php $items = $studyVisaLists[$key] ?? []; @endphp
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white study-visa-list-card" data-list-key="{{ $key }}">
                    <label class="form-label fw-semibold mb-2">{{ $meta['label'] }}</label>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control study-visa-list-input" maxlength="255"
                            placeholder="{{ $meta['placeholder'] }}" autocomplete="off"
                            aria-label="Add {{ $meta['label'] }}">
                        <button type="button" class="btn btn-outline-primary study-visa-list-add">Add</button>
                    </div>
                    <ul class="list-group list-group-flush study-visa-list-items small mb-0">
                        @foreach ($items as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                                <span class="study-visa-list-label text-break">{{ $item }}</span>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 study-visa-list-remove" aria-label="Remove">&times;</button>
                            </li>
                        @endforeach
                    </ul>
                    @if (count($items) === 0)
                        <p class="text-muted small mb-0 study-visa-list-empty">No items yet.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div class="cc-settings-actions mt-3">
        <button type="button" class="btn btn-primary" id="save-study-visa-lists">
            <i class="fa fa-save me-1"></i> Save Study Visa Lists
        </button>
    </div>
</div>

<script>
    (function () {
        function collectStudyVisaLists() {
            const payload = {
                course_names: [],
                course_durations: [],
                institutions: [],
                intakes: [],
            };

            document.querySelectorAll('.study-visa-list-card').forEach(function (card) {
                const key = card.getAttribute('data-list-key');
                if (!payload.hasOwnProperty(key)) {
                    return;
                }
                card.querySelectorAll('.study-visa-list-label').forEach(function (label) {
                    const value = (label.textContent || '').trim();
                    if (value !== '') {
                        payload[key].push(value);
                    }
                });
            });

            return payload;
        }

        function syncStudyVisaEmptyState(card) {
            const hasItems = card.querySelectorAll('.study-visa-list-label').length > 0;
            let empty = card.querySelector('.study-visa-list-empty');
            if (!hasItems && !empty) {
                empty = document.createElement('p');
                empty.className = 'text-muted small mb-0 study-visa-list-empty';
                empty.textContent = 'No items yet.';
                card.querySelector('.study-visa-list-items').after(empty);
            } else if (hasItems && empty) {
                empty.remove();
            }
        }

        function addStudyVisaItem(card, value) {
            const trimmed = (value || '').trim();
            if (trimmed === '') {
                return false;
            }
            const existing = Array.from(card.querySelectorAll('.study-visa-list-label'))
                .some(function (el) {
                    return (el.textContent || '').trim().toLowerCase() === trimmed.toLowerCase();
                });
            if (existing) {
                return false;
            }

            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-0';
            li.innerHTML = '<span class="study-visa-list-label text-break"></span>'
                + '<button type="button" class="btn btn-link btn-sm text-danger p-0 study-visa-list-remove" aria-label="Remove">&times;</button>';
            li.querySelector('.study-visa-list-label').textContent = trimmed;
            card.querySelector('.study-visa-list-items').appendChild(li);
            syncStudyVisaEmptyState(card);
            return true;
        }

        document.querySelectorAll('.study-visa-list-add').forEach(function (button) {
            button.addEventListener('click', function () {
                const card = button.closest('.study-visa-list-card');
                const input = card.querySelector('.study-visa-list-input');
                if (addStudyVisaItem(card, input.value)) {
                    input.value = '';
                }
                input.focus();
            });
        });

        document.querySelectorAll('.study-visa-list-input').forEach(function (input) {
            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }
                event.preventDefault();
                const card = input.closest('.study-visa-list-card');
                const addBtn = card.querySelector('.study-visa-list-add');
                if (addBtn) {
                    addBtn.click();
                }
            });
        });

        document.getElementById('study-visa-lists-panel')?.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('.study-visa-list-remove');
            if (!removeBtn) {
                return;
            }
            const card = removeBtn.closest('.study-visa-list-card');
            removeBtn.closest('li')?.remove();
            syncStudyVisaEmptyState(card);
        });

        $('#save-study-visa-lists').on('click', function () {
            const lists = collectStudyVisaLists();
            $.ajax({
                url: "{{ route('save_cc_study_visa_lists') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    study_visa_lists: lists,
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Study visa lists saved successfully.',
                    });
                },
                error: function (xhr) {
                    const message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Failed to save study visa lists.';
                    Swal.fire({
                        icon: 'warning',
                        customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: message,
                    });
                },
            });
        });
    })();
</script>
