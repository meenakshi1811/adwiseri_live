@extends('admin.layout.main')

@section('main-section')

<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h3 class="text-primary m-0">Homepage Sections</h3>
            <small class="text-muted">Turn a section off to hide it completely on the public homepage</small>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header fw-bold">Section Visibility</div>
            <div class="card-body">
                <form method="POST" action="{{ route('update_homepage_sections') }}" id="homepage-sections-form">
                    @csrf
                    <div class="list-group list-group-flush">
                        @foreach($sectionDefinitions as $key => $label)
                            @php $isVisible = !empty($sectionVisibility[$key]); @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                                <div>
                                    <div class="fw-semibold">{{ $label }}</div>
                                    <small class="text-muted">Section key: {{ $key }}</small>
                                </div>
                                <div class="form-check form-switch m-0 homepage-section-switch">
                                    <input type="hidden" name="sections[{{ $key }}]" value="0">
                                    <input
                                        class="form-check-input homepage-section-toggle"
                                        type="checkbox"
                                        role="switch"
                                        id="section_{{ $key }}"
                                        name="sections[{{ $key }}]"
                                        value="1"
                                        @if($isVisible) checked @endif
                                    >
                                    <label class="form-check-label homepage-section-status" for="section_{{ $key }}">
                                        {{ $isVisible ? 'Visible' : 'Hidden' }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save Section Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>

<style>
    .homepage-section-switch .form-check-input {
        width: 2.75rem;
        height: 1.4rem;
        cursor: pointer;
    }
    .homepage-section-switch .form-check-input:checked {
        background-color: #695EEE !important;
        border-color: #695EEE !important;
    }
    .homepage-section-switch .form-check-input:focus {
        border-color: #695EEE;
        box-shadow: 0 0 0 0.2rem rgba(105, 94, 238, 0.25);
    }
    .homepage-section-status {
        min-width: 4.5rem;
        font-weight: 600;
        color: #5A6275;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.homepage-section-toggle').forEach(function (toggle) {
            var label = toggle.closest('.homepage-section-switch').querySelector('.homepage-section-status');

            toggle.addEventListener('change', function () {
                label.textContent = toggle.checked ? 'Visible' : 'Hidden';
            });
        });
    });
</script>

@endsection
