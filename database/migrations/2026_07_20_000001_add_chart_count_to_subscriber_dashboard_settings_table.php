<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscriber_dashboard_settings')) {
            return;
        }

        Schema::table('subscriber_dashboard_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriber_dashboard_settings', 'chart_count')) {
                $table->unsignedTinyInteger('chart_count')->default(6)->after('charts');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscriber_dashboard_settings')) {
            return;
        }

        Schema::table('subscriber_dashboard_settings', function (Blueprint $table) {
            if (Schema::hasColumn('subscriber_dashboard_settings', 'chart_count')) {
                $table->dropColumn('chart_count');
            }
        });
    }
};
