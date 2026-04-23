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
            if (!Schema::hasColumn('visa_enquiries', 'consent_to_store_data')) {
                $table->boolean('consent_to_store_data')->default(false)->after('signature');
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
            if (Schema::hasColumn('visa_enquiries', 'consent_to_store_data')) {
                $table->dropColumn('consent_to_store_data');
            }
        });
    }
};
