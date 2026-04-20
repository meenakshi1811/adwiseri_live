@extends('web.layout.main')

@section('main-section')

<div class="col-lg-10 column-client">
<div class="client-dashboard">

<div class="col-12 d-flex justify-content-between align-items-center mb-3">
<h3 class="text-primary text-center flex-grow-1 m-0">Enquiries</h3>
<div class="d-flex gap-2">
@php
$encryptedId = encrypt($user->id);
$qrUrl = url('/create-new-lead/'.$encryptedId);
@endphp
<a href="{{ route('createLead', $encryptedId) }}" class="btn btn-info btn-sm">Add Enquiry</a>
<a href="javascript:void(0)" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#qrModal">Get QR Code For Enquiry Form</a>
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

<div class="row m-0 pb-2">
<div class="col-4 border p-1 text-center top_modules" onclick="window.location.href = '{{ route('client') }}';">
Clients
</div>
<div class="col-4 border p-1 text-center top_modules" onclick="window.location.href = '{{ route('subscriber_dependents') }}';">
Dependants
</div>
<div class="col-4 border p-1 text-center bg-info text-white">
Enquries
</div>

</div>

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
<th class="text-center">Convert to Client</th>
<th class="text-center">Status</th>
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
{{ $enquiry->contact_no ?: '-' }}
</td>

<td class="text-center">
{{ $enquiry->email }}
</td>

<td class="text-center">

{{ 1 + (int) ($enquiry->children_count ?? 0) }}

</td>

<td class="text-center">
{{ \Carbon\Carbon::parse($enquiry->created_at)->format('d-m-Y') }}
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
<strong>Status Guide:</strong> <span class="badge bg-warning text-dark">Enquiry</span> means a fresh lead.
After successful conversion, it changes to <span class="badge bg-success">Client</span>.
</p>

</div>
</div>

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
        text: "Do you want to convert this enquiry into client?",
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
                title: 'Error',
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
            title: 'Error',
            text: 'Something went wrong!'
        });
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
            icon: 'error',
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
                    <h2 style="margin-bottom:8px;">{{ $user->organization ?? $user->name }}</h2>
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
        const img = printWindow.document.getElementById('printQrImage');
        if (!img) {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
            return;
        }

        if (img.complete) {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        } else {
            img.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
            img.onerror = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        }
    };

    setTimeout(waitForImage, 300);
}

function deleteEnquiry(id){

    Swal.fire({
        title: "Are you sure?",
        text: "You want to delete this enquiry!",
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
            Swal.fire("Deleted!", res.message, "success")
            .then(()=>{
                location.reload();
            });
        })
        .catch((error) => {
            Swal.fire('Error', error.message || 'Something went wrong!', 'error');
        });

    }

});

}
</script>
