@extends('affiliate.layout.main')
@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="client-btn d-flex mb-2">
                <h3 class="text-primary">Subscribers</h3>
            </div>
            <div class="table-wrapper mt-3">
                <table class="fl-table table table-hover p-0 m-0" id="affiliateSubscribersTable">
                    <thead>
                        <tr>
                            <th class="p-1 text-center">Sr.No.</th>
                            <th class="p-1 text-start">Subscriber Name</th>
                            <th class="p-1 text-start">Email</th>
                            <th class="p-1 text-start">Country</th>
                            <th class="p-1 text-start">Plan</th>
                            <th class="p-1 text-start">Sub-Category</th>
                            <th class="p-1 text-start">Status</th>
                            <th class="p-1 text-start">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $key => $subscriber)
                            <tr>
                                <td class="p-1 text-center">{{ $key + 1 }}</td>
                                <td class="p-1">{{ $subscriber->name }} ({{ $subscriber->id }})</td>
                                <td class="p-1">{{ $subscriber->email }}</td>
                                <td class="p-1">{{ $subscriber->country ?: 'N/A' }}</td>
                                <td class="p-1">{{ $subscriber->membership ?: 'N/A' }}</td>
                                <td class="p-1">{{ $subscriber->sub_category ?: 'N/A' }}</td>
                                <td class="p-1">
                                    @if($subscriber->status == 'true' || $subscriber->status === true || $subscriber->status === 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="p-1">{{ $subscriber->created_at ? \Carbon\Carbon::parse($subscriber->created_at)->format('d-m-Y') : '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-3 text-center text-muted">No referred subscribers yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
