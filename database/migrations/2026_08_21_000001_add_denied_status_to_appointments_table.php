<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments') || !Schema::hasColumn('appointments', 'status')) {
            return;
        }

        DB::statement(
            "ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'accepted', 'denied', 'canceled', 'completed') NOT NULL DEFAULT 'pending'"
        );

        DB::table('appointments')
            ->where('status', 'canceled')
            ->update(['status' => 'denied']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('appointments') || !Schema::hasColumn('appointments', 'status')) {
            return;
        }

        DB::table('appointments')
            ->where('status', 'denied')
            ->update(['status' => 'canceled']);

        DB::statement(
            "ALTER TABLE appointments MODIFY COLUMN status ENUM('pending', 'accepted', 'canceled', 'completed') NOT NULL DEFAULT 'pending'"
        );
    }
};
