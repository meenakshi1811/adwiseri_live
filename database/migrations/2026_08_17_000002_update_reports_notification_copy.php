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

        $signature = '<p style="margin:16px 0 0 0;">Sincerely,<br><strong>Adwiseri</strong></p>';
        $body = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:100%;">'
            . '<tr><td style="padding:0;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">'
            . '<p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;"><strong>Hello {{name}},</strong></p>'
            . '</td></tr>'
            . '<tr><td style="padding:0;word-wrap:break-word;overflow-wrap:break-word;word-break:break-word;">'
            . '<p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;">Your {{consultancy_name}}\'s Report(s) are ready.</p>'
            . '<p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#1f2937;">Please find attached file or download via link given in this email.</p>'
            . '</td></tr>'
            . '<tr><td style="padding:0;">' . $signature . '</td></tr>'
            . '</table>';

        DB::table('email_templates')
            ->whereNull('owner_user_id')
            ->where('audience', 'subscriber')
            ->where('template_key', 'reports')
            ->update([
                'body' => $body,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // No rollback — prior template versions remain acceptable.
    }
};
