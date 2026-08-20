<?php

namespace App\Console\Commands;

use App\Models\Internal_Invoices;
use App\Services\InvoiceMailService;
use Illuminate\Console\Command;

class SendTestInvoiceEmail extends Command
{
    protected $signature = 'invoice:send-test
                            {invoice : Invoice ID or invoice number}
                            {--to= : Override recipient email for testing}';

    protected $description = 'Send a test invoice email (with PDF) for an existing invoice';

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

        $config = $invoiceMailService->mailConfigSummary();
        $this->line('Mailer : ' . $config['mailer']);
        $this->line('From   : ' . $config['from']);
        $this->line('Bcc    : ' . (empty($config['bcc']) ? 'none' : implode(', ', $config['bcc'])));

        $override = $this->option('to');
        $recipient = $invoiceMailService->recipientEmail($invoice, $override);
        $this->line('To     : ' . ($recipient ?: '(invalid / missing)'));
        $this->newLine();

        $result = $invoiceMailService->send($invoice, null, $override);

        if ($result['success']) {
            $this->info($result['message']);

            return self::SUCCESS;
        }

        $this->error($result['message']);
        $this->line('Tip: set MAIL_MAILER=log in .env to capture emails in storage/logs/laravel.log while testing locally.');

        return self::FAILURE;
    }
}
