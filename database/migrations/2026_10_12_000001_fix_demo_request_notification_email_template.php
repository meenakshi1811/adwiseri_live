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

        $body = '<p style="margin:0 0 12px 0;">A new demo request was submitted on adwiseri.com.</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Name:</strong> {{name}}</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Email:</strong> {{email}}</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Phone:</strong> {{phone}}</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Country:</strong> {{country}}</p>'
            . '<p style="margin:0 0 6px 0;"><strong>City:</strong> {{city}}</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Company:</strong> {{company_name}}</p>'
            . '<p style="margin:0 0 6px 0;"><strong>Job title:</strong> {{job_title}}</p>'
            . '<p style="margin:0;"><strong>How they heard about us:</strong> {{how_did_hear}}</p>';

        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->where('audience', 'admin')
            ->where('template_key', 'demo_request_notification_email')
            ->update([
                'subject' => 'Demo Request from adwiseri.com — {{name}}',
                'body' => $body,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // No rollback — prior template versions remain acceptable.
    }
};
