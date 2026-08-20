<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_STATUS = 'Registration';
    private const REPLACEMENT_STATUS = 'Client Registered';

    public function up(): void
    {
        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'application_status')) {
            DB::table('applications')
                ->where('application_status', self::LEGACY_STATUS)
                ->update(['application_status' => self::REPLACEMENT_STATUS]);
        }

        if (Schema::hasTable('associate_businesses') && Schema::hasColumn('associate_businesses', 'application_status')) {
            DB::table('associate_businesses')
                ->where('application_status', self::LEGACY_STATUS)
                ->update(['application_status' => self::REPLACEMENT_STATUS]);
        }

        if (Schema::hasTable('application_status_tracks')) {
            DB::table('application_status_tracks')
                ->where('status', self::LEGACY_STATUS)
                ->update(['status' => self::REPLACEMENT_STATUS]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'application_status')) {
            DB::table('applications')
                ->where('application_status', self::REPLACEMENT_STATUS)
                ->update(['application_status' => self::LEGACY_STATUS]);
        }

        if (Schema::hasTable('associate_businesses') && Schema::hasColumn('associate_businesses', 'application_status')) {
            DB::table('associate_businesses')
                ->where('application_status', self::REPLACEMENT_STATUS)
                ->update(['application_status' => self::LEGACY_STATUS]);
        }

        if (Schema::hasTable('application_status_tracks')) {
            DB::table('application_status_tracks')
                ->where('status', self::REPLACEMENT_STATUS)
                ->update(['status' => self::LEGACY_STATUS]);
        }
    }
};
