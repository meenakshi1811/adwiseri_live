@php
    $invoiceNote = old('invoice_note', trim((string) ($invoiceNote ?? '')));
    $invoiceNote = is_string($invoiceNote) ? $invoiceNote : '';
    $isLocked = (bool) ($isLocked ?? false);
@endphp
<div class="col-md-4 p-1">
    <label>Note</label>
</div>
<div class="col-md-8 p-1">
    <textarea class="form-control" rows="4"
        @if($isLocked)
            name="invoice_note"
            placeholder="Optional note for this invoice"
        @else
            readonly
            placeholder="{{ trim($invoiceNote) === '' ? 'No note configured in Invoice Settings' : '' }}"
        @endif
    >{{ $invoiceNote }}</textarea>
    <small class="text-muted d-block mt-1">
        @if($isLocked)
            Editable for last-minute changes on this invoice only.
        @else
            From Invoice Settings. This note will appear on the invoice when created.
        @endif
    </small>
</div>
