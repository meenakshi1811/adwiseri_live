@extends('web.layout.main')

@section('main-section')

<div class="col-lg-10 column-client">
<div class="client-dashboard">

<div class="col-12 d-flex justify-content-between align-items-center mb-3">
<h3 class="text-primary text-center flex-grow-1 m-0">Enquiries (Leads)</h3>
<div class="d-flex gap-2">
@php
use App\Models\User;
$subscriber = $user->user_type === 'Subscriber' ? $user : User::find($user->added_by);
$encryptedId = encrypt($subscriber->id);
$qrUrl = url('/create-new-lead/'.$encryptedId);
$subscriberLogoUrl = null;
if (!empty($subscriber->organization_logo)) {
    $subscriberLogoPath = public_path('web_assets/users/user' . $subscriber->id . '/' . $subscriber->organization_logo);
    if (file_exists($subscriberLogoPath)) {
        $subscriberLogoUrl = asset('web_assets/users/user' . $subscriber->id . '/' . $subscriber->organization_logo);
    }
}
$subscriberDisplayName = $subscriber->organization ?? $subscriber->name;
@endphp
<a href="{{ route('enquiries.create') }}" class="btn btn-primary btn-sm">Add Lead</a>
<a href="{{ route('createLead', $encryptedId) }}" class="btn btn-outline-primary btn-sm">Full Enquiry Form</a>
<a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#qrModal">Get QR Code For Enquiry Form</a>
</div>
</div>


<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                @if(!empty($subscriberLogoUrl))
                    <img
                        src="{{ $subscriberLogoUrl }}"
                        alt="{{ $subscriberDisplayName }}"
                        class="enquiry-qr-logo mb-3"
                        style="max-height: 70px; max-width: 220px; object-fit: contain;"
                    >
                @else
                    <div class="fw-bold text-primary mb-3" style="font-size: 1.25rem;">{{ $subscriberDisplayName }}</div>
                @endif
                <p>Scan this QR code to fill the Enquiry Form.</p>
                <img
                    id="enquiryQrImage"
                    src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($qrUrl) }}"
                    alt="QR Code"
                />
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="shareEnquiryQr('{{ $qrUrl }}')">
                        <i class="fa-solid fa-share-nodes"></i> Share
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="printEnquiryQr()">
                        <i class="fa-solid fa-print"></i> Print A4
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.client_module_tabs', ['activeTab' => 'enquiries'])

@if(session()->has('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('success'))
    });
});
</script>
@endif

@if(session()->has('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Oops!',
        text: @json(session('error'))
    });
});
</script>
@endif

@if(count($enquiries) != 0)

<div class="table-wrapper">

<table class="fl-table table table-hover table-responsive p-0 m-0" id="clientTable">

<thead>

<tr>

<th class="text-center">EnquiryID</th>
<th class="text-center">Client Name</th>
<th class="text-center">COP</th>
<th class="text-center">PVC</th>
<th class="text-center">Contact No</th>
<th class="text-center">Email</th>
<th class="text-center">NOA</th>
<th class="text-center">Created Date</th>
<th class="text-center">Source</th>
<th class="text-center">Follow-up Status</th>
<th class="text-center">Worked By</th>
<th class="text-center">Last Worked</th>
<th class="text-center">Followups</th>
<th class="text-center">Convert to Client</th>
<th class="text-center">Client Status</th>
<th class="text-center">Action</th>

</tr>

</thead>

<tbody>

@foreach($enquiries as $enquiry)

<tr>

<td class="text-center">{{ $enquiry->id }}</td>

<td class="text-center enquiry-client-name-cell">
{{ $enquiry->full_name }}
</td>

<td class="text-center">
@php
$countryPreferences = collect([
    $enquiry->country_pref_1,
    $enquiry->country_pref_2,
    $enquiry->country_pref_3,
])->map(fn ($country) => trim((string) $country))
  ->filter()
  ->unique()
  ->values();
@endphp
{{ $countryPreferences->isNotEmpty() ? $countryPreferences->implode(', ') : '-' }}
</td>

<td class="text-center">
{{ $enquiry->visa_category }}
</td>

<td class="text-center">
@include('partials.phone_display', ['phone' => $enquiry->contact_no, 'emptyText' => '-'])
</td>

