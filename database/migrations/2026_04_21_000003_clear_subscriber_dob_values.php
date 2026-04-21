<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'dob')) {
            return;
        }

        DB::table('users')
            ->where('user_type', 'Subscriber')
            ->update(['dob' => null]);
    }

    public function down(): void
    {
        // Intentionally left blank because prior DOB values for subscribers cannot be restored.
    }
};
