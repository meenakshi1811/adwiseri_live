<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Documents Reminder emails: "Kindly send below listed Documents" -> "below listed documents" in the stored
     * default and in subscribers' saved copies of the template.
     */
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        DB::table('email_templates')
            ->where('template_key', 'document_reminder')
            ->update([
                'body' => DB::raw("REPLACE(body, 'Kindly send below listed Documents', 'Kindly send below listed documents')"),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        DB::table('email_templates')
            ->where('template_key', 'document_reminder')
            ->update([
                'body' => DB::raw("REPLACE(body, 'Kindly send below listed documents', 'Kindly send below listed Documents')"),
                'updated_at' => now(),
            ]);
    }
};
