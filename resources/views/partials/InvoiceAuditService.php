<?php

namespace App\Services;

use App\Models\Activities;
use App\Models\Internal_Invoices;
use App\Models\Invoices;
use App\Models\User;

class InvoiceAuditService
{
    public function formatUserName(User $user): string
    {
        return trim($user->name) . ' (' . trim($user->email) . ')';
    }

    public function resolveCreatedByDisplay(Internal_Invoices $invoice): string
    {
        $user = $this->findAuditUser($invoice->created_by)
            ?? $this->findAuditUser($invoice->user_id)
            ?? $this->findAuditUser($invoice->subscriber_id);

        if ($user) {
            return $this->formatUserName($user);
        }

        if (!empty($invoice->created_by_name)) {
            return $invoice->created_by_name;
        }

        return 'Not recorded';
    }

    public function resolveUpdatedByDisplay(Internal_Invoices $invoice): ?string
    {
        if (empty($invoice->updated_by) && empty($invoice->updated_by_name)) {
            return null;
        }

        $user = $this->findAuditUser($invoice->updated_by);
        if ($user) {
            return $this->formatUserName($user);
        }

        return $invoice->updated_by_name ?: null;
    }

    public function ensureCreatedAudit(Internal_Invoices $invoice): void
    {
        if (!empty($invoice->created_by) && !empty($invoice->created_by_name)) {
            return;
        }

        $user = $this->findAuditUser($invoice->created_by)
            ?? $this->findAuditUser($invoice->user_id)
            ?? $this->findAuditUser($invoice->subscriber_id);

        if ($user) {
            $this->markCreated($invoice, $user);
        }
    }

    public function markCreated(Internal_Invoices $invoice, User $user): void
    {
        $invoice->created_by = $user->id;
        $invoice->created_by_name = $this->formatUserName($user);
    }

    public function markUpdated(Internal_Invoices $invoice, User $user): void
    {
        $invoice->updated_by = $user->id;
        $invoice->updated_by_name = $this->formatUserName($user);
    }

    private function findAuditUser($userId): ?User
    {
        if (empty($userId)) {
            return null;
        }

        return User::find($userId);
    }

    public function logActivity(User $user, int $subscriberId, string $activityName, string $detail, ?string $localTime = null): void
    {
        $activity = new Activities();
        $activity->subscriber_id = $subscriberId;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->activity_name = $activityName;
        $activity->activity_detail = $detail;
        $activity->activity_icon = 'invoice.jpg';
        $activity->local_time = $localTime;
        $activity->save();
    }

    public function syncLegacyInvoiceIfPaid(Internal_Invoices $invoice, User $actingUser): void
    {
        if ($invoice->status !== 'Paid') {
            return;
        }

        $legacyInvoice = Invoices::where('invoice', '=', $invoice->invoice_no)->first();
        if ($legacyInvoice === null) {
            $legacyInvoice = new Invoices();
        }

        $legacyInvoice->user_id = $invoice->subscriber_id;
        $legacyInvoice->invoice = $invoice->invoice_no;
        $legacyInvoice->company_name = $invoice->name;
        $legacyInvoice->city = $invoice->city;
        $legacyInvoice->state = $invoice->state;
        $legacyInvoice->country = $invoice->country;
        $legacyInvoice->pincode = $invoice->pincode;
        $legacyInvoice->phone = $invoice->phone;
        $legacyInvoice->address = $invoice->address;
        $legacyInvoice->logo = $invoice->logo;
        $legacyInvoice->to_name = $invoice->to_name;
        $legacyInvoice->to_company = $actingUser->email;
        $legacyInvoice->to_city = $invoice->to_city;
        $legacyInvoice->to_state = $invoice->to_state;
        $legacyInvoice->to_country = $invoice->to_country;
        $legacyInvoice->to_pincode = $invoice->to_pincode;
        $legacyInvoice->to_phone = $invoice->to_phone;
        $legacyInvoice->to_email = $invoice->to_email;
        $legacyInvoice->service_fee = $invoice->amount;
        $legacyInvoice->discount = ($invoice->amount * ($invoice->discount / 100));
        $legacyInvoice->tax = (($invoice->amount - ($invoice->amount * $invoice->discount / 100)) * ($invoice->tax / 100));
        $legacyInvoice->total = $invoice->total;
        $legacyInvoice->payment_mode = 'Cash';
        $legacyInvoice->save();
    }
}
