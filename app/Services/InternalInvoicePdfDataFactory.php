<?php

namespace App\Services;

use App\Models\Internal_Invoices;
use App\Models\Invoice_settings;
use App\Models\User;

class InternalInvoicePdfDataFactory
{
    public static function make(Internal_Invoices $internalInvoice, User $subscriber, User $company): object
    {
        $invoiceSetting = Invoice_settings::forUser((int) $subscriber->id);

        return (object) [
            'invoice_no' => $internalInvoice->invoice_no,
            'invoice_date' => $internalInvoice->created_at,
            'due_date' => $internalInvoice->due_date,
            'status' => $internalInvoice->status,
            'detail' => $internalInvoice->detail,
            'amount' => $internalInvoice->amount,
            'discount' => $internalInvoice->discount,
            'tax' => $internalInvoice->tax,
            'tax_label' => $internalInvoice->tax_label ?? $invoiceSetting?->tax_label,
            'payment_link' => $internalInvoice->payment_link ?? $invoiceSetting?->payment_link,
            'payment_qr_code' => $internalInvoice->payment_qr_code ?? $invoiceSetting?->payment_qr_code,
            'invoice_note' => $internalInvoice->invoice_note,
            'total' => $internalInvoice->total,
            'currency' => 'USD',
            'name' => $subscriber->name,
            'to_email' => $internalInvoice->to_email ?? $subscriber->email,
            'to_address' => $internalInvoice->to_address ?? $subscriber->address_line,
            'to_city' => $internalInvoice->to_city ?? $subscriber->city,
            'to_state' => $internalInvoice->to_state ?? $subscriber->state,
            'to_country' => $internalInvoice->to_country ?? $subscriber->country,
            'to_pincode' => $internalInvoice->to_pincode ?? $subscriber->pincode,
            'company_name' => $company->organization ?: 'adwiseri',
            'from_email' => $company->email,
            'display_from_email' => $company->email,
            'subscriber_id' => $subscriber->id,
            'user_id' => $subscriber->id,
            'invoice_id' => $internalInvoice->id,
            'token' => $internalInvoice->token,
            'logo' => $company->organization_logo,
            'logo_path' => !empty($company->organization_logo)
                ? 'web_assets/users/logos/' . $company->organization_logo
                : null,
        ];
    }
}
