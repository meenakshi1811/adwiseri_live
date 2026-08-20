<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const REMOVED_STATUSES = [
        'Pending',
        'In Process',
        'Complete',
    ];

    private const STATUS_MAP = [
        'Pending' => 'Preparation',
        'In Process' => 'Applied',
        'Complete' => 'Decision',
    ];

    public function up(): void
    {
        foreach (self::STATUS_MAP as $from => $to) {
            if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'application_status')) {
                DB::table('applications')
                    ->where('application_status', $from)
                    ->update(['application_status' => $to]);
            }

            if (Schema::hasTable('associate_businesses') && Schema::hasColumn('associate_businesses', 'application_status')) {
                DB::table('associate_businesses')
                    ->where('application_status', $from)
                    ->update(['application_status' => $to]);
            }
        }

        if (Schema::hasTable('application_status_tracks')) {
            DB::table('application_status_tracks')
                ->whereIn('status', self::REMOVED_STATUSES)
                ->delete();
        }
    }

    public function down(): void
    {
        foreach (self::STATUS_MAP as $from => $to) {
            if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'application_status')) {
                DB::table('applications')
                    ->where('application_status', $to)
                    ->update(['application_status' => $from]);
            }

            if (Schema::hasTable('associate_businesses') && Schema::hasColumn('associate_businesses', 'application_status')) {
                DB::table('associate_businesses')
                    ->where('application_status', $to)
                    ->update(['application_status' => $from]);
            }
        }
    }
};
