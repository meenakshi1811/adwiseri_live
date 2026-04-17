@extends('web.layout.main')

@section('main-section')

<div class="container mt-5 mb-5 pt-5">
<div class="row">

<div class="col-lg-2"></div>

<div class="col-lg-8 loginouter-box login-box">
@if(session('success'))

<div class="alert alert-success">
{{ session('success') }}
</div>

@endif


@if(session('error'))

<div class="alert alert-danger">
{{ session('error') }}
</div>

@endif
@php
$isEdit = $isEdit ?? false;
@endphp
<form method="POST" action="{{ $isEdit ? route('visa_enquiries.update', $enquiry->id) : route('visa.enquiry.store') }}">
@csrf

<input type="hidden" name="subscriber_id" value="{{ $subscriberId }}">

<h3 class="mb-4 text-center">{{ $isEdit ? 'Edit Visa / Immigration Enquiry' : 'Visa / Immigration Enquiry Form' }}</h3>

<h5 class="mt-3">1. Personal Details</h5>

<div class="row">

<div class="col-md-6 mb-3">
<label>Full Name *</label>
<input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name', $enquiry->full_name ?? '') }}" required>
</div>

<div class="col-md-6 mb-3">
<label>DOB</label>
<input type="text" name="dob" class="form-control datepicker" value="{{ old('dob', $enquiry->dob ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<label>Email *</label>
<input type="email" name="email" class="form-control" value="{{ old('email', $enquiry->email ?? '') }}" required>
</div>

