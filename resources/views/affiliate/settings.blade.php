@extends('affiliate.layout.main')
@section('main-section')

<div class="col-lg-10 userdash-client column-client">
    <div class="client-btn d-flex justify-content-between mb-4">
        <h3>Settings</h3>
    </div>

    <div class="profile-detail">
        <div class="col-lg-10 profile-data" style="border: 1px solid lightgrey;">
            <div class="row p-3 m-0">
                <p class="m-0" style="font-size:18px;font-weight:550;">Auto Reports</p>
                <small class="text-muted mt-1">
                    A monthly PDF covering Subscribers Referred, Commissions, and Wallet activity is emailed on the last day of each calendar month (even when counts are zero).
                </small>
            </div>

            <form id="affiliate-reports-settings-form" class="p-3">
                @csrf
                @php
                    $selectedModules = old('modules', optional($reportSetting)->modules ?? array_keys($reportModules));
                    $selectedModules = is_array($selectedModules) ? $selectedModules : [];
                    $reportDefaultEmail = trim((string) (optional($reportSetting)->emails ?? $user->email ?? ''));
                    $selectedDeliveryMode = old('delivery_mode', optional($reportSetting)->delivery_mode ?? 'attachment');
                @endphp

                <div class="mb-3">
                    <label class="fw-semibold d-block mb-2">Report modules (included every month)</label>
                    @foreach ($reportModules as $moduleKey => $moduleLabel)
                        <div class="form-check">
                            <input type="checkbox" name="modules[]" value="{{ $moduleKey }}" class="form-check-input"
                                checked disabled>
                            <input type="hidden" name="modules[]" value="{{ $moduleKey }}">
                            <label class="form-check-label">{{ $moduleLabel }}</label>
                        </div>
                    @endforeach
                </div>

                <input type="hidden" name="frequency" value="monthly">

                <div class="row mb-3 align-items-center">
                    <div class="col-md-4"><label>Frequency</label></div>
                    <div class="col-md-8">
                        <input type="text" class="form-control" value="Monthly (last day of each calendar month)" readonly>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <div class="col-md-4"><label>Delivery Mode</label></div>
                    <div class="col-md-8">
                        <select name="delivery_mode" class="form-control form-select">
                            <option value="attachment" {{ $selectedDeliveryMode === 'attachment' ? 'selected' : '' }}>PDF attachment</option>
                            <option value="link" {{ $selectedDeliveryMode === 'link' ? 'selected' : '' }}>Download link in email</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <div class="col-md-4"><label>Send To</label></div>
                    <div class="col-md-8">
                        <textarea name="emails" class="form-control" rows="2" maxlength="1000" required
                            placeholder="Enter up to 5 emails separated by comma">{{ old('emails', $reportDefaultEmail) }}</textarea>
                        <small class="text-muted">Your affiliate login email is always included first.</small>
                        <div class="invalid-feedback d-block" id="affiliate-reports-emails-error" style="display:none;"></div>
                    </div>
                </div>

                @if(optional($reportSetting)->last_sent_at)
                    <p class="small text-muted">
                        Last sent: {{ optional($reportSetting->last_sent_at)->format('d M Y, H:i') }}
                        ({{ optional($reportSetting)->last_sent_status ?? 'unknown' }})
                    </p>
                @endif

                <div class="text-end">
                    <button type="submit" class="btn btn-primary" id="save-affiliate-reports-settings">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('other-scripts')
<script>
    (function () {
        var form = document.getElementById('affiliate-reports-settings-form');
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var errorBox = document.getElementById('affiliate-reports-emails-error');
            errorBox.style.display = 'none';
            errorBox.textContent = '';

            var formData = new FormData(form);

            fetch("{{ route('save_report_settings') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin',
            }).then(function (response) {
                return response.json().then(function (payload) {
                    return { ok: response.ok, payload: payload };
                });
            }).then(function (result) {
                if (result.ok && result.payload.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: result.payload.message || 'Affiliate report settings saved.'
                    });
                    return;
                }

                var message = result.payload.message || 'Unable to save report settings.';
                if (result.payload.errors && result.payload.errors.emails) {
                    message = result.payload.errors.emails[0];
                    errorBox.textContent = message;
                    errorBox.style.display = 'block';
                }

                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Oops!',
                    text: message
                });
            }).catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong while saving report settings.'
                });
            });
        });
    })();
</script>
@endpush
