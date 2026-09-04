<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('membership') && !Schema::hasColumn('membership', 'email_broadcast_limit')) {
            Schema::table('membership', function (Blueprint $table) {
                $table->unsignedInteger('email_broadcast_limit')->default(0)->after('messaging');
            });
        }

        if (Schema::hasTable('membership') && Schema::hasColumn('membership', 'email_broadcast_limit')) {
            $limits = [
                'Solo' => 1000,
                'Adwiseri' => 5000,
                'Adwiseri+' => 25000,
                'Enterprise' => 100000,
            ];

            foreach ($limits as $plan => $limit) {
                DB::table('membership')->where('plan_name', $plan)->update(['email_broadcast_limit' => $limit]);
            }

            DB::table('membership')->where('plan_name', 'Advisory+')->update(['email_broadcast_limit' => 25000]);
            DB::table('membership')->where('plan_name', 'Enterprises')->update(['email_broadcast_limit' => 100000]);
            DB::table('membership')->whereRaw('LOWER(TRIM(plan_name)) IN (?, ?)', ['free', 'free plan'])
                ->update(['email_broadcast_limit' => 0]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('membership') && Schema::hasColumn('membership', 'email_broadcast_limit')) {
            Schema::table('membership', function (Blueprint $table) {
                $table->dropColumn('email_broadcast_limit');
            });
        }
    }
};
