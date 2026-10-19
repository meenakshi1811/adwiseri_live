@php
    $layout = $layout ?? 'row';
    $application = $application ?? null;
    $studyVisaOptions = $studyVisaOptions ?? [];
    $fieldValue = function (string $field) use ($application) {
        $value = old($field, $application->{$field} ?? 'NA');

        return ($value === 'NA') ? '' : $value;
    };

    // Institution → Course → Course Duration → Intake: dropdowns filled only from the Settings catalogue,
    // each shown once the previous one is chosen. `studyEmptyInstitutions` replaces the Institution
    // message before a catalogue is loaded (e.g. "Select a client first" on the admin form).
    $studyCatalog = $studyVisaOptions['institutions'] ?? [];
    $studyEmptyInstitutions = $studyEmptyInstitutions ?? '';
    $studySteps = [
        ['field' => 'institution', 'label' => 'Institution', 'prompt' => 'Select Institution'],
        ['field' => 'course_name', 'label' => 'Course', 'prompt' => 'Select Course'],
        ['field' => 'course_duration', 'label' => 'Course Duration', 'prompt' => 'Select Course Duration'],
        ['field' => 'intake', 'label' => 'Intake', 'prompt' => 'Select Intake'],
    ];
    $studyHint = 'These lists come from Settings → Countries & Categories.';
@endphp

@if ($layout === 'modal')
    <div class="js-study-visa-fields js-study-visa-cascade" style="display: none;" data-catalog="{{ json_encode($studyCatalog) }}"
        data-empty-institutions="{{ $studyEmptyInstitutions }}">
        @foreach ($studySteps as $step)
            <div class="mb-3 js-study-step" data-field="{{ $step['field'] }}">
                <label class="form-label" for="{{ $step['field'] }}_modal">{{ $step['label'] }}</label>
                @include('partials.study_visa_list_input', [
                    'layout' => 'modal',
                    'inputName' => $step['field'],
                    'inputId' => $step['field'] . '_modal',
                    'prompt' => $step['prompt'],
                    'value' => $fieldValue($step['field']),
                    'hint' => $loop->first ? $studyHint : null,
                ])
            </div>
        @endforeach
        <div class="mb-3">
            <label class="form-label">Admission Number (CAS / I-20)</label>
            <input type="text" name="admission_number" maxlength="255"
                class="form-control @error('admission_number') is-invalid @enderror"
                value="{{ $fieldValue('admission_number') }}" placeholder="CAS / I-20 number">
            @error('admission_number')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
        </div>
    </div>

    <div class="js-work-visa-fields" style="display: none;">
        <div class="mb-3">
            <label class="form-label">Employer Name</label>
            <input type="text" name="employer_name" maxlength="255"
                class="form-control @error('employer_name') is-invalid @enderror"
                value="{{ $fieldValue('employer_name') }}" placeholder="Employer Name">
            @error('employer_name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Job Role</label>
            <input type="text" name="employment_role" maxlength="255"
                class="form-control @error('employment_role') is-invalid @enderror"
                value="{{ $fieldValue('employment_role') }}" placeholder="Job Role">
            @error('employment_role')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Permit Duration</label>
            <input type="text" name="permit_duration" maxlength="255"
                class="form-control @error('permit_duration') is-invalid @enderror"
                value="{{ $fieldValue('permit_duration') }}" placeholder="e.g. 2 years, 3 years">
            @error('permit_duration')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Sponsorship (Permit) No. (LMIA / COS)</label>
            <input type="text" name="sponsor_number" maxlength="255"
                class="form-control @error('sponsor_number') is-invalid @enderror"
                value="{{ $fieldValue('sponsor_number') }}" placeholder="LMIA / COS number">
            @error('sponsor_number')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
        </div>
    </div>
@else
    <div class="col-12 p-0 js-study-visa-fields js-study-visa-cascade" style="display: none;" data-catalog="{{ json_encode($studyCatalog) }}"
        data-empty-institutions="{{ $studyEmptyInstitutions }}">
        <div class="row w-100 mx-0">
        @foreach ($studySteps as $step)
            <div class="col-12 p-0 js-study-step" data-field="{{ $step['field'] }}">
                <div class="row w-100 mx-0">
                    <div class="col-md-4 p-1">
                        <label for="{{ $step['field'] }}">{{ $step['label'] }}</label>
                    </div>
                    <div class="col-md-8 p-1">
                        @include('partials.study_visa_list_input', [
                            'inputName' => $step['field'],
                            'inputId' => $step['field'],
                            'prompt' => $step['prompt'],
                            'value' => $fieldValue($step['field']),
                            'hint' => $loop->first ? $studyHint : null,
                        ])
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-md-4 p-1">
            <label>Admission Number (CAS / I-20)</label>
        </div>
        <div class="col-md-8 p-1">
            <input type="text" name="admission_number" maxlength="255"
                class="form-control @error('admission_number') is-invalid @enderror"
                value="{{ $fieldValue('admission_number') }}" placeholder="CAS / I-20 number">
            @error('admission_number')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
        </div>
        </div>
    </div>

    <div class="col-12 p-0 js-work-visa-fields" style="display: none;">
        <div class="row w-100 mx-0">
        <div class="col-md-4 p-1">
            <label>Employer Name</label>
        </div>
        <div class="col-md-8 p-1">
            <input type="text" name="employer_name" maxlength="255"
                class="form-control @error('employer_name') is-invalid @enderror"
                value="{{ $fieldValue('employer_name') }}" placeholder="Employer Name">
            @error('employer_name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="col-md-4 p-1">
            <label>Job Role</label>
        </div>
        <div class="col-md-8 p-1">
            <input type="text" name="employment_role" maxlength="255"
                class="form-control @error('employment_role') is-invalid @enderror"
                value="{{ $fieldValue('employment_role') }}" placeholder="Job Role">
            @error('employment_role')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="col-md-4 p-1">
            <label>Permit Duration</label>
        </div>
        <div class="col-md-8 p-1">
            <input type="text" name="permit_duration" maxlength="255"
                class="form-control @error('permit_duration') is-invalid @enderror"
                value="{{ $fieldValue('permit_duration') }}" placeholder="e.g. 2 years, 3 years">
            @error('permit_duration')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="col-md-4 p-1">
            <label>Sponsorship (Permit) No. (LMIA / COS)</label>
        </div>
        <div class="col-md-8 p-1">
            <input type="text" name="sponsor_number" maxlength="255"
                class="form-control @error('sponsor_number') is-invalid @enderror"
                value="{{ $fieldValue('sponsor_number') }}" placeholder="LMIA / COS number">
            @error('sponsor_number')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
        </div>
        </div>
    </div>
@endif
