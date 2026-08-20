@extends('admin.layout.main')

@section('main-section')

        <div class="col-lg-10 column-client">
            @if(isset($application))
            <div class="client-dashboard">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <a href="{{ route('manage_applications') }}" class="text-decoration-none small">
                            <i class="fa-solid fa-arrow-left me-1"></i>Back to Applications
                        </a>
                        <h3 class="text-primary mb-0 mt-1">{{ $application->application_name }}</h3>
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

                @include('web.partials.application_documents_panel', [
                    'application' => $application,
                    'documents' => $documents ?? collect(),
                    'documentsByType' => $documentsByType ?? collect(),
                    'documentsByFolder' => $documentsByFolder ?? collect(),
                    'documentFolders' => $documentFolders ?? [],
                    'canDownload' => true,
                    'showAddActions' => true,
                    'showEditActions' => true,
                    'showDeleteActions' => true,
                    'showDocumentListActions' => true,
                    'documentListRoute' => route('admin.generate_application_document_list', $application->id),
                    'documentListDownloadRoute' => route('admin.download_application_document_list', $application->id),
                    'documentListConfigured' => $canGenerateDocumentList ?? false,
                    'uploadAction' => route('upload_document'),
                    'deleteRouteName' => 'delete_document',
                    'clientId' => $application->client_id,
                    'applicationDbId' => $application->id,
                    'returnApplicationId' => $application->id,
                ])
            </div>
            @endif
        </div>
    </div>

  </div>

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

@endsection()
