<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('offers') || !Schema::hasColumn('offers', 'user_id')) {
            return;
        }

        DB::statement('ALTER TABLE `offers` MODIFY `user_id` BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('offers') || !Schema::hasColumn('offers', 'user_id')) {
            return;
        }

        DB::statement('ALTER TABLE `offers` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
    }
};
