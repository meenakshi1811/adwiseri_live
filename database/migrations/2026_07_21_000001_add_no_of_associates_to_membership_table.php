<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('membership') && !Schema::hasColumn('membership', 'no_of_associates')) {
            Schema::table('membership', function (Blueprint $table) {
                $table->integer('no_of_associates')->default(0)->after('no_of_users');
            });
        }

        // Seed the associate allowance for the existing plans.
        // Solo: 0, Adwiseri (Adw): 10, Adwiseri+ (Adw+): 25, Enterprise: 100
        if (Schema::hasTable('membership') && Schema::hasColumn('membership', 'no_of_associates')) {
            $limits = [
                'Solo' => 0,
                'Adwiseri' => 10,
                'Adwiseri+' => 25,
                'Enterprise' => 100,
            ];

            foreach ($limits as $plan => $limit) {
                DB::table('membership')->where('plan_name', $plan)->update(['no_of_associates' => $limit]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('membership') && Schema::hasColumn('membership', 'no_of_associates')) {
            Schema::table('membership', function (Blueprint $table) {
                $table->dropColumn('no_of_associates');
            });
        }
    }
};