<td class="text-center">
{{ $enquiry->email }}
</td>

<td class="text-center">

{{ 1 + (!empty($enquiry->spouse_name) ? 1 : 0) + (int) ($enquiry->children_applying_count ?? 0) }}

</td>

<td class="text-center">
{{ \Carbon\Carbon::parse($enquiry->created_at)->format('d-m-Y H:i:s') }}
</td>

@php
$leadStatusClass = match ($enquiry->lead_status ?? 'Open') {
    'Open' => 'bg-primary',
    'Contacted' => 'bg-info text-dark',
    'Followup' => 'bg-warning text-dark',
    'Converted' => 'bg-success',
    'Closed' => 'bg-secondary',
    'Reopen' => 'bg-danger',
    default => 'bg-light text-dark',
};
@endphp

<td class="text-center">
<select class="form-select form-select-sm lead-follow-up-field" data-field="lead_source" data-enquiry-id="{{ $enquiry->id }}">
@foreach(($leadService->sources() ?? []) as $sourceOption)
<option value="{{ $sourceOption }}" {{ ($enquiry->lead_source ?? 'Walk-in') === $sourceOption ? 'selected' : '' }}>{{ $sourceOption }}</option>
@endforeach
</select>
</td>

<td class="text-center">
<select class="form-select form-select-sm lead-follow-up-field" data-field="lead_status" data-enquiry-id="{{ $enquiry->id }}">
@foreach(($leadService->statuses() ?? []) as $statusOption)
<option value="{{ $statusOption }}" {{ ($enquiry->lead_status ?? 'Open') === $statusOption ? 'selected' : '' }}>{{ $statusOption }}</option>
@endforeach
</select>
<span class="badge {{ $leadStatusClass }} mt-1 lead-status-badge">{{ $enquiry->lead_status ?? 'Open' }}</span>
</td>

<td class="text-center">
<select class="form-select form-select-sm lead-follow-up-field" data-field="lead_worked_by_user_id" data-enquiry-id="{{ $enquiry->id }}">
<option value="">Unassigned</option>
<option value="{{ $subscriber->id }}" {{ (int) ($enquiry->lead_worked_by_user_id ?? 0) === (int) $subscriber->id ? 'selected' : '' }}>{{ $subscriber->name }} (Subscriber)</option>
@foreach(($staffMembers ?? collect()) as $staffMember)
<option value="{{ $staffMember->id }}" {{ (int) ($enquiry->lead_worked_by_user_id ?? 0) === (int) $staffMember->id ? 'selected' : '' }}>{{ $staffMember->name }}</option>
@endforeach
</select>
</td>

<td class="text-center lead-worked-at-cell">
{{ !empty($enquiry->lead_worked_at) ? \Carbon\Carbon::parse($enquiry->lead_worked_at)->format('d-m-Y H:i:s') : '-' }}
</td>

<td class="text-center lead-followups-cell">
@if((int) ($enquiry->follow_up_logs_count ?? 0) > 0)
<button
    type="button"
    class="btn btn-sm btn-outline-primary lead-followups-btn"
    data-enquiry-id="{{ $enquiry->id }}"
    data-bs-toggle="modal"
    data-bs-target="#leadFollowUpHistoryModal"
    onclick="if (typeof initLeadFollowUpHistoryModal === 'function') { initLeadFollowUpHistoryModal({{ $enquiry->id }}); }"
>
Followups
</button>
@else
-
@endif
</td>

<td class="text-center convert-to-client-cell">

@if($enquiry->status == 1)

<span class="badge bg-success">Converted</span>

@else

<button class="btn btn-sm btn-success convertClient" data-id="{{ $enquiry->id }}">
Yes
</button>

@endif

</td>

<td class="text-center enquiry-status-cell">

@if($enquiry->status == 1)
<span class="badge bg-success">Client</span>
@else
<span class="badge bg-warning text-dark">Enquiry</span>
@endif

</td>

<td class="pb-5 text-center action-icon" style="display:flex;justify-content:center;align-items:center">

<a href="{{ url('visa-enquiries/view/'.$enquiry->id) }}" title="View">
<i class="fa-solid fa-eye text-info btn p-1 " style="font-size:14px"></i>
</a>

