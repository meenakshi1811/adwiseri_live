@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-primary text-center flex-grow-1 m-0">Business (Referrals)</h3>
                <p class="m-0">
                    <a href="{{ route('add_associate_business') }}">Add New Business Entry</a>
                </p>
            </div>

            @include('web.associates._tabs')

            @if(count($businesses) != 0)
            @include('partials.table_filter_toolbar', [
                'filterItems' => $associateReferralFilters ?? [],
                'tableId' => 'businessTable',
                'toolbarTitle' => 'Associates with Referrals',
                'totalCount' => count($businesses),
            ])
            <div class="table-wrapper">
                <table class="fl-table table table-hover p-0 m-0" id="businessTable">
                    <thead>
                    <tr>
                        <th class="text-center">Sr No.</th>
                        <th class="text-center">Associate</th>
                        <th class="text-center">Client Name (ID)</th>
                        <th class="text-center">Application</th>
                        <th class="text-center">Services</th>
                        <th class="text-center">Fees</th>
                        <th class="text-center">Home Country</th>
                        <th class="text-center">Visa Country</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($businesses as $key => $business)
                    @php
                        $associateName = trim((string) optional($business->associate)->name);
                        if ($associateName === '' && $business->associate_id) {
                            $fallback = \App\Models\Associate::find($business->associate_id);
                            $associateName = trim((string) optional($fallback)->name);
                        }
                        $associateLabel = $associateName !== '' ? $associateName : 'Unassigned';
                        $associateFilterKey = \App\Services\TableFilterCountService::keyFor($associateLabel);
                        $associateDisplay = $associateName !== ''
                            ? $associateName . ($business->associate_id ? ' (' . $business->associate_id . ')' : '')
                            : '-';
                    @endphp
                    <tr data-filter-value="{{ $associateFilterKey }}">
                        <td class="p-1 text-center">{{ $key+1 }}</td>
                        <td class="p-1 text-center">{{ $associateDisplay }}</td>
                        <td class="p-1 text-center">{{ $business->client_name ?: '-' }}@if($business->client_id) ({{ $business->client_id }})@endif</td>
                        <td class="p-1 text-center">{{ $business->application_name ?: '-' }}</td>
                        <td class="p-1 text-center">{{ $business->formattedServices() }}</td>
                        <td class="p-1 text-center">{{ number_format((float) $business->fees, 2) }}</td>
                        <td class="p-1 text-center">{{ $business->home_country ?: '-' }}</td>
                        <td class="p-1 text-center">{{ $business->visa_country ?: '-' }}</td>
                        <td class="p-1 text-center action-icon">
                            <a href="{{ route('view_associate_business', $business->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></a>
                            <a href="{{ route('edit_associate_business', $business->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fa-solid fa-pen-to-square btn p-1 text-primary" style="font-size:14px;"></i></a>
                            <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteBusiness({{ $business->id }})"></i>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-secondary px-3">No Business Entries Added...</p>
            @endif
        </div>
    </div>

</div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
    });
    function deleteBusiness(id){
        if(confirm('Are you sure you want to delete this business entry?')){
            window.location.href = "{{ url('delete_associate_business') }}/"+id;
        }
    }
</script>

@include('web.associates._alerts')

@endsection()
