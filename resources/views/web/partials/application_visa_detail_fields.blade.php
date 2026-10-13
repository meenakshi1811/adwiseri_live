@php
    $layout = $layout ?? 'row';
    $application = $application ?? null;
    $studyVisaOptions = $studyVisaOptions ?? [];
    $fieldValue = function (string $field) use ($application) {
        $value = old($field, $application->{$field} ?? 'NA');

        return ($value === 'NA') ? '' : $value;
    };
@endphp

@if ($layout === 'modal')
    <div class="js-study-visa-fields" style="display: none;">
        <div class="mb-3">
            <label class="form-label">Course Name</label>
            @include('partials.study_visa_list_input', [
                'layout' => 'modal',
                'studyVisaOptions' => $studyVisaOptions,
                'optionKey' => 'course_names',
                'inputName' => 'course_name',
                'inputId' => 'course_name_modal',
                'placeholder' => 'Course Name',
                'value' => $fieldValue('course_name'),
            ])
        </div>
        <div class="mb-3">
            <label class="form-label">Course Duration</label>
            @include('partials.study_visa_list_input', [
                'layout' => 'modal',
                'studyVisaOptions' => $studyVisaOptions,
                'optionKey' => 'course_durations',
                'inputName' => 'course_duration',
                'inputId' => 'course_duration_modal',
                'placeholder' => 'e.g. 12 months, 2 years',
                'value' => $fieldValue('course_duration'),
            ])
        </div>
        <div class="mb-3">
            <label class="form-label">Institution</label>
            @include('partials.study_visa_list_input', [
                'layout' => 'modal',
                'studyVisaOptions' => $studyVisaOptions,
                'optionKey' => 'institutions',
                'inputName' => 'institution',
                'inputId' => 'institution_modal',
                'placeholder' => 'University / College',
                'value' => $fieldValue('institution'),
            ])
        </div>
        <div class="mb-3">
            <label class="form-label">Intake</label>
            @include('partials.study_visa_list_input', [
                'layout' => 'modal',
                'studyVisaOptions' => $studyVisaOptions,
                'optionKey' => 'intakes',
                'inputName' => 'intake',
                'inputId' => 'intake_modal',
                'placeholder' => 'e.g. September 2025',
                'value' => $fieldValue('intake'),
            ])
        </div>
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
    <div class="col-12 p-0 js-study-visa-fields" style="display: none;">
        <div class="row w-100 mx-0">
        <div class="col-md-4 p-1">
            <label>Course Name</label>
        </div>
        <div class="col-md-8 p-1">
            @include('partials.study_visa_list_input', [
                'studyVisaOptions' => $studyVisaOptions,
                'optionKey' => 'course_names',
                'inputName' => 'course_name',
                'inputId' => 'course_name',
                'placeholder' => 'Course Name',
                'value' => $fieldValue('course_name'),
            ])
        </div>
        <div class="col-md-4 p-1">
            <label>Course Duration</label>
        </div>
        <div class="col-md-8 p-1">
            @include('partials.study_visa_list_input', [
                'studyVisaOptions' => $studyVisaOptions,
                'optionKey' => 'course_durations',
                'inputName' => 'course_duration',
                'inputId' => 'course_duration',
                'placeholder' => 'e.g. 12 months, 2 years',
                'value' => $fieldValue('course_duration'),
            ])
        </div>
        <div class="col-md-4 p-1">
            <label>Institution</label>
        </div>
        <div class="col-md-8 p-1">
            @include('partials.study_visa_list_input', [
                'studyVisaOptions' => $studyVisaOptions,
                'optionKey' => 'institutions',
                'inputName' => 'institution',
                'inputId' => 'institution',
                'placeholder' => 'University / College',
                'value' => $fieldValue('institution'),
            ])
        </div>
        <div class="col-md-4 p-1">
            <label>Intake</label>
        </div>
        <div class="col-md-8 p-1">
            @include('partials.study_visa_list_input', [
                'studyVisaOptions' => $studyVisaOptions,
                'optionKey' => 'intakes',
                'inputName' => 'intake',
                'inputId' => 'intake',
                'placeholder' => 'e.g. September 2025',
                'value' => $fieldValue('intake'),
            ])
        </div>
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
