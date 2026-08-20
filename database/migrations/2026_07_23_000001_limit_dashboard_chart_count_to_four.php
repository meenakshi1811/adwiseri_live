<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscriber_dashboard_settings')) {
            return;
        }

        if (!Schema::hasColumn('subscriber_dashboard_settings', 'chart_count')) {
            return;
        }

        DB::table('subscriber_dashboard_settings')
            ->where(function ($query) {
                $query->whereNull('chart_count')
                    ->orWhere('chart_count', '>', 4);
            })
            ->update(['chart_count' => 4]);
    }

    public function down(): void
    {
        // Chart count of 6 is no longer supported; leave stored values at 4.
    }
};
