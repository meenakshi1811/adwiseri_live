<div class="msg-row" style="align-items: flex-start;">
    <div class="msg-label" style="padding-top: 0.5rem;">
        <h6>Attachments</h6>
    </div>
    <div class="msg-field">
        <input type="file"
            name="attachments[]"
            id="message_attachments"
            class="form-control message-attachments-input"
            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt"
            multiple />
        <div class="form-text">
            Optional — up to {{ \App\Support\InternalMessageAttachments::MAX_FILES }} files, 25 MB combined
            (jpg, png, pdf, doc, docx, xls, xlsx, txt).
        </div>
        <div class="message-attachments-summary text-muted small mt-1" aria-live="polite"></div>
        @error('attachments')
            <span class="text-danger d-block">{{ $message }}</span>
        @enderror
        @error('attachments.*')
            <span class="text-danger d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    var maxFiles = {{ (int) \App\Support\InternalMessageAttachments::MAX_FILES }};
    var maxTotalBytes = {{ (int) \App\Support\InternalMessageAttachments::MAX_TOTAL_BYTES }};

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function validateMessageAttachments(input) {
        var files = input.files ? Array.prototype.slice.call(input.files) : [];
        var summary = input.closest('.msg-field').querySelector('.message-attachments-summary');
        if (!files.length) {
            if (summary) summary.textContent = '';
            return true;
        }

        var total = 0;
        for (var i = 0; i < files.length; i++) {
            total += files[i].size || 0;
        }

        if (files.length > maxFiles) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Too many files',
                    text: 'You can attach up to ' + maxFiles + ' documents.'
                });
            }
            input.value = '';
            if (summary) summary.textContent = '';
            return false;
        }

        if (total > maxTotalBytes) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Attachments too large',
                    text: 'Total attachment size must not exceed 25 MB.'
                });
            }
            input.value = '';
            if (summary) summary.textContent = '';
            return false;
        }

        if (summary) {
            summary.textContent = files.length + ' file(s) selected — ' + formatSize(total) + ' total';
        }
        return true;
    }

    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('message-attachments-input')) {
            validateMessageAttachments(e.target);
        }
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.querySelector) return;
        var input = form.querySelector('.message-attachments-input');
        if (input && !validateMessageAttachments(input)) {
            e.preventDefault();
        }
    }, true);
})();
</script>
@endpush
@endonce
