<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('offers') && !Schema::hasColumn('offers', 'applied_by')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->unsignedBigInteger('applied_by')->nullable()->after('offer_end_date');
                $table->string('applied_by_name')->nullable()->after('applied_by');
            });
        }

        if (Schema::hasTable('referrals') && !Schema::hasColumn('referrals', 'applied_by')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->unsignedBigInteger('applied_by')->nullable()->after('offer_id');
                $table->string('applied_by_name')->nullable()->after('applied_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('offers') && Schema::hasColumn('offers', 'applied_by')) {
            Schema::table('offers', function (Blueprint $table) {
                $table->dropColumn(['applied_by', 'applied_by_name']);
            });
        }

        if (Schema::hasTable('referrals') && Schema::hasColumn('referrals', 'applied_by')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->dropColumn(['applied_by', 'applied_by_name']);
            });
        }
    }
};
