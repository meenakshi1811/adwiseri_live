<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_settings', 'invoice_note')) {
                $table->text('invoice_note')->nullable()->after('payment_qr_code');
            }
        });

        Schema::table('internal_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_invoices', 'invoice_note')) {
                $table->text('invoice_note')->nullable()->after('payment_qr_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_settings', 'invoice_note')) {
                $table->dropColumn('invoice_note');
            }
        });

        Schema::table('internal_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('internal_invoices', 'invoice_note')) {
                $table->dropColumn('invoice_note');
            }
        });
    }
};
