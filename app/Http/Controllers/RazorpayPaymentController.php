<?php

namespace App\Http\Controllers;

use App\Models\RazorpayPayment;
use Illuminate\Http\Request;

class RazorpayPaymentController extends Controller
{
    public function create(Request $request)
    {
        return view('web.razorpay_dummy_payment', [
            'patient_id' => $request->query('patient_id'),
            'dr_id' => $request->query('dr_id'),
            'appointment_id' => $request->query('appointment_id'),
            'amount' => $request->query('amount', 500),
            'currency' => $request->query('currency', 'INR'),
            'razorpayKey' => env('RAZORPAY_KEY', 'rzp_test_dummyKey12345'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|integer',
            'dr_id' => 'required|integer',
            'appointment_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:10',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'contact' => 'nullable|string|max:30',
            'razorpay_payment_id' => 'required|string|max:255',
            'razorpay_order_id' => 'nullable|string|max:255',
            'razorpay_signature' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:30',
        ]);

        $validated['notes'] = [
            'source' => 'dummy_frontend_payment_form',
        ];
        $validated['paid_at'] = now();

        RazorpayPayment::create($validated);

        return redirect()->route('razorpay.dummy.create', [
            'patient_id' => $validated['patient_id'],
            'dr_id' => $validated['dr_id'],
            'appointment_id' => $validated['appointment_id'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
        ])->with('success', 'Dummy Razorpay payment saved successfully.');
    }
}
