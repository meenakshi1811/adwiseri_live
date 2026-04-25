<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('visa_enquiries')) {
            return;
        }

        Schema::table('visa_enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('visa_enquiries', 'form_date')) {
                $table->date('form_date')->nullable()->after('spouse_contact');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('visa_enquiries')) {
            return;
        }

        Schema::table('visa_enquiries', function (Blueprint $table) {
            if (Schema::hasColumn('visa_enquiries', 'form_date')) {
                $table->dropColumn('form_date');
            }
        });
    }
};
