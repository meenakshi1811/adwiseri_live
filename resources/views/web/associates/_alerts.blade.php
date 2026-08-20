{{-- Shared SweetAlert flash messages for the Associates module --}}
@php
    $associateSuccess = collect([
        'associate_added', 'associate_updated', 'associate_deleted',
        'business_added', 'business_updated', 'business_deleted',
        'invoice_added', 'invoice_updated', 'invoice_deleted',
        'payment_added', 'payment_deleted',
    ])->first(fn($k) => session()->has($k));
@endphp

@if($associateSuccess)
<script>
    Swal.fire({ icon: 'success', title: 'Success', text: @json(session($associateSuccess)) });
</script>
@endif

@if(session()->has('invoice_email_failed'))
<script>
    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Invoice Created', text: @json(session('invoice_email_failed')) });
</script>
@endif

@if(session()->has('payment_error'))
<script>
    Swal.fire({ icon: 'error', title: 'Error', text: @json(session('payment_error')) });
</script>
@endif

@if(session()->has('associate_limit'))
<script>
    Swal.fire({
        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
        title: 'Associate Limit Reached!',
        text: @json(session('associate_limit')),
        showCancelButton: true,
        confirmButtonText: 'Upgrade',
        cancelButtonText: 'Later',
        buttonsStyling: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route('user_membership') }}';
        }
    });
</script>
@endif

@if(session()->has('all_invoices_created'))
<script>
    Swal.fire({ icon: 'info', title: 'Notice', text: @json(session('all_invoices_created')) });
</script>
@endif
