<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('associate_businesses')) {
            return;
        }

        Schema::table('associate_businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('associate_businesses', 'other_service')) {
                $table->string('other_service', 255)->nullable()->after('services');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('associate_businesses')) {
            return;
        }

        Schema::table('associate_businesses', function (Blueprint $table) {
            if (Schema::hasColumn('associate_businesses', 'other_service')) {
                $table->dropColumn('other_service');
            }
        });
    }
};
