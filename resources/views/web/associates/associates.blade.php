@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                <h3 class="text-primary text-center flex-grow-1 m-0">Associates (B2B)</h3>
                <p class="m-0">
                    <a href="{{ route('add_associate') }}">Add Associate</a>
                </p>
            </div>

            @include('web.associates._tabs')

            <p class="text-secondary px-1 mt-2" style="font-size:13px;">
                Associate allowance for your plan: <strong>{{ $associateLimit }}</strong>
                &nbsp;|&nbsp; Used: <strong>{{ count($associates) }}</strong>
            </p>

            @if(count($associates) != 0)
            @include('partials.table_filter_toolbar', [
                'filterItems' => $associateLocationFilters ?? [],
                'tableId' => 'associateTable',
                'toolbarTitle' => 'Associates by City / Country',
                'totalCount' => count($associates),
            ])
            <div class="table-wrapper">
                <table class="fl-table table table-hover p-0 m-0" id="associateTable">
                    <thead>
                    <tr>
                        <th class="text-center">Sr No.</th>
                        <th class="text-center">Associate ID</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Organization</th>
                        <th class="text-center">City</th>
                        <th class="text-center">Phone No</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Created Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($associates as $key => $associate)
                    @php
                        $associateLocation = \App\Services\TableFilterCountService::associateLocationLabel($associate);
                        $associateFilterKey = \App\Services\TableFilterCountService::keyFor($associateLocation);
                    @endphp
                    <tr data-filter-value="{{ $associateFilterKey }}">
                        <td class="p-1 text-center">{{ $key+1 }}</td>
                        <td class="p-1 text-center">{{ $associate->id }}</td>
                        <td class="p-1 text-center">{{ $associate->name }}</td>
                        <td class="p-1 text-center">{{ $associate->organization ?: '-' }}</td>
                        <td class="p-1 text-center">{{ $associate->city }}</td>
                        <td class="p-1 text-center">@include('partials.phone_display', ['phone' => $associate->phone])</td>
                        <td class="p-1 text-center">{{ $associate->email }}</td>
                        <td class="p-1 text-center">{{ \Carbon\Carbon::parse($associate->created_at)->format('d-m-Y') }}</td>
                        <td class="p-1 text-center action-icon">
                            <a href="{{ route('edit_associate', $associate->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fa-solid fa-eye btn p-1 text-info" style="font-size:14px;"></i></a>
                            <a href="{{ route('edit_associate', $associate->id) }}" style="text-decoration:none;" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fa-solid fa-pen-to-square btn p-1 text-primary" style="font-size:14px;"></i></a>
                            <i class="fa-solid fa-trash btn p-1 text-danger" style="font-size:14px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteAssociate({{ $associate->id }})"></i>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-secondary px-3">Associates Not Added...</p>
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
    function deleteAssociate(id){
        if(confirm('Are you sure you want to delete this associate?')){
            window.location.href = "{{ url('delete_associate') }}/"+id;
        }
    }
</script>

@include('web.associates._alerts')

@endsection()
