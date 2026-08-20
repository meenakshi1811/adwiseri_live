<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_invoices', 'export_service_tax_exempt')) {
                $afterColumn = Schema::hasColumn('internal_invoices', 'tax_label') ? 'tax_label' : 'tax';
                $table->boolean('export_service_tax_exempt')->default(false)->after($afterColumn);
            }
        });
    }

    public function down(): void
    {
        Schema::table('internal_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('internal_invoices', 'export_service_tax_exempt')) {
                $table->dropColumn('export_service_tax_exempt');
            }
        });
    }
};
