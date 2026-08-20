@extends('web.layout.main')

@section('main-section')

@php
use App\Models\UserRoles;
$application_roles = UserRoles::where('user_id', '=', $user->id)->where('module', '=', 'Applications')->first();
$canDownload = $user->user_type === 'admin' || $user->user_type === 'Subscriber' || ($application_roles && ($application_roles->read_only == 1 || $application_roles->read_write_only == 1));
$canEdit = $user->user_type === 'admin' || $user->user_type === 'Subscriber' || ($application_roles && ($application_roles->update_only == 1 || $application_roles->read_write_only == 1));
$canAdd = $user->user_type === 'admin' || $user->user_type === 'Subscriber' || ($application_roles && ($application_roles->write_only == 1 || $application_roles->read_write_only == 1));
$canDelete = $user->user_type === 'admin' || $user->user_type === 'Subscriber' || ($application_roles && $application_roles->delete_only == 1);
$canViewChecklist = $user->user_type === 'admin' || $user->user_type === 'Subscriber' || ($application_roles && (
    $application_roles->read_only == 1
    || $application_roles->read_write_only == 1
    || $application_roles->write_only == 1
    || $application_roles->update_only == 1
));
@endphp

<div class="col-lg-10 column-client">
    @if(isset($application))
    <div class="client-dashboard">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <a href="{{ route('applications') }}" class="text-decoration-none small">
                    <i class="fa-solid fa-arrow-left me-1"></i>Back to Applications
                </a>
                <h3 class="text-primary mb-0 mt-1"><b>{{ $application->application_name }}</b></h3>
            </div>
            <span class="badge rounded-pill px-3 py-2 app-status-badge">
                {{ $application->application_status }}
            </span>
        </div>

        <div class="app-detail-card mb-3">
            <div class="app-detail-card-header">
                <i class="fa-solid fa-circle-info me-2"></i>Application Details
            </div>
            <div class="app-detail-card-body">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="app-detail-row">
                            <span class="app-detail-label">Client ID</span>
                            <span class="app-detail-value">{{ $application->client_id }}</span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">Application ID</span>
                            <span class="app-detail-value">{{ $application->application_id }}</span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">Application</span>
                            <span class="app-detail-value">{{ $application->application_name }}</span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">Application Country</span>
                            <span class="app-detail-value">{{ $application->application_country }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="app-detail-row">
                            <span class="app-detail-label">Start Date</span>
                            <span class="app-detail-value">{{ date("d-m-Y", strtotime($application->start_date)) }}</span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">End Date</span>
                            <span class="app-detail-value">
                                @if($application->end_date != null)
                                    {{ date("d-m-Y", strtotime($application->end_date)) }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">Status</span>
                            <span class="app-detail-value">{{ $application->application_status }}</span>
                        </div>
                        <div class="app-detail-row app-detail-row-full">
                            <span class="app-detail-label">Additional Information</span>
                            <span class="app-detail-value">{{ $application->application_detail ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $ccService = app(\App\Services\CountryCategorySettingsService::class);
            $showStudyDetails = $ccService->isStudyVisaCategory($application->application_name);
            $showWorkDetails = $ccService->isWorkVisaCategory($application->application_name);
            $displayValue = fn ($value) => ($value && $value !== 'NA') ? $value : '—';
        @endphp

        @if ($showStudyDetails)
        <div class="app-detail-card mb-3">
            <div class="app-detail-card-header">
                <i class="fa-solid fa-graduation-cap me-2"></i>Study Visa Details
            </div>
            <div class="app-detail-card-body">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="app-detail-row">
                            <span class="app-detail-label">Course Name</span>
                            <span class="app-detail-value">{{ $displayValue($application->course_name ?? null) }}</span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">Course Duration</span>
                            <span class="app-detail-value">{{ $displayValue($application->course_duration ?? null) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="app-detail-row">
                            <span class="app-detail-label">Institution</span>
                            <span class="app-detail-value">{{ $displayValue($application->institution ?? null) }}</span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">Admission Number (CAS / I-20)</span>
                            <span class="app-detail-value">{{ $displayValue($application->admission_number ?? null) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if ($showWorkDetails)
        <div class="app-detail-card mb-3">
            <div class="app-detail-card-header">
                <i class="fa-solid fa-briefcase me-2"></i>Work Visa Details
            </div>
            <div class="app-detail-card-body">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="app-detail-row">
                            <span class="app-detail-label">Employer Name</span>
                            <span class="app-detail-value">{{ $displayValue($application->employer_name ?? null) }}</span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">Job Role</span>
                            <span class="app-detail-value">{{ $displayValue($application->employment_role ?? null) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="app-detail-row">
                            <span class="app-detail-label">Permit Duration</span>
                            <span class="app-detail-value">{{ $displayValue($application->permit_duration ?? null) }}</span>
                        </div>
                        <div class="app-detail-row">
                            <span class="app-detail-label">Sponsorship (Permit) No. (LMIA / COS)</span>
                            <span class="app-detail-value">{{ $displayValue($application->sponsor_number ?? null) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @include('web.partials.application_documents_panel', [
            'application' => $application,
            'documents' => $documents ?? collect(),
            'documentsByType' => $documentsByType ?? collect(),
            'documentsByFolder' => $documentsByFolder ?? collect(),
            'documentFolders' => $documentFolders ?? [],
            'canDownload' => $canDownload,
            'showAddActions' => $canAdd,
            'showEditActions' => $canEdit,
            'showDeleteActions' => $canDelete,
            'showDocumentListActions' => $canViewChecklist,
            'documentListRoute' => route('generate_application_document_list', $application->id),
            'documentListDownloadRoute' => route('download_application_document_list', $application->id),
            'documentListSendRoute' => route('send_application_document_list', $application->id),
            'documentListClientEmail' => optional($application->client)->email ?? '',
            'documentListConfigured' => $canGenerateDocumentList ?? false,
            'documentChecklistItems' => $documentChecklistItems ?? [],
            'uploadAction' => route('upload_client_document'),
            'deleteRouteName' => 'delete_client_document',
            'clientId' => $application->client_id,
            'applicationDbId' => $application->id,
            'returnApplicationId' => $application->id,
        ])
    </div>
    @endif
</div>
</div>

</div>

<script>
    function deleteapplication(id){
        var conf = confirm('Are you sure you want to delete this application?');
        if(conf == true){
            window.location.href = "delete_application/"+id+"";
        }
    }
</script>

@if(session()->has('deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Application deleted successfully.'
    })
  </script>
@endif
@if(session()->has('application_added'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Application added successfully.'
    })
  </script>
@endif
@if(session()->has('application_updated'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Application updated successfully.'
    })
  </script>
@endif
@if(session()->has('document_deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Document deleted successfully.'
    })
  </script>
@endif
@if(session()->has('document_added'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Document added successfully.'
    })
  </script>
@endif
@if(session()->has('document_updated'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Document updated successfully.'
    })
  </script>
@endif
@if(session()->has('document_list_error'))
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Document List unavailable',
      text: @json(session('document_list_error'))
    })
  </script>
@endif
@if(session()->has('document_list_sent'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Sent',
      text: @json(session('document_list_sent'))
    })
  </script>
@endif
@endsection()
