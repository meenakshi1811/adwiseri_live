@php
    $snapshotService = app(\App\Services\InvoiceSnapshotService::class);
    $invoiceNote = $snapshotService->resolvedInvoiceNote($invoice, $invoiceSetting ?? null);
    $noteParagraphs = array_values(array_filter(
        preg_split('/\R/', $invoiceNote) ?: [],
        static fn ($line) => trim((string) $line) !== ''
    ));
@endphp
@if(count($noteParagraphs) > 0)
    <div class="invoice-note-section" style="margin-top: 20px; font-size: 0.95rem; line-height: 1.6;">
        <p style="margin: 0 0 8px 0;"><strong>Note :</strong></p>
        @foreach($noteParagraphs as $paragraph)
            <p style="margin: 0 0 8px 0;">{{ $paragraph }}</p>
        @endforeach
    </div>
@endif
