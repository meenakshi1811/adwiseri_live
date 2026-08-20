<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_settings', 'tax_label')) {
                $table->string('tax_label', 10)->default('Tax')->after('tax');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_settings', 'tax_label')) {
                $table->dropColumn('tax_label');
            }
        });
    }
};
