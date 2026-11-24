@extends('affiliate.layout.main')
@section('main-section')

<div class="col-lg-10 userdash-client column-client">
    <div class="client-btn d-flex mb-4">
        <h3 class="text-primary text-center flex-grow-1 m-0">Settings</h3>
    </div>

    @php
        $frequencyOptions = [
            'weekly' => "Weekly - last week's report (every Monday, 8:00 AM)",
            'monthly' => "Monthly - the calendar month's report (last day of the month, 11:00 PM)",
            'quarterly' => "Quarterly - last 3 calendar months' report (1st of Jan, Apr, Jul, Oct, 8:00 AM)",
        ];
        $selectedFrequency = old('frequency', optional($reportSetting)->frequency ?: 'monthly');
        if (!array_key_exists($selectedFrequency, $frequencyOptions)) {
            $selectedFrequency = 'monthly';
        }
    @endphp
    <div class="profile-detail">
        <div class="col-lg-10 mx-auto profile-data" style="border: 1px solid lightgrey;">
            <div class="row p-3 m-0">
                <p class="m-0" style="font-size:18px;font-weight:550;">Auto Reports</p>
                <small class="text-muted mt-1">
                    A PDF covering Subscribers Referred, Commissions, and Wallet activity is emailed at the frequency you choose below, in your account timezone (even when counts are zero).
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
                    <label class="fw-semibold d-block mb-2">Report modules (included in every report)</label>
                    @foreach ($reportModules as $moduleKey => $moduleLabel)
                        <div class="form-check">
                            <input type="checkbox" name="modules[]" value="{{ $moduleKey }}" class="form-check-input"
                                checked disabled>
                            <input type="hidden" name="modules[]" value="{{ $moduleKey }}">
                            <label class="form-check-label">{{ $moduleLabel }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="row mb-3 align-items-center">
                    <div class="col-md-4"><label for="affiliateReportFrequency">Frequency</label></div>
                    <div class="col-md-8">
                        <select name="frequency" id="affiliateReportFrequency" class="form-control form-select">
                            @foreach ($frequencyOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedFrequency === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
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

                <div class="d-flex flex-wrap justify-content-end gap-2">
                    {{-- Emails the report now (for the frequency chosen above), so it can be checked without waiting. --}}
                    <button type="button" class="btn btn-outline-primary" id="send-affiliate-test-report">Send Test Report Now</button>
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

        // "Send Test Report Now": emails the report straight away for the frequency chosen in the form.
        var testButton = document.getElementById('send-affiliate-test-report');
        if (testButton) {
            testButton.addEventListener('click', function () {
                var formData = new FormData();
                formData.append('frequency', form.querySelector('[name="frequency"]').value);
                formData.append('delivery_mode', form.querySelector('[name="delivery_mode"]').value);
                testButton.disabled = true;
                var label = testButton.textContent;
                testButton.textContent = 'Sending...';

                fetch("{{ route('affiliate_send_test_report') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin',
                }).then(function (response) {
                    return response.json().then(function (payload) { return { ok: response.ok, payload: payload }; });
                }).then(function (result) {
                    var ok = result.ok && result.payload.status;
                    Swal.fire({
                        icon: ok ? 'success' : 'warning',
                        customClass: ok ? {} : { icon: 'adwiseri-oops-icon' },
                        title: ok ? 'Test report sent' : 'Oops!',
                        text: result.payload.message || (ok ? 'Test report sent.' : 'The test report could not be sent.')
                    });
                }).catch(function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'The test report could not be sent. Please try again.' });
                }).finally(function () {
                    testButton.disabled = false;
                    testButton.textContent = label;
                });
            });
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
