<?php

namespace App\Console\Commands;

use App\Services\EmailAutoReplyService;
use App\Support\EmailAddress;
use Illuminate\Console\Command;

class ProcessMailboxAutoReply extends Command
{
    protected $signature = 'mailbox:process-auto-reply {mailbox : Mailbox that received the email, e.g. hello@adwiseri.com}';

    protected $description = 'Process a piped inbound email and send an auto-reply to external senders only.';

    public function handle(EmailAutoReplyService $autoReplyService): int
    {
        $mailbox = EmailAddress::normalize((string) $this->argument('mailbox'));

        if ($autoReplyService->mailboxConfig($mailbox) === null) {
            $this->error('Unknown mailbox: ' . $mailbox);

            return self::FAILURE;
        }

        $rawMessage = stream_get_contents(STDIN) ?: '';

        if (trim($rawMessage) === '') {
            $this->error('No email content received on STDIN.');

            return self::FAILURE;
        }

        $parsed = $autoReplyService->parseIncomingMessage($rawMessage);

        if (!$autoReplyService->shouldSendAutoReply($mailbox, $parsed)) {
            $this->line('Auto-reply skipped for ' . ($parsed['from'] ?: 'unknown sender') . '.');

            return self::SUCCESS;
        }

        if ($autoReplyService->sendAutoReply($mailbox, $parsed)) {
            $this->info('Auto-reply sent to ' . $parsed['from'] . ' from ' . $mailbox . '.');

            return self::SUCCESS;
        }

        $this->error('Failed to send auto-reply to ' . ($parsed['from'] ?: 'unknown sender') . '.');

        return self::FAILURE;
    }
}
