<?php

use App\Models\Internal_Invoices;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('internal_invoices')
            ->where('type', 'ap')
            ->where('detail', 'like', 'Subscription Fees%')
            ->update([
                'to_name' => Internal_Invoices::ADWISERI_VENDOR_NAME,
                'vendor_id' => Internal_Invoices::ADWISERI_VENDOR_ID,
            ]);

        $subscriptionInvoiceNos = DB::table('internal_invoices')
            ->where('type', 'ap')
            ->where('detail', 'like', 'Subscription Fees%')
            ->pluck('invoice_no')
            ->filter()
            ->values()
            ->all();

        if ($subscriptionInvoiceNos !== []) {
            DB::table('payment_ar')
                ->where('type', 'ap')
                ->whereIn('invoice_no', $subscriptionInvoiceNos)
                ->update([
                    'service_provider' => Internal_Invoices::ADWISERI_VENDOR_NAME,
                ]);
        }
    }

    public function down(): void
    {
        // Historical vendor values are not restored.
    }
};
