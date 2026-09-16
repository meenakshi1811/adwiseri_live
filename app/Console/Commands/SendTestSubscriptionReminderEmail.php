<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionRenewalReminder;
use App\Models\User;
use App\Support\EmailAddress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestSubscriptionReminderEmail extends Command
{
    protected $signature = 'subscription:send-reminder-test
                            {email : Recipient email for the demo}
                            {--type=all : pre_expiry, winback, winback_final, or all}
                            {--days= : Override days remaining (pre_expiry) or days expired (winback)}
                            {--subscriber= : Optional subscriber ID or email to use as sample data}';

    protected $description = 'Send a demo subscription renewal / win-back reminder email for screenshots';

    public function handle(): int
    {
        $recipient = trim((string) $this->argument('email'));
        if (!EmailAddress::isValidRecipient($recipient)) {
            $this->error('Please pass a valid recipient email.');

            return self::FAILURE;
        }

        $type = strtolower(trim((string) $this->option('type')));
        $allowed = ['pre_expiry', 'winback', 'winback_final', 'all'];
        if (!in_array($type, $allowed, true)) {
            $this->error('Invalid --type. Use pre_expiry, winback, winback_final, or all.');

            return self::FAILURE;
        }

        $subscriber = $this->resolveSampleSubscriber();
        $samples = $this->samplesForType($type);

        $this->line('Mailer : ' . config('mail.default'));
        $this->line('From   : ' . config('mail.from.address'));
        $this->line('To     : ' . $recipient);
        $this->line('Sample : ' . $subscriber->name . ' (#' . $subscriber->id . ')');
        $this->newLine();

        $sent = 0;
        foreach ($samples as $sample) {
            try {
                Mail::to($recipient)->send(new SubscriptionRenewalReminder(
                    $subscriber,
                    $sample['days'],
                    $sample['phase']
                ));
                $this->info('Sent: ' . $sample['label']);
                $sent++;
            } catch (\Throwable $e) {
                $this->error('Failed to send ' . $sample['label'] . ': ' . $e->getMessage());
                $this->line('Tip: set MAIL_MAILER=log in .env to capture emails in storage/logs/laravel.log while testing locally.');

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info($sent . ' demo reminder email(s) sent to ' . $recipient . '.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{phase: string, days: int, label: string}>
     */
    private function samplesForType(string $type): array
    {
        $daysOption = $this->option('days');
        $days = $daysOption !== null && $daysOption !== '' ? (int) $daysOption : null;

        $samples = [
            'pre_expiry' => [
                'phase' => SubscriptionRenewalReminder::PHASE_PRE_EXPIRY,
                'days' => $days ?? 30,
                'label' => 'Pre-expiry reminder (' . ($days ?? 30) . ' days left)',
            ],
            'winback' => [
                'phase' => SubscriptionRenewalReminder::PHASE_WINBACK,
                'days' => $days ?? 7,
                'label' => 'Weekly win-back reminder (' . ($days ?? 7) . ' days after expiry)',
            ],
            'winback_final' => [
                'phase' => SubscriptionRenewalReminder::PHASE_WINBACK_FINAL,
                'days' => 30,
                'label' => 'Final win-back reminder (day 30, wallet credit expiry)',
            ],
        ];

        if ($type === 'all') {
            return array_values($samples);
        }

        return [$samples[$type]];
    }

    private function resolveSampleSubscriber(): User
    {
        $lookup = trim((string) $this->option('subscriber'));
        if ($lookup !== '') {
            $subscriber = $this->findSubscriber($lookup);
            if (!$subscriber) {
                $this->warn('Subscriber not found: ' . $lookup . '. Using a demo subscriber instead.');
            } else {
                return $subscriber;
            }
        }

        $subscriber = User::where('user_type', 'Subscriber')->orderBy('id')->first();
        if ($subscriber) {
            return $subscriber;
        }

        $demo = new User();
        $demo->id = 1;
        $demo->name = 'Demo Subscriber';
        $demo->email = (string) $this->argument('email');
        $demo->wallet = 150;

        return $demo;
    }

    private function findSubscriber(string $lookup): ?User
    {
        if (ctype_digit($lookup)) {
            return User::where('id', (int) $lookup)
                ->where('user_type', 'Subscriber')
                ->first();
        }

        return User::where('email', $lookup)
            ->where('user_type', 'Subscriber')
            ->first();
    }
}
