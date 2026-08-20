<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Legacy offers.discount_type was an ENUM limited to manual types only.
     * Automated offer keys (e.g. 3_months_extra) require a wider string column.
     */
    public function up(): void
    {
        if (Schema::hasTable('offers') && Schema::hasColumn('offers', 'discount_type')) {
            DB::statement('ALTER TABLE `offers` MODIFY `discount_type` VARCHAR(64) NOT NULL');
        }

        if (Schema::hasTable('offers') && Schema::hasColumn('offers', 'subscriber_type')) {
            DB::statement("ALTER TABLE `offers` MODIFY `subscriber_type` VARCHAR(64) NOT NULL DEFAULT 'existing_single'");
        }

        if (Schema::hasTable('referrals') && Schema::hasColumn('referrals', 'type')) {
            DB::statement('ALTER TABLE `referrals` MODIFY `type` VARCHAR(64) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('offers') && Schema::hasColumn('offers', 'discount_type')) {
            DB::statement("ALTER TABLE `offers` MODIFY `discount_type` ENUM('cashback','one_off','double_term') NOT NULL");
        }

        if (Schema::hasTable('offers') && Schema::hasColumn('offers', 'subscriber_type')) {
            DB::statement("ALTER TABLE `offers` MODIFY `subscriber_type` VARCHAR(255) NOT NULL DEFAULT 'existing'");
        }

        if (Schema::hasTable('referrals') && Schema::hasColumn('referrals', 'type')) {
            DB::statement('ALTER TABLE `referrals` MODIFY `type` VARCHAR(255) NULL');
        }
    }
};
