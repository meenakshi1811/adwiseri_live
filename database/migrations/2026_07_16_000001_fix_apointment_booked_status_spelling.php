<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('applications')
            ->where('application_status', 'Apointment Booked')
            ->update(['application_status' => 'Appointment Booked']);

        if (Schema::hasTable('application_status_tracks')) {
            DB::table('application_status_tracks')
                ->where('status', 'Apointment Booked')
                ->update(['status' => 'Appointment Booked']);
        }
    }

    public function down(): void
    {
        DB::table('applications')
            ->where('application_status', 'Appointment Booked')
            ->update(['application_status' => 'Apointment Booked']);

        if (Schema::hasTable('application_status_tracks')) {
            DB::table('application_status_tracks')
                ->where('status', 'Appointment Booked')
                ->update(['status' => 'Apointment Booked']);
        }
    }
};
