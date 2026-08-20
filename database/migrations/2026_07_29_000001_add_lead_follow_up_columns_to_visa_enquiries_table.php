<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('visa_enquiries')) {
            return;
        }

        Schema::table('visa_enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('visa_enquiries', 'lead_source')) {
                $table->string('lead_source', 50)->default('Walk-in')->after('status');
            }

            if (!Schema::hasColumn('visa_enquiries', 'lead_status')) {
                $table->string('lead_status', 50)->default('Open')->after('lead_source');
            }

            if (!Schema::hasColumn('visa_enquiries', 'lead_worked_by_user_id')) {
                $table->unsignedBigInteger('lead_worked_by_user_id')->nullable()->after('lead_status');
            }

            if (!Schema::hasColumn('visa_enquiries', 'lead_worked_at')) {
                $table->timestamp('lead_worked_at')->nullable()->after('lead_worked_by_user_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('visa_enquiries')) {
            return;
        }

        Schema::table('visa_enquiries', function (Blueprint $table) {
            if (Schema::hasColumn('visa_enquiries', 'lead_worked_at')) {
                $table->dropColumn('lead_worked_at');
            }

            if (Schema::hasColumn('visa_enquiries', 'lead_worked_by_user_id')) {
                $table->dropColumn('lead_worked_by_user_id');
            }

            if (Schema::hasColumn('visa_enquiries', 'lead_status')) {
                $table->dropColumn('lead_status');
            }

            if (Schema::hasColumn('visa_enquiries', 'lead_source')) {
                $table->dropColumn('lead_source');
            }
        });
    }
};
