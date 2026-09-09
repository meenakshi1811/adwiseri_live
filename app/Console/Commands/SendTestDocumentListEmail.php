<?php

namespace App\Console\Commands;

use App\Models\Applications;
use App\Models\User;
use App\Services\ApplicationDocumentListService;
use App\Services\DocumentChecklistMailService;
use Illuminate\Console\Command;

class SendTestDocumentListEmail extends Command
{
    protected $signature = 'document-list:send-test
                            {application : Application DB id or Application ID (e.g. 5HQZT31W)}
                            {--to= : Your email address (required)}
                            {--user= : Acting user id (defaults to the application subscriber)}
                            {--message= : Optional custom message for the email body}';

    protected $description = 'Send a test Documents Checklist email (same as client receives) to your inbox';

    public function handle(
        ApplicationDocumentListService $documentListService,
        DocumentChecklistMailService $mailService
    ): int {
        $lookup = trim((string) $this->argument('application'));
        $application = ctype_digit($lookup)
            ? Applications::with('client')->find($lookup)
            : Applications::with('client')->where('application_id', $lookup)->first();

        if (!$application) {
            $this->error('Application not found: ' . $lookup);
            $this->line('');
            $this->line('Examples:');
            $this->line('  php artisan document-list:send-test 42 --to=you@example.com');
            $this->line('  php artisan document-list:send-test 5HQZT31W --to=you@example.com');

            return self::FAILURE;
        }

        $recipient = trim((string) $this->option('to'));
        if ($recipient === '' || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('Provide your email address with --to=you@example.com');

            return self::FAILURE;
        }

        $actingUser = $this->resolveActingUser($application);
        if (!$actingUser) {
            $this->error('Unable to resolve an acting user for this application.');

            return self::FAILURE;
        }

        $diagnosis = $documentListService->diagnoseListAvailability($actingUser, $application);
        $this->printDiagnosis($application, $diagnosis);

        if (!$diagnosis['available']) {
            $this->newLine();
            $this->error('Documents checklist is not available for this application, so the email was not sent.');
            $this->line('Fix the country/category document list under Settings -> Countries & Visa Categories, then retry.');

            return self::FAILURE;
        }

        $this->line('Mailer : ' . config('mail.default'));
        $this->line('From   : ' . config('mail.from.address'));
        $this->line('To     : ' . $recipient);
        $this->newLine();

        if (config('mail.default') === 'log') {
            $this->warn('MAIL_MAILER=log — email will be written to storage/logs/laravel.log, not delivered to your inbox.');
            $this->line('Set MAIL_MAILER=smtp in .env to receive the test email.');
            $this->newLine();
        }

        $customMessage = trim((string) $this->option('message'));
        $result = $mailService->send(
            $application,
            $actingUser,
            null,
            $recipient,
            $customMessage !== '' ? $customMessage : null,
            false
        );

        if ($result['success']) {
            $this->info($result['message']);
            $this->line('Check your inbox (and spam folder) for the Documents Checklist email with PDF attachment.');

            return self::SUCCESS;
        }

        $this->error($result['message']);

        return self::FAILURE;
    }

    private function resolveActingUser(Applications $application): ?User
    {
        $userOption = trim((string) $this->option('user'));
        if ($userOption !== '' && ctype_digit($userOption)) {
            return User::find((int) $userOption);
        }

        return User::find($application->subscriber_id);
    }

    /**
     * @param  array<string, mixed>  $diagnosis
     */
    private function printDiagnosis(Applications $application, array $diagnosis): void
    {
        $this->line('Application : ' . ($application->application_id ?? $application->id)
            . ' (' . trim((string) ($application->application_name ?? '')) . ')');
        $this->line('Subscriber  : ' . ($diagnosis['subscriber_name'] ?? '—')
            . ' [ID ' . ($diagnosis['subscriber_id'] ?? '—') . ']');
        $this->line('Countries   : ' . $this->formatList($diagnosis['country_candidates'] ?? []));
        $this->line('Categories  : ' . $this->formatList($diagnosis['category_candidates'] ?? []));
        $this->line('Matched     : '
            . trim((string) (($diagnosis['matched_country'] ?? '—') . ' / ' . ($diagnosis['matched_category'] ?? '—'))));
        $this->line('Configured  : ' . $this->formatList($diagnosis['configured_combinations'] ?? []));
        $this->line('Settings    : ' . (!empty($diagnosis['settings_found']) ? 'found' : 'missing'));

        if (!empty($diagnosis['reason'])) {
            $this->warn('Issue       : ' . $diagnosis['reason']);
        } else {
            $this->info('Status      : Documents checklist is available.');
        }

        $this->newLine();
    }

    /**
     * @param  array<int, string>  $values
     */
    private function formatList(array $values): string
    {
        $values = array_values(array_filter(array_map('trim', $values)));

        return $values === [] ? '—' : implode(', ', $values);
    }
}
