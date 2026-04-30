@extends('web.layout.main')

@section('main-section')
<div class="container py-5">
    <h3 class="mb-4">Dummy Razorpay Payment</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="payment-save-form" action="{{ route('razorpay.dummy.store') }}" method="POST" class="card p-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Patient ID</label>
                <input type="number" name="patient_id" class="form-control" value="{{ old('patient_id', $patient_id) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Doctor ID</label>
                <input type="number" name="dr_id" class="form-control" value="{{ old('dr_id', $dr_id) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Appointment ID</label>
                <input type="number" name="appointment_id" class="form-control" value="{{ old('appointment_id', $appointment_id) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Amount (INR)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount', $amount) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Currency</label>
                <input type="text" name="currency" class="form-control" value="{{ old('currency', $currency) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact</label>
                <input type="text" name="contact" class="form-control" value="{{ old('contact') }}">
            </div>
        </div>

        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        <input type="hidden" name="status" id="status" value="captured">

        <button type="button" id="rzp-button" class="btn btn-primary mt-4">Pay with Razorpay</button>
    </form>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('rzp-button').onclick = function (e) {
        e.preventDefault();

        const form = document.getElementById('payment-save-form');
        const amountInPaise = Math.round(parseFloat(document.getElementById('amount').value || 0) * 100);

        const options = {
            key: @json($razorpayKey),
            amount: amountInPaise,
            currency: form.currency.value,
            name: 'Dummy Hospital Checkout',
            description: 'Appointment Payment',
            handler: function (response) {
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id || '';
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id || '';
                document.getElementById('razorpay_signature').value = response.razorpay_signature || '';
                form.submit();
            },
            prefill: {
                name: form.name.value,
                email: form.email.value,
                contact: form.contact.value
            },
            theme: {
                color: '#3399cc'
            }
        };

        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function () {
            alert('Payment failed in dummy flow.');
        });
        rzp.open();
    }
</script>
@endsection
