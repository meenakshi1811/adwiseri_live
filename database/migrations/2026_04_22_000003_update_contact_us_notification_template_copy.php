<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private string $newSubject = 'New Contact Us Submission from {{name}}';

    private string $newBody = '<p style="margin-bottom:12px;">A new Contact Us message has been submitted.</p><p style="margin-bottom:6px;"><strong>Name:</strong> {{name}}</p><p style="margin-bottom:6px;"><strong>Email:</strong> {{email}}</p><p style="margin-bottom:6px;"><strong>Phone:</strong> {{phone}}</p><p style="margin-bottom:6px;"><strong>Country:</strong> {{country}}</p><p style="margin-bottom:12px;"><strong>City:</strong> {{city}}</p><p style="margin-bottom:0;"><strong>Message:</strong><br>{{message}}</p>';

    public function up(): void
    {
        DB::table('email_templates')
            ->where('audience', 'admin')
            ->where('template_key', 'contact_us_notification_email')
            ->whereNull('owner_user_id')
            ->update([
                'subject' => $this->newSubject,
                'body' => $this->newBody,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->where('audience', 'admin')
            ->where('template_key', 'contact_us_notification_email')
            ->whereNull('owner_user_id')
            ->update([
                'subject' => 'New Message from adwiseri.com (Contact Us)',
                'body' => '<p>New contact form message received from {{name}} ({{email}}).</p><p>{{message}}</p>',
                'updated_at' => now(),
            ]);
    }
};
