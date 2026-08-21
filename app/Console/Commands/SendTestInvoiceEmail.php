<?php

namespace App\Console\Commands;

use App\Models\Internal_Invoices;
use App\Services\InvoiceMailService;
use Illuminate\Console\Command;

class SendTestInvoiceEmail extends Command
{
    protected $signature = 'invoice:send-test
                            {invoice : Invoice ID or invoice number}
                            {--to= : Your email address (required for testing)}';

    protected $description = 'Send a test invoice email with PDF attachment to your inbox';

    public function handle(InvoiceMailService $invoiceMailService): int
    {
        $lookup = (string) $this->argument('invoice');
        $invoice = ctype_digit($lookup)
            ? Internal_Invoices::with('items')->find($lookup)
            : Internal_Invoices::with('items')->where('invoice_no', $lookup)->first();

        if (!$invoice) {
            $this->error('Invoice not found: ' . $lookup);

            return self::FAILURE;
        }

        $override = trim((string) $this->option('to'));
        if ($override === '' || !filter_var($override, FILTER_VALIDATE_EMAIL)) {
            $this->error('Provide your email address with --to=you@example.com');
            $this->line('');
            $this->line('Example:');
            $this->line('  php artisan invoice:send-test ' . $invoice->id . ' --to=you@example.com');
            $this->line('');
            $this->line('Use invoice ID or invoice number as the first argument.');

            return self::FAILURE;
        }

        $config = $invoiceMailService->mailConfigSummary();
        $this->line('Invoice : ' . ($invoice->invoice_no ?? $invoice->id));
        $this->line('Mailer  : ' . $config['mailer']);
        $this->line('From    : ' . $config['from']);
        $this->line('Bcc     : ' . (empty($config['bcc']) ? 'none' : implode(', ', $config['bcc'])));
        $this->line('To      : ' . $override);
        $this->newLine();

        if ($config['mailer'] === 'log') {
            $this->warn('MAIL_MAILER=log — email will be written to storage/logs/laravel.log, not delivered to your inbox.');
            $this->line('Set MAIL_MAILER=smtp in .env to receive the test email.');
            $this->newLine();
        }

        $result = $invoiceMailService->send($invoice, null, $override);

        if ($result['success']) {
            $this->info($result['message']);
            $this->line('Check your inbox (and spam folder) for the invoice email with PDF attachment.');

            return self::SUCCESS;
        }

        $this->error($result['message']);

        return self::FAILURE;
    }
}
