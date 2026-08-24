@php
    use App\Support\ModuleAvailability;

    $activeTab = $activeTab ?? 'applications';
    $pageTitles = [
        'applications' => 'Applications',
        'documents' => 'Documents',
        'management' => 'Application Management',
        'tracking' => 'Application Tracking',
    ];
    $pageTitle = $pageTitle ?? ($pageTitles[$activeTab] ?? 'Applications');
    $hasClients = ModuleAvailability::hasClients($user);
    $hasApplications = ModuleAvailability::hasApplications($user);
    $canWrite = ($application_roles->write_only ?? 0) == 1 || ($application_roles->read_write_only ?? 0) == 1;
    $unassignedCount = (int) ($unassignedApplicationsCount ?? 0);
    $hasSiteUsers = ModuleAvailability::hasStaffUsers($user);
@endphp

<div class="col-12 client-btn d-flex justify-content-between align-items-center mt-3 mb-3">
    <h3 class="text-primary text-center flex-grow-1 text-center m-0">{{ $pageTitle }}</h3>
    <div class="d-flex gap-2 mb-0 module-header-actions">
        @if($hasClients)
            <a @if($canWrite) href="{{ route('add_application') }}" @else href="#" @endif class="m-0">Add Application</a>
        @else
            <a @if($canWrite) href="javascript:void(0)" onclick="showNoClientAlert(); return false;" @else href="#" @endif class="m-0">Add Application</a>
        @endif

        @if($activeTab === 'documents')
            @if(!$hasClients)
                <a href="javascript:void(0)" @if($canWrite) onclick="showNoClientAlert(); return false;" @endif class="m-0">Add Document</a>
            @elseif($hasApplications)
                <a href="javascript:void(0)" @if($canWrite) id="add_new" @endif class="m-0">Add Document</a>
            @else
                <a href="javascript:void(0)" @if($canWrite) id="add_new_zero" @endif class="m-0">Add Document</a>
            @endif
        @else
            @if(!$hasClients)
                <a href="javascript:void(0)" onclick="showNoClientAlert(); return false;" class="m-0">Add Document</a>
            @elseif($hasApplications)
                <a href="{{ route('client_documents') }}" class="m-0">Add Document</a>
            @else
                <a href="javascript:void(0)" @if($canWrite) id="add_new_zero" @endif class="m-0">Add Document</a>
            @endif
        @endif

        @if($activeTab === 'management')
            @if(!$hasApplications)
                <a href="javascript:void(0)" @if($canWrite) id="new_assign_zero" @endif class="m-0">New Assign</a>
            @elseif($unassignedCount > 0)
                @if($hasSiteUsers)
                    <a href="javascript:void(0)" @if($canWrite) id="new_assign" @endif class="m-0">New Assign</a>
                @else
                    <a href="javascript:void(0)" @if($canWrite) id="new_assign_usr" @endif class="m-0">New Assign</a>
                @endif
            @else
                <a href="javascript:void(0)" @if($canWrite) id="new_assign_zero" @endif class="m-0">New Assign</a>
            @endif
        @else
            @if($hasApplications)
                <a href="{{ route('user_applications') }}" class="m-0">New Assign</a>
            @else
                <a href="javascript:void(0)" @if($canWrite) id="new_assign_zero" @endif class="m-0">New Assign</a>
            @endif
        @endif

        <a href="{{ route('export_applications') }}" class="m-0">Export</a>

        @if(in_array($activeTab, ['documents', 'management'], true))
            <a href="javascript:void(0)" style="display: none;" id="back" class="m-0">Back</a>
        @endif
    </div>
</div>

<div class="row m-0 pb-2 module-tab-row">
    <div class="col-3 border p-1 text-center tab-anchor {{ $activeTab === 'applications' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'applications') onclick="window.location.href = '{{ route('applications') }}';" @endif>
        Applications
    </div>
    <div class="col-3 border p-1 text-center tab-anchor {{ $activeTab === 'documents' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'documents')
            @if($hasApplications)
                onclick="window.location.href = '{{ route('client_documents') }}';"
            @else
                id="documents_zero" style="cursor:pointer;opacity:0.45;"
            @endif
        @endif>
        Documents
    </div>
    <div class="col-3 border p-1 text-center tab-anchor {{ $activeTab === 'management' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'management' && $user->user_type == 'Subscriber')
            @if($hasApplications)
                onclick="window.location.href = '{{ route('user_applications') }}';"
            @else
                id="management_zero" style="cursor:pointer;opacity:0.45;"
            @endif
        @endif>
        Application Management
    </div>
    @if($hasApplications)
    <div class="col-3 border p-1 text-center tab-anchor {{ $activeTab === 'tracking' ? 'bg-info text-white' : 'top_modules' }}"
        @if($activeTab !== 'tracking') onclick="window.location.href = '{{ route('user_application_tracking') }}';" @endif>
        Application Tracking
    </div>
    @else
    <div class="col-3 border p-1 text-center tab-anchor top_modules" id="app_tracking_zero" style="cursor:pointer;opacity:0.45;">
        Application Tracking
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['#documents_zero', '#management_zero', '#app_tracking_zero'].forEach(function (selector) {
            var element = document.querySelector(selector);
            if (!element) {
                return;
            }

            element.addEventListener('click', function () {
                if (window.AdwiseriAlert && typeof window.AdwiseriAlert.oops === 'function') {
                    window.AdwiseriAlert.oops('No applications have been created yet.');
                    return;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'No applications have been created yet.'
                    });
                }
            });
        });
    });
</script>
