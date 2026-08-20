<?php

namespace App\Console\Commands;

use App\Services\OperationalNotificationService;
use Illuminate\Console\Command;

class SendOutstandingPaymentNotifications extends Command
{
    protected $signature = 'notifications:outstanding-closed-apps';

    protected $description = 'Send weekly Monday 08:00 local-time outstanding payment digests for closed applications to subscribers.';

    public function handle(OperationalNotificationService $operationalNotifications)
    {
        $sent = $operationalNotifications->dispatchWeeklyOutstandingDigests();
        $this->info('Outstanding payment digests sent: ' . $sent);

        return Command::SUCCESS;
    }
}
