<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('associate_businesses')
            && !Schema::hasColumn('associate_businesses', 'application_status')) {
            Schema::table('associate_businesses', function (Blueprint $table) {
                $table->string('application_status', 100)->nullable()->after('application_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('associate_businesses')
            && Schema::hasColumn('associate_businesses', 'application_status')) {
            Schema::table('associate_businesses', function (Blueprint $table) {
                $table->dropColumn('application_status');
            });
        }
    }
};
