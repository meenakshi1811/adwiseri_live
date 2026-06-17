<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->where('audience', 'admin')
            ->where('template_key', 'support_ticket_notification_email')
            ->update([
                'subject' => 'New Support Ticket Raised ({{ticket_id}})',
                'body' => '<p>A new support ticket has been raised.</p><p>Ticket Raiser: {{ticket_raiser}}</p><p>Ticket ID: {{ticket_id}}</p><p>Department: {{department}}</p><p>Issue: {{issue}}</p>',
            ]);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->where('audience', 'admin')
            ->where('template_key', 'support_ticket_notification_email')
            ->update([
                'subject' => 'New Support Ticket Raised ({{ticket_id}})',
                'body' => '<p>A new support ticket has been raised.</p><p>Ticket ID: {{ticket_id}}</p><p>Issue: {{issue}}</p>',
            ]);
    }
};