<div class="col-md-6 mb-3">
<label>Contact No</label>
<input type="text" name="contact_no" class="form-control" value="{{ old('contact_no', $enquiry->contact_no ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<label>Marital Status</label>
<select name="marital_status" id="marital_status" class="form-control">
<option value="">Select</option>
<option {{ old('marital_status', $enquiry->marital_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
<option {{ old('marital_status', $enquiry->marital_status ?? '') == 'Engaged' ? 'selected' : '' }}>Engaged</option>
<option {{ old('marital_status', $enquiry->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
<option {{ old('marital_status', $enquiry->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
<option {{ old('marital_status', $enquiry->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
</select>
</div>

<div class="col-md-12 mb-3">
<label>Address *</label>
<textarea name="address" class="form-control">{{ old('address', $enquiry->address ?? '') }}</textarea>
</div>

</div>

<h5 class="mt-4">2. Country & Visa Preference</h5>

<div class="row">

<div class="col-md-4">
<input type="text" name="country_pref[]" class="form-control" placeholder="1st Preference" value="{{ old('country_pref.0', $enquiry->country_pref_1 ?? '') }}">
</div>

<div class="col-md-4">
<input type="text" name="country_pref[]" class="form-control" placeholder="2nd Preference" value="{{ old('country_pref.1', $enquiry->country_pref_2 ?? '') }}">
</div>

<div class="col-md-4">
<input type="text" name="country_pref[]" class="form-control" placeholder="3rd Preference" value="{{ old('country_pref.2', $enquiry->country_pref_3 ?? '') }}">
</div>

<div class="col-md-6 mt-3">
<label>Visa Category</label>
<select name="visa_category" id="visa_category" class="form-control">
<option value="">Select</option>
<option value="Visit" {{ old('visa_category', $enquiry->visa_category ?? '') == 'Visit' ? 'selected' : '' }}>Visit</option>
<option value="Training" {{ old('visa_category', $enquiry->visa_category ?? '') == 'Training' ? 'selected' : '' }}>Training</option>
<option value="Study" {{ old('visa_category', $enquiry->visa_category ?? '') == 'Study' ? 'selected' : '' }}>Study</option>
<option value="Work" {{ old('visa_category', $enquiry->visa_category ?? '') == 'Work' ? 'selected' : '' }}>Work</option>
<option value="Dependent" {{ old('visa_category', $enquiry->visa_category ?? '') == 'Dependent' ? 'selected' : '' }}>Dependent</option>
<option value="PR" {{ old('visa_category', $enquiry->visa_category ?? '') == 'PR' ? 'selected' : '' }}>PR</option>
<option value="Business" {{ old('visa_category', $enquiry->visa_category ?? '') == 'Business' ? 'selected' : '' }}>Business</option>
<option value="Investor" {{ old('visa_category', $enquiry->visa_category ?? '') == 'Investor' ? 'selected' : '' }}>Investor</option>
</select>
</div>

</div>

<h5 class="mt-4">3. Abroad Residency History</h5>

<div id="residency_history">
@php $resRows = old('res_country') ? collect(old('res_country'))->map(fn($_, $i) => ['country' => old('res_country.'.$i), 'duration' => old('res_duration.'.$i), 'visa_category' => old('res_visa.'.$i)]) : ($enquiry->residencyHistory ?? collect([[]])); @endphp
@foreach($resRows as $idx => $row)
<div class="row residency-row {{ $idx > 0 ? 'mt-2' : '' }}">
<div class="col-md-3">
<input type="text" name="res_country[]" class="form-control" placeholder="Country" value="{{ $row['country'] ?? $row->country ?? '' }}">
</div>
<div class="col-md-3">
<input type="text" name="res_duration[]" class="form-control" placeholder="Duration" value="{{ $row['duration'] ?? $row->duration ?? '' }}">
</div>
<div class="col-md-3">
<input type="text" name="res_visa[]" class="form-control" placeholder="Visa Category" value="{{ $row['visa_category'] ?? $row->visa_category ?? '' }}">
</div>
<div class="col-md-3">
@if($idx === 0)
<button type="button" class="btn btn-success addResidency">+</button>
@else
<button type="button" class="btn btn-danger remove">-</button>
@endif
</div>
</div>
@endforeach

</div>

<h5 class="mt-4">4. Travel History</h5>

<div id="travel_history">
@php $travelRows = old('travel_country') ? collect(old('travel_country'))->map(fn($_, $i) => ['country' => old('travel_country.'.$i), 'duration' => old('travel_duration.'.$i)]) : ($enquiry->travelHistory ?? collect([[]])); @endphp
@foreach($travelRows as $idx => $row)
<div class="row travel-row {{ $idx > 0 ? 'mt-2' : '' }}">
<div class="col-md-4">
<input type="text" name="travel_country[]" class="form-control" placeholder="Country" value="{{ $row['country'] ?? $row->country ?? '' }}">
</div>
<div class="col-md-4">
<input type="text" name="travel_duration[]" class="form-control" placeholder="Duration" value="{{ $row['duration'] ?? $row->duration ?? '' }}">
</div>
<div class="col-md-4">
@if($idx === 0)
<button type="button" class="btn btn-success addTravel">+</button>
@else
<button type="button" class="btn btn-danger remove">-</button>
@endif
</div>
</div>
@endforeach

</div>

<h5 class="mt-4">5. Visa Refusal History</h5>

<div id="refusal_history">
@php $refusalRows = old('refusal_country') ? collect(old('refusal_country'))->map(fn($_, $i) => ['country' => old('refusal_country.'.$i), 'refusal_date' => old('refusal_date.'.$i), 'refusal_reason' => old('refusal_reason.'.$i)]) : ($enquiry->refusalHistory ?? collect([[]])); @endphp
@foreach($refusalRows as $idx => $row)
<div class="row refusal-row {{ $idx > 0 ? 'mt-2' : '' }}">
<div class="col-md-3">
<input type="text" name="refusal_country[]" class="form-control" placeholder="Country" value="{{ $row['country'] ?? $row->country ?? '' }}">
</div>
<div class="col-md-3">
<input type="text" name="refusal_date[]" class="form-control datepicker" value="{{ $row['refusal_date'] ?? $row->refusal_date ?? '' }}">
</div>
<div class="col-md-4">
<input type="text" name="refusal_reason[]" class="form-control" placeholder="Reason" value="{{ $row['refusal_reason'] ?? $row->refusal_reason ?? '' }}">
</div>
<div class="col-md-2">
@if($idx === 0)
<button type="button" class="btn btn-success addRefusal">+</button>
@else
<button type="button" class="btn btn-danger remove">-</button>
@endif
</div>
</div>
@endforeach

</div>

<h5 class="mt-4">6. Educational Qualifications</h5>

<div class="row">

<div class="col-md-3">
<select name="qualification" class="form-control">
<option {{ old('qualification', $enquiry->qualification ?? '') == '10th' ? 'selected' : '' }}>10th</option>
<option {{ old('qualification', $enquiry->qualification ?? '') == '12th' ? 'selected' : '' }}>12th</option>
<option {{ old('qualification', $enquiry->qualification ?? '') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
<option {{ old('qualification', $enquiry->qualification ?? '') == 'Bachelor’s Degree' ? 'selected' : '' }}>Bachelor’s Degree</option>
<option {{ old('qualification', $enquiry->qualification ?? '') == 'Master’s Degree' ? 'selected' : '' }}>Master’s Degree</option>
<option {{ old('qualification', $enquiry->qualification ?? '') == 'PhD' ? 'selected' : '' }}>PhD</option>
</select>
</div>

<div class="col-md-4">
<input type="text" name="institution" class="form-control" placeholder="Institution Name" value="{{ old('institution', $enquiry->institution ?? '') }}">
</div>

<div class="col-md-2">
<input type="text" name="passing_year" class="form-control" placeholder="Year" value="{{ old('passing_year', $enquiry->passing_year ?? '') }}">
</div>

<div class="col-md-3">
<input type="text" name="grade" class="form-control" placeholder="Result / Grade" value="{{ old('grade', $enquiry->grade ?? '') }}">
</div>

</div>

<h5 class="mt-4">7. English Language Competency</h5>

<div class="row">

<div class="col-md-4">
<select name="english_test" class="form-control">
<option {{ old('english_test', $enquiry->english_test ?? '') == 'IELTS' ? 'selected' : '' }}>IELTS</option>
<option {{ old('english_test', $enquiry->english_test ?? '') == 'TOEFL' ? 'selected' : '' }}>TOEFL</option>
<option {{ old('english_test', $enquiry->english_test ?? '') == 'TOEIC' ? 'selected' : '' }}>TOEIC</option>
<option {{ old('english_test', $enquiry->english_test ?? '') == 'PTE' ? 'selected' : '' }}>PTE</option>
<option {{ old('english_test', $enquiry->english_test ?? '') == 'DuoLingo' ? 'selected' : '' }}>DuoLingo</option>
</select>
</div>

<div class="col-md-4">
<input type="text" name="overall_score" class="form-control" placeholder="Overall Score" value="{{ old('overall_score', $enquiry->overall_score ?? '') }}">
</div>

<div class="col-md-4">
<input type="text" name="test_date" class="form-control datepicker" value="{{ old('test_date', $enquiry->test_date ?? '') }}">
</div>

</div>

<h5 class="mt-4">8. Work Experience</h5>

<div id="work_experience">
@php $workRows = old('job_title') ? collect(old('job_title'))->map(fn($_, $i) => ['job_title' => old('job_title.'.$i), 'employer' => old('employer.'.$i), 'work_country' => old('work_country.'.$i), 'joining_date' => old('joining_date.'.$i)]) : ($enquiry->workExperience ?? collect([[]])); @endphp
@foreach($workRows as $idx => $row)
<div class="row work-row {{ $idx > 0 ? 'mt-2' : '' }}">
<div class="col-md-3">
<input type="text" name="job_title[]" class="form-control" placeholder="Job Title" value="{{ $row['job_title'] ?? $row->job_title ?? '' }}">
</div>
<div class="col-md-3">
<input type="text" name="employer[]" class="form-control" placeholder="Employer Name" value="{{ $row['employer'] ?? $row->employer ?? '' }}">
</div>
<div class="col-md-2">
<input type="text" name="work_country[]" class="form-control" placeholder="Country" value="{{ $row['work_country'] ?? $row->work_country ?? '' }}">
</div>
<div class="col-md-2">
<input type="text" name="joining_date[]" class="form-control datepicker" value="{{ $row['joining_date'] ?? $row->joining_date ?? '' }}">
</div>
<div class="col-md-2">
@if($idx === 0)
<button type="button" class="btn btn-success addWork">+</button>
@else
<button type="button" class="btn btn-danger remove">-</button>
@endif
</div>
</div>
@endforeach

</div>

<div id="spouse_section" style="display:none">

<h5 class="mt-4">9. Spouse Personal Details</h5>

<div class="row">

<div class="col-md-6 mb-3">
<input type="text" name="spouse_name" class="form-control" placeholder="Spouse Name" value="{{ old('spouse_name', $enquiry->spouse_name ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<input type="email" name="spouse_email" class="form-control" placeholder="Spouse Email" value="{{ old('spouse_email', $enquiry->spouse_email ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<input type="text" name="spouse_dob" class="form-control datepicker" value="{{ old('spouse_dob', $enquiry->spouse_dob ?? '') }}">
</div>

<div class="col-md-6 mb-3">
<input type="text" name="spouse_contact" class="form-control" placeholder="Contact No" value="{{ old('spouse_contact', $enquiry->spouse_contact ?? '') }}">
</div>

</div>

</div>

<h5 class="mt-4">Do you have Children?</h5>

<select id="children_option" class="form-control mb-3">
<option value="no">No</option>
<option value="yes">Yes</option>
</select>

<div id="children_section" style="display:none">

<div id="children_rows">
@php $childrenRows = old('child_name') ? collect(old('child_name'))->map(fn($_, $i) => ['child_name' => old('child_name.'.$i), 'child_age' => old('child_age.'.$i), 'child_gender' => old('child_gender.'.$i), 'child_dob' => old('child_dob.'.$i)]) : ($enquiry->children ?? collect([[]])); @endphp
@foreach($childrenRows as $idx => $row)
<div class="row child-row {{ $idx > 0 ? 'mt-2' : '' }}">
<div class="col-md-3">
<input type="text" name="child_name[]" class="form-control" placeholder="Name" value="{{ $row['child_name'] ?? $row->child_name ?? '' }}">
</div>
<div class="col-md-2">
<input type="number" name="child_age[]" class="form-control" placeholder="Age" value="{{ $row['child_age'] ?? $row->child_age ?? '' }}">
</div>
<div class="col-md-2">
<select name="child_gender[]" class="form-control">
<option {{ ($row['child_gender'] ?? $row->child_gender ?? '') == 'M' ? 'selected' : '' }}>M</option>
<option {{ ($row['child_gender'] ?? $row->child_gender ?? '') == 'F' ? 'selected' : '' }}>F</option>
<option {{ ($row['child_gender'] ?? $row->child_gender ?? '') == 'PNTS' ? 'selected' : '' }}>PNTS</option>
</select>
</div>
<div class="col-md-3">
<input type="text" name="child_dob[]" class="form-control datepicker" value="{{ $row['child_dob'] ?? $row->child_dob ?? '' }}">
</div>
<div class="col-md-2">
@if($idx === 0)
<button type="button" class="btn btn-success addChild">+</button>
@else
<button type="button" class="btn btn-danger remove">-</button>
@endif
</div>
</div>
@endforeach

</div>

</div>

<!-- STUDENT VISA FUNDING -->

<div id="student_funding_section" style="display:none">

<h5 class="mt-4">What is the prime source of funding for your abroad study?</h5>

<div class="form-check">
<input class="form-check-input" type="checkbox" name="funding[]" value="Own Savings" {{ in_array('Own Savings', old('funding', isset($enquiry) ? $enquiry->fundingSources->pluck('funding_source')->toArray() : [])) ? 'checked' : '' }}> Own Savings
</div>

<div class="form-check">
<input class="form-check-input" type="checkbox" name="funding[]" value="Bank Finance" {{ in_array('Bank Finance', old('funding', isset($enquiry) ? $enquiry->fundingSources->pluck('funding_source')->toArray() : [])) ? 'checked' : '' }}> Bank Finance
</div>

<div class="form-check">
<input class="form-check-input" type="checkbox" name="funding[]" value="Family" {{ in_array('Family', old('funding', isset($enquiry) ? $enquiry->fundingSources->pluck('funding_source')->toArray() : [])) ? 'checked' : '' }}> Sponsored by Family
</div>

<div class="form-check">
<input class="form-check-input" type="checkbox" name="funding[]" value="Employer" {{ in_array('Employer', old('funding', isset($enquiry) ? $enquiry->fundingSources->pluck('funding_source')->toArray() : [])) ? 'checked' : '' }}> Sponsored by Employer
</div>

<div class="form-check">
<input class="form-check-input" type="checkbox" name="funding[]" value="Government" {{ in_array('Government', old('funding', isset($enquiry) ? $enquiry->fundingSources->pluck('funding_source')->toArray() : [])) ? 'checked' : '' }}> Sponsored by Institution/Government
</div>

</div>

<!-- DATE PLACE SIGN -->

<div class="row mt-4">

<div class="col-md-3">
<label>Date</label>
<div class="input-group">
<input type="text" name="form_date" class="form-control datepicker" value="{{ old('form_date', $enquiry->form_date ?? now()->format('d-m-Y')) }}">
</div>
</div>
<div class="col-md-3">
<label>Place</label>
<input type="text" name="place" class="form-control" value="{{ old('place', $enquiry->place ?? ($defaultPlace ?? '')) }}">
</div>

<div class="col-md-3">
<label>Print Name</label>
<input type="text" name="print_name" id="print_name" class="form-control" value="{{ old('print_name', $enquiry->print_name ?? '') }}">
</div>
<div class="col-md-6">
<label>Signature</label>

<div style="border:1px solid #ccc;border-radius:6px;padding:10px">

<canvas id="signature-pad" style="width:100%;height:150px;border:1px solid #ddd;"></canvas>

<input type="hidden" name="signature" id="signature">

<div class="mt-2">
<button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Clear</button>
</div>

</div>

</div>


</div>

<!-- CAPTCHA -->

<div class="mt-4">

@if(!$isEdit)
{!! NoCaptcha::display() !!}
@endif

</div>

<div class="mt-4">
@if(!$isEdit)
<label>
<input type="checkbox" required> I agree to <a href="/privacy_policy" target="_blank">Privacy Policy</a>
</label>

<br>

<label>
<input type="checkbox" required> I agree to <a href="/terms_of_use" target="_blank">Terms of Service</a>
</label>
@endif

</div>

<button class="btn btn-primary mt-4 w-100">{{ $isEdit ? 'Update Enquiry' : 'Submit Enquiry' }}</button>

</form>

</div>

<div class="col-lg-2"></div>

</div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
@if(!$isEdit)
{!! NoCaptcha::renderJs() !!}
@endif

<script>

document.addEventListener("DOMContentLoaded", function () {
    flatpickr(".datepicker", {
        dateFormat: "d-m-Y",
        allowInput: true
    });
});
$(document).ready(function(){

var canvas = document.getElementById('signature-pad');

if(canvas){

var signaturePad = new SignaturePad(canvas);

function resizeCanvas() {
var ratio =  Math.max(window.devicePixelRatio || 1, 1);
canvas.width = canvas.offsetWidth * ratio;
canvas.height = canvas.offsetHeight * ratio;
canvas.getContext("2d").scale(ratio, ratio);
signaturePad.clear();
}

window.addEventListener("resize", resizeCanvas);
resizeCanvas();

$('#clear-signature').click(function(){
signaturePad.clear();
$('#signature').val('');
});

$('form').on('submit', function(){

if(!signaturePad.isEmpty()){
var dataURL = signaturePad.toDataURL();
$('#signature').val(dataURL);
}

});

}

$('#marital_status').change(function(){
if($(this).val()=='Married'){ $('#spouse_section').show(); }
else{ $('#spouse_section').hide(); }
});
if($('#marital_status').val()=='Married'){ $('#spouse_section').show(); }

$('#children_option').change(function(){
if($(this).val()=='yes'){ $('#children_section').show(); }
else{ $('#children_section').hide(); }
});
@if(isset($enquiry) && $enquiry->children && $enquiry->children->count() > 0)
$('#children_option').val('yes');
$('#children_section').show();
@endif

$('#visa_category').change(function(){

if($(this).val()=='Study'){
$('#student_funding_section').show();
}
else{
$('#student_funding_section').hide();
}

});
if($('#visa_category').val()=='Study'){ $('#student_funding_section').show(); }

let printNameTouched = $('#print_name').val().trim().length > 0;
$('#print_name').on('input', function(){ printNameTouched = $(this).val().trim().length > 0; });
if(!$('#print_name').val()){
    $('#print_name').val($('#full_name').val());
}
$('#full_name').on('input', function(){
    if(!printNameTouched){
        $('#print_name').val($(this).val());
    }
});

function addRow(container,html){ $(container).append(html); }

$(document).on('click','.addResidency',function(){
addRow('#residency_history',
'<div class="row mt-2">'+
'<div class="col-md-3"><input type="text" name="res_country[]" class="form-control"></div>'+
'<div class="col-md-3"><input type="text" name="res_duration[]" class="form-control"></div>'+
'<div class="col-md-3"><input type="text" name="res_visa[]" class="form-control"></div>'+
'<div class="col-md-3"><button type="button" class="btn btn-danger remove">-</button></div>'+
'</div>');
});

$(document).on('click','.addTravel',function(){
addRow('#travel_history',
'<div class="row mt-2">'+
'<div class="col-md-4"><input type="text" name="travel_country[]" class="form-control"></div>'+
'<div class="col-md-4"><input type="text" name="travel_duration[]" class="form-control"></div>'+
'<div class="col-md-4"><button type="button" class="btn btn-danger remove">-</button></div>'+
'</div>');
});

$(document).on('click','.addRefusal',function(){
addRow('#refusal_history',
'<div class="row mt-2">'+
'<div class="col-md-3"><input type="text" name="refusal_country[]" class="form-control"></div>'+
'<div class="col-md-3"><input type="text" name="refusal_date[]" class="form-control datepicker"></div>'+
'<div class="col-md-4"><input type="text" name="refusal_reason[]" class="form-control"></div>'+
'<div class="col-md-2"><button type="button" class="btn btn-danger remove">-</button></div>'+
'</div>');
});

$(document).on('click','.addWork',function(){
addRow('#work_experience',
'<div class="row mt-2">'+
'<div class="col-md-3"><input type="text" name="job_title[]" class="form-control"></div>'+
'<div class="col-md-3"><input type="text" name="employer[]" class="form-control"></div>'+
'<div class="col-md-2"><input type="text" name="work_country[]" class="form-control"></div>'+
'<div class="col-md-2"><input type="text" name="joining_date[]" class="form-control datepicker"></div>'+
'<div class="col-md-2"><button type="button" class="btn btn-danger remove">-</button></div>'+
'</div>');
});

$(document).on('click','.addChild',function(){
addRow('#children_rows',
'<div class="row mt-2">'+
'<div class="col-md-3"><input type="text" name="child_name[]" class="form-control"></div>'+
'<div class="col-md-2"><input type="number" name="child_age[]" class="form-control"></div>'+
'<div class="col-md-2"><select name="child_gender[]" class="form-control"><option>M</option><option>F</option><option>PNTS</option></select></div>'+
'<div class="col-md-3"><input type="text" name="child_dob[]" class="form-control datepicker"></div>'+
'<div class="col-md-2"><button type="button" class="btn btn-danger remove">-</button></div>'+
'</div>');
});

$(document).on('click','.remove',function(){ $(this).closest('.row').remove(); });

});

</script>

@endsection
