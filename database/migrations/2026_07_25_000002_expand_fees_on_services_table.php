<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('services') || !Schema::hasColumn('services', 'fees')) {
            return;
        }

        // Legacy schemas often used DECIMAL(8,2) — max 999,999.99 — which rejects fees like 1,000,000.
        DB::statement('ALTER TABLE `services` MODIFY `fees` DECIMAL(12, 2) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        if (!Schema::hasTable('services') || !Schema::hasColumn('services', 'fees')) {
            return;
        }

        DB::statement('ALTER TABLE `services` MODIFY `fees` DECIMAL(8, 2) NOT NULL DEFAULT 0');
    }
};
