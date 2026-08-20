<?php

namespace App\Console\Commands;

use App\Services\ScheduledReportService;
use Illuminate\Console\Command;

class SendTestScheduledReportEmail extends Command
{
    protected $signature = 'reports:send-test
                            {--to= : Recipient email address for the test message}
                            {--user= : Subscriber user ID whose report settings should be used}';

    protected $description = 'Send a test scheduled report notification email (with PDF attachment and download link)';

    public function handle(ScheduledReportService $scheduledReportService): int
    {
        $recipient = trim((string) $this->option('to'));

        if ($recipient === '') {
            $this->error('Please pass your email with --to=you@example.com');

            return self::FAILURE;
        }

        $userId = $this->option('user');
        $userId = $userId !== null && $userId !== '' ? (int) $userId : null;

        $this->line('Mailer : ' . config('mail.default'));
        $this->line('From   : ' . config('mail.from.address'));
        $this->line('To     : ' . $recipient);
        if ($userId) {
            $this->line('User   : ' . $userId);
        }
        $this->newLine();

        $result = $scheduledReportService->sendTestEmail($recipient, $userId);

        if (in_array($result['status'], ['sent', 'partial'], true)) {
            $this->info($result['message']);
            if (!empty($result['file'])) {
                $this->line('PDF    : storage/app/reports/' . $result['file']);
            }

            return self::SUCCESS;
        }

        $this->error($result['message']);
        if (config('mail.default') === 'log') {
            $this->line('Tip: set MAIL_MAILER=smtp in .env to deliver emails to a real mailbox.');
        }

        return self::FAILURE;
    }
}