<a href="{{ route('visa_enquiries.edit', $enquiry->id) }}" title="Edit">
<i class="fa-solid fa-pen-to-square text-primary btn p-1" style="font-size:14px"></i>
</a>

<a href="javascript:void(0)" onclick="deleteEnquiry({{ $enquiry->id }})" title="Delete">
<i class="fa-solid fa-trash text-danger btn p-1" style="font-size:14px"></i>
</a>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@else

<p class="text-secondary">No enquiries found.</p>

@endif

<p class="mt-3 mb-0 text-muted small">
<strong>Follow-up Status Guide:</strong>
<span class="badge bg-primary">Open</span> new lead,
<span class="badge bg-info text-dark">Contacted</span> initial contact made,
<span class="badge bg-warning text-dark">Followup</span> in progress,
<span class="badge bg-success">Converted</span> moved to client,
<span class="badge bg-secondary">Closed</span> not proceeding,
<span class="badge bg-danger">Reopen</span> reopened for action.
<strong>Client Status</strong> shows whether the enquiry record has been converted into a client profile.
</p>

</div>
</div>

@include('partials.lead_follow_up_history_modal')

@endsection

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
const csrfToken = "{{ csrf_token() }}";

function setButtonState(button, isDisabled, label) {
    button.disabled = isDisabled;
    button.textContent = label;
}

function setConvertedUI(button, row) {
    const convertCell = row.querySelector('.convert-to-client-cell');
    const statusCell = row.querySelector('.enquiry-status-cell');
    const clientNameCell = row.querySelector('.enquiry-client-name-cell');

    if (clientNameCell) {
        clientNameCell.classList.remove('convert-to-client-cell');
    }

    if (convertCell) {
        convertCell.innerHTML = '<span class="badge bg-success">Converted</span>';
    }

    if (statusCell) {
        statusCell.innerHTML = '<span class="badge bg-success">Client</span>';
    }

    const leadStatusSelect = row.querySelector('[data-field="lead_status"]');
    const leadStatusBadge = row.querySelector('.lead-status-badge');
    if (leadStatusSelect) {
        leadStatusSelect.value = 'Converted';
    }
    if (leadStatusBadge) {
        leadStatusBadge.className = 'badge bg-success mt-1 lead-status-badge';
        leadStatusBadge.textContent = 'Converted';
    }

    button.disabled = true;
    button.classList.add('disabled');
    button.setAttribute('aria-disabled', 'true');
}

document.addEventListener('click', async function (event) {
    const button = event.target.closest('.convertClient');

    if (!button) {
        return;
    }

    if (button.disabled) {
        return;
    }

    const enquiryId = button.dataset.id;
    const row = button.closest('tr');

    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to convert this enquiry into a client?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Convert',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) {
        return;
    }

    setButtonState(button, true, 'Converting...');

    try {
        const response = await fetch("{{ url('convert-enquiry-client') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                enquiry_id: enquiryId
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            setButtonState(button, false, 'Yes');
            await Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: (data && data.message) ? data.message : 'Unable to convert enquiry.'
            });
            return;
        }

        setConvertedUI(button, row);

        await Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data.message || 'Enquiry converted to client successfully!'
        });
    } catch (error) {
        setButtonState(button, false, 'Yes');
        await Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: 'Something went wrong. Please try again.'
        });
    }
});


const leadStatusClasses = {
    Open: 'bg-primary',
    Contacted: 'bg-info text-dark',
    Followup: 'bg-warning text-dark',
    Converted: 'bg-success',
    Closed: 'bg-secondary',
    Reopen: 'bg-danger'
};

