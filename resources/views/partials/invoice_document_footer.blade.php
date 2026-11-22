@php
    $isAdwiseriInvoice = $isAdwiseriInvoice ?? false;
    // In the PDF the footer is pinned to the bottom of the page, so it needs no gap of its own.
    $inPdfFooter = $inPdfFooter ?? false;
@endphp

@if($isAdwiseriInvoice && !$inPdfFooter)
    {{-- About ten lines of space between the totals and the footer, so the invoice reads as a full page. --}}
    <div class="invoice-doc-footer-gap" style="height:160px;" aria-hidden="true"></div>
@endif

<div class="invoice-doc-footer-thanks">
    Thank you for the business!
</div>

@if($isAdwiseriInvoice)
    <div class="invoice-doc-footer-divider" style="border-top:1px solid #d1d5db; margin:12px 0 10px;"></div>
    <div class="invoice-doc-footer-contact" style="text-align:center; font-size:11px; color:#111827; line-height:1.7;">
        Web : <a href="https://www.adwiseri.com" style="color:#111827; text-decoration:none;">https://www.adwiseri.com</a>
        <span style="color:#6b7280;"> | </span>
        General Enquiries : <a href="mailto:hello@adwiseri.com" style="color:#111827; text-decoration:none;">hello@adwiseri.com</a>
        <span style="color:#6b7280;"> | </span>
        Support &amp; Suggestions : <a href="mailto:care@adwiseri.com" style="color:#111827; text-decoration:none;">care@adwiseri.com</a>
    </div>
@endif
