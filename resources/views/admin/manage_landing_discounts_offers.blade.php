@extends('admin.layout.main')

@section('main-section')

<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-primary m-0">Manage Discounts &amp; Offers (Homepage)</h3>
            <small class="text-muted">Shown as a section after Price Plans on the landing page</small>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-header fw-bold">Section Settings</div>
            <div class="card-body">
                <form method="POST" action="{{ route('update_landing_promo_settings') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Heading</label>
                            <input type="text" name="heading" class="form-control" value="{{ old('heading', $settings->heading) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discounts footnote</label>
                            <textarea name="discount_note" class="form-control" rows="3">{{ old('discount_note', $settings->discount_note) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Offers footnote</label>
                            <textarea name="offer_note" class="form-control" rows="3">{{ old('offer_note', $settings->offer_note) }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @foreach([
            'discount' => ['title' => 'Discounts', 'items' => $discountItems],
            'offer' => ['title' => 'Offers', 'items' => $offerItems],
        ] as $category => $group)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold">{{ $group['title'] }} rows</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
                        <table class="table table-hover fl-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Benefit / %</th>
                                    <th>Detail</th>
                                    <th>Order</th>
                                    <th>Active</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($group['items'] as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td colspan="5">
                                        <form method="POST" action="{{ route('update_landing_promo_item', $item->id) }}" class="row g-2 align-items-center">
                                            @csrf
                                            <div class="col-md-3">
                                                <input type="text" name="benefit" class="form-control form-control-sm" value="{{ $item->benefit }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="detail" class="form-control form-control-sm" value="{{ $item->detail }}" required>
                                            </div>
                                            <div class="col-md-1">
                                                <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $item->sort_order }}" min="0">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="m-0">
                                                    <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}> Active
                                                </label>
                                            </div>
                                            <div class="col-md-2 d-flex gap-1">
                                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                                <a href="{{ route('delete_landing_promo_item', $item->id) }}" class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Delete this row?');">Delete</a>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No rows yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <form method="POST" action="{{ route('store_landing_promo_item') }}" class="row g-2 align-items-end border-top pt-3">
                        @csrf
                        <input type="hidden" name="category" value="{{ $category }}">
                        <div class="col-md-3">
                            <label class="form-label">Benefit / %</label>
                            <input type="text" name="benefit" class="form-control" placeholder="e.g. 10%" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Detail</label>
                            <input type="text" name="detail" class="form-control" placeholder="e.g. 2 years Subscription" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ $group['items']->count() + 1 }}" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="m-0 d-block mb-2">&nbsp;</label>
                            <label class="m-0"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-success w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
</div>
</div>

@endsection
