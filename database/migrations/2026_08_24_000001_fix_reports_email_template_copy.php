<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->where('audience', 'subscriber')
            ->where('template_key', 'reports')
            ->where('body', 'like', '%Please find the attached file%')
            ->update([
                'body' => DB::raw("REPLACE(body, 'Please find the attached file or use the secure download link below.', 'Please find attached file or use the secure download link below.')"),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->where('audience', 'subscriber')
            ->where('template_key', 'reports')
            ->where('body', 'like', '%Please find attached file or use the secure download link below.%')
            ->update([
                'body' => DB::raw("REPLACE(body, 'Please find attached file or use the secure download link below.', 'Please find the attached file or use the secure download link below.')"),
                'updated_at' => now(),
            ]);
    }
};
