<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_invoices', 'tax_label')) {
                $table->string('tax_label', 10)->nullable()->after('tax');
            }
        });

        Schema::table('internal_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_invoices', 'payment_link')) {
                $after = Schema::hasColumn('internal_invoices', 'tax_label') ? 'tax_label' : 'tax';
                $table->string('payment_link', 500)->nullable()->after($after);
            }
        });

        Schema::table('internal_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_invoices', 'payment_qr_code')) {
                $after = Schema::hasColumn('internal_invoices', 'payment_link') ? 'payment_link' : 'tax';
                $table->string('payment_qr_code', 255)->nullable()->after($after);
            }
        });

        Schema::table('internal_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_invoices', 'export_service_tax_exempt')) {
                if (Schema::hasColumn('internal_invoices', 'tax_label')) {
                    $table->boolean('export_service_tax_exempt')->default(false)->after('tax_label');
                } elseif (Schema::hasColumn('internal_invoices', 'tax')) {
                    $table->boolean('export_service_tax_exempt')->default(false)->after('tax');
                } else {
                    $table->boolean('export_service_tax_exempt')->default(false);
                }
            }
        });
    }

    public function down(): void
    {
        // Repair migration — no rollback.
    }
};
