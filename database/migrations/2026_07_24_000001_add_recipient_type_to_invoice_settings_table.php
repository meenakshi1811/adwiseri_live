<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_settings', 'recipient_type')) {
                $table->string('recipient_type', 20)->default('clients')->after('user_id');
            }
        });

        DB::table('invoice_settings')
            ->where(function ($query) {
                $query->whereNull('recipient_type')->orWhere('recipient_type', '');
            })
            ->update(['recipient_type' => 'clients']);

        // Allow one settings row per user + audience (clients | associates).
        try {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->unique(['user_id', 'recipient_type'], 'invoice_settings_user_recipient_unique');
            });
        } catch (\Throwable $e) {
            // Index may already exist on re-run.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->dropUnique('invoice_settings_user_recipient_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('invoice_settings', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_settings', 'recipient_type')) {
                $table->dropColumn('recipient_type');
            }
        });
    }
};
