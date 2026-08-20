<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (!Schema::hasColumn('offers', 'upgrade_from_plan')) {
                $table->string('upgrade_from_plan', 100)->nullable()->after('discount_value');
            }
            if (!Schema::hasColumn('offers', 'upgrade_to_plan')) {
                $table->string('upgrade_to_plan', 100)->nullable()->after('upgrade_from_plan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            if (Schema::hasColumn('offers', 'upgrade_to_plan')) {
                $table->dropColumn('upgrade_to_plan');
            }
            if (Schema::hasColumn('offers', 'upgrade_from_plan')) {
                $table->dropColumn('upgrade_from_plan');
            }
        });
    }
};
