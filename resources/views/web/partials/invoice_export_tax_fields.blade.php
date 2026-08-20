@php
    $invoice = $invoice ?? null;
    $defaultTaxPercent = max(0, min(100, (float) ($defaultTaxPercent ?? 0)));
    $taxLabel = $taxLabel ?? 'Tax';
    $preferInvoiceValues = (bool) ($preferInvoiceValues ?? false);
    $isExempt = (bool) old('export_service_tax_exempt', $invoice ? $invoice->export_service_tax_exempt : false);
    if ($preferInvoiceValues && $invoice) {
        $currentTax = old('tax', $invoice->tax ?? 0);
    } else {
        $currentTax = old('tax', $invoice ? ($invoice->tax ?? $defaultTaxPercent) : $defaultTaxPercent);
    }
@endphp

<div class="col-md-4 p-1">
    <label>Service to abroad-based client<span class="text-danger" style="font-size: 18px;">*</span></label>
</div>
<div class="col-md-8 p-1">
    <div class="d-flex flex-column gap-2">
        <label class="d-flex align-items-start gap-2 mb-0">
            <input type="radio" name="export_service_tax_exempt" value="0" class="mt-1 export-tax-exempt-radio"
                {{ !$isExempt ? 'checked' : '' }}>
            <span><strong>Standard invoice</strong> — tax applies as per your settings ({{ number_format($defaultTaxPercent, 2) }}%)</span>
        </label>
        <label class="d-flex align-items-start gap-2 mb-0">
            <input type="radio" name="export_service_tax_exempt" value="1" class="mt-1 export-tax-exempt-radio"
                {{ $isExempt ? 'checked' : '' }}>
            <span><strong>Export of services</strong> — abroad-based client, tax exempt (no tax on invoice)</span>
        </label>
    </div>
    @error('export_service_tax_exempt')
        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div id="invoice-ar-tax-wrap" class="col-12 px-0" style="{{ $isExempt ? 'display:none' : '' }}">
    <div class="row mx-0 w-100">
        <div class="col-md-4 p-1">
            <label>{{ $taxLabel }} (%)<span class="text-danger export-tax-required" style="font-size: 18px;">*</span></label>
        </div>
        <div class="col-md-8 p-1">
            <input name="tax" id="tax_percent" type="number" min="0" max="100" step="0.01"
                class="form-control @error('tax') is-invalid @enderror"
                value="{{ $currentTax }}"
                placeholder="{{ number_format($defaultTaxPercent, 2) }}"
                {{ $isExempt ? '' : 'required' }}>
            @error('tax')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function syncExportTaxFields() {
            var exempt = document.querySelector('input[name="export_service_tax_exempt"]:checked');
            var isExempt = exempt && exempt.value === '1';
            var wrap = document.getElementById('invoice-ar-tax-wrap');
            var taxInput = document.getElementById('tax_percent');
            if (wrap) wrap.style.display = isExempt ? 'none' : '';
            if (taxInput) taxInput.required = !isExempt;
        }
        document.querySelectorAll('.export-tax-exempt-radio').forEach(function (radio) {
            radio.addEventListener('change', syncExportTaxFields);
        });
        syncExportTaxFields();
    });
</script>
