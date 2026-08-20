<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_settings', 'payment_qr_code')) {
                $table->string('payment_qr_code', 255)->nullable()->after('payment_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_settings', 'payment_qr_code')) {
                $table->dropColumn('payment_qr_code');
            }
        });
    }
};
