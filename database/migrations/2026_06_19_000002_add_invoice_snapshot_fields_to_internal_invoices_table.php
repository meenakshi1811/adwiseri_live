<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_invoices', 'tax_label')) {
                $table->string('tax_label', 10)->nullable()->after('tax');
            }
            if (!Schema::hasColumn('internal_invoices', 'payment_link')) {
                $table->string('payment_link', 500)->nullable()->after('tax_label');
            }
            if (!Schema::hasColumn('internal_invoices', 'payment_qr_code')) {
                $table->string('payment_qr_code', 255)->nullable()->after('payment_link');
            }
        });

        DB::table('internal_invoices')
            ->orderBy('id')
            ->chunkById(200, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $subscriberId = $invoice->subscriber_id ?: $invoice->user_id;
                    if (!$subscriberId) {
                        continue;
                    }

                    $settings = DB::table('invoice_settings')
                        ->where('user_id', $subscriberId)
                        ->first();

                    if (!$settings) {
                        continue;
                    }

                    DB::table('internal_invoices')
                        ->where('id', $invoice->id)
                        ->update([
                            'tax_label' => $settings->tax_label ?? 'Tax',
                            'payment_link' => $settings->payment_link,
                            'payment_qr_code' => $settings->payment_qr_code ?? null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('internal_invoices', function (Blueprint $table) {
            foreach (['tax_label', 'payment_link', 'payment_qr_code'] as $column) {
                if (Schema::hasColumn('internal_invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