document.addEventListener('change', async function (event) {
    const field = event.target.closest('.lead-follow-up-field');
    if (!field) {
        return;
    }

    const enquiryId = field.dataset.enquiryId;
    const row = field.closest('tr');
    const payload = {};

    row.querySelectorAll('.lead-follow-up-field').forEach(function (input) {
        payload[input.dataset.field] = input.value;
    });

    field.disabled = true;

    try {
        const response = await fetch(`/enquiries/${enquiryId}/lead-follow-up`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error((data && data.message) ? data.message : 'Unable to update lead follow-up.');
        }

        const workedAtCell = row.querySelector('.lead-worked-at-cell');
        if (workedAtCell && data.data && data.data.lead_worked_at) {
            workedAtCell.textContent = data.data.lead_worked_at;
        }

        const leadStatusBadge = row.querySelector('.lead-status-badge');
        if (leadStatusBadge && data.data && data.data.lead_status) {
            const statusClass = leadStatusClasses[data.data.lead_status] || 'bg-light text-dark';
            leadStatusBadge.className = `badge ${statusClass} mt-1 lead-status-badge`;
            leadStatusBadge.textContent = data.data.lead_status;
        }

        const followupsCell = row.querySelector('.lead-followups-cell');
        if (followupsCell && data.data && Number(data.data.follow_up_logs_count) > 0) {
            const enquiryId = field.dataset.enquiryId;
            followupsCell.innerHTML = `<button type="button" class="btn btn-sm btn-outline-primary lead-followups-btn" data-enquiry-id="${enquiryId}" data-bs-toggle="modal" data-bs-target="#leadFollowUpHistoryModal" onclick="if (typeof initLeadFollowUpHistoryModal === 'function') { initLeadFollowUpHistoryModal(${enquiryId}); }">Followups</button>`;
        }
    } catch (error) {
        await Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: error.message || 'Something went wrong. Please try again.'
        });
        location.reload();
    } finally {
        field.disabled = false;
    }
});


function shareEnquiryQr(qrUrl) {
    if (navigator.share) {
        navigator.share({
            title: 'Enquiry Form QR',
            text: 'Scan this QR code to fill the Enquiry Form',
            url: qrUrl
        }).catch(() => {});
        return;
    }

    window.location.href = 'mailto:?subject=' + encodeURIComponent('Enquiry Form QR Link') +
        '&body=' + encodeURIComponent('Please use this link to access the enquiry form: ' + qrUrl);
}

function printEnquiryQr() {
    const printWindow = window.open('', '_blank', 'height=1000,width=900');

    if (!printWindow) {
        Swal.fire({
            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Popup Blocked',
            text: 'Please allow popups to print the QR code sheet.'
        });
        return;
    }

    const printHtml = `
        <html>
            <head>
                <title>Enquiry QR Code</title>
                <style>
                    @page { size: A4; margin: 0; }
                    body { margin: 0; font-family: Arial, sans-serif; }
                </style>
            </head>
            <body>
                <div style="width:210mm; min-height:297mm; padding:20mm; text-align:center; box-sizing:border-box;">
                    @if(!empty($subscriberLogoUrl))
                    <img id="printLogoImage" src="{{ $subscriberLogoUrl }}" alt="{{ $subscriberDisplayName }}" style="max-height:90px; max-width:280px; object-fit:contain; margin-bottom:12px;">
                    @else
                    <h2 style="margin-bottom:8px;">{{ $subscriberDisplayName }}</h2>
                    @endif
                    <p style="margin-bottom:20px;">Enquiry Form Access</p>
                    <img id="printQrImage" src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data={{ urlencode($qrUrl) }}" alt="Enquiry QR Code" style="max-width:350px;">
                    <p style="margin-top:25px; font-size:18px;">Scan this QR code to fill the Enquiry Form</p>
                </div>
            </body>
        </html>
    `;

    printWindow.document.open();
    printWindow.document.write(printHtml);
    printWindow.document.close();

    const waitForImage = () => {
        const images = printWindow.document.querySelectorAll('img');
        if (!images.length) {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
            return;
        }

        let loadedCount = 0;
        const finishPrint = () => {
            loadedCount++;
            if (loadedCount >= images.length) {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }
        };

        images.forEach((img) => {
            if (img.complete) {
                finishPrint();
            } else {
                img.onload = finishPrint;
                img.onerror = finishPrint;
            }
        });
    };

    setTimeout(waitForImage, 300);
}

function deleteEnquiry(id){

    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to delete this enquiry?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes delete it"
    }).then((result) => {

        if(result.isConfirmed){

        fetch('/visa-enquiries/delete/' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(async (response) => {
            const data = await response.json();

            if (!response.ok) {
                throw new Error((data && data.message) ? data.message : 'Unable to delete enquiry.');
            }

            return data;
        })
        .then((res) => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: res.message
            })
            .then(()=>{
                location.reload();
            });
        })
        .catch((error) => {
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: error.message || 'Something went wrong. Please try again.'
            });
        });

    }

});

}
</script>
