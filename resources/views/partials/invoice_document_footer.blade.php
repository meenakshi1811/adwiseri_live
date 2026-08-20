@php
    $isAdwiseriInvoice = $isAdwiseriInvoice ?? false;
@endphp

<div class="invoice-doc-footer-thanks">
    Thank you for the business!
</div>

@if($isAdwiseriInvoice)
    <div class="invoice-doc-footer-divider" style="border-top:1px solid #d1d5db; margin:12px 0 10px;"></div>
    <div class="invoice-doc-footer-contact" style="text-align:center; font-size:10px; color:#6b7280; line-height:1.7;">
        Web : <a href="https://www.adwiseri.com" style="color:#6b7280; text-decoration:none;">https://www.adwiseri.com</a>
        <span style="color:#9ca3af;"> | </span>
        General Enquiries : <a href="mailto:hello@adwiseri.com" style="color:#6b7280; text-decoration:none;">hello@adwiseri.com</a>
        <span style="color:#9ca3af;"> | </span>
        Support &amp; Suggestions : <a href="mailto:care@adwiseri.com" style="color:#6b7280; text-decoration:none;">care@adwiseri.com</a>
    </div>
@endif
