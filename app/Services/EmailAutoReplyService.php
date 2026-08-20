<?php

namespace App\Services;

use App\Mail\MailboxAutoReplyMail;
use App\Models\EmailAutoReplyLog;
use App\Support\EmailAddress;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailAutoReplyService
{
    public function isEnabled(): bool
    {
        return (bool) config('email_auto_replies.enabled', true);
    }

    public function mailboxConfig(string $mailbox): ?array
    {
        $mailbox = EmailAddress::normalize($mailbox);
        $mailboxes = config('email_auto_replies.mailboxes', []);

        if (!isset($mailboxes[$mailbox]) || !is_array($mailboxes[$mailbox])) {
            return null;
        }

        return $mailboxes[$mailbox];
    }

    public function parseIncomingMessage(string $rawMessage): array
    {
        $headers = $this->extractHeaders($rawMessage);

        return [
            'from' => EmailAddress::extractFromHeader($headers['from'] ?? ''),
            'message_id' => trim((string) ($headers['message-id'] ?? '')),
            'subject' => trim((string) ($headers['subject'] ?? '')),
            'auto_submitted' => strtolower(trim((string) ($headers['auto-submitted'] ?? ''))),
            'precedence' => strtolower(trim((string) ($headers['precedence'] ?? ''))),
            'x_auto_response_suppress' => strtolower(trim((string) ($headers['x-auto-response-suppress'] ?? ''))),
        ];
    }

    public function shouldSendAutoReply(string $mailbox, array $parsedMessage): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $mailbox = EmailAddress::normalize($mailbox);
        $config = $this->mailboxConfig($mailbox);

        if (!$config || empty($config['enabled'])) {
            return false;
        }

        $sender = EmailAddress::normalize($parsedMessage['from'] ?? '');

        if (!EmailAddress::isValidRecipient($sender)) {
            return false;
        }

        if (EmailAddress::isInternalDomain($sender)) {
            return false;
        }

        if ($this->isAutomatedSender($sender, $parsedMessage)) {
            return false;
        }

        if ($this->recentlyReplied($mailbox, $sender)) {
            return false;
        }

        $messageId = trim((string) ($parsedMessage['message_id'] ?? ''));
        if ($messageId !== '' && $this->alreadyRepliedToMessage($mailbox, $messageId)) {
            return false;
        }

        return true;
    }

    public function sendAutoReply(string $mailbox, array $parsedMessage): bool
    {
        $mailbox = EmailAddress::normalize($mailbox);
        $config = $this->mailboxConfig($mailbox);

        if (!$config || !$this->shouldSendAutoReply($mailbox, $parsedMessage)) {
            return false;
        }

        $sender = EmailAddress::normalize($parsedMessage['from'] ?? '');
        $subject = trim((string) ($config['subject'] ?? 'Thank you for contacting Adwiseri'));
        $body = trim((string) ($config['body'] ?? ''));
        $fromName = trim((string) ($config['from_name'] ?? 'Adwiseri'));

        if ($body === '') {
            return false;
        }

        try {
            Mail::to($sender)->send(new MailboxAutoReplyMail($mailbox, $fromName, $subject, $body));

            EmailAutoReplyLog::create([
                'mailbox' => $mailbox,
                'sender_email' => $sender,
                'incoming_message_id' => trim((string) ($parsedMessage['message_id'] ?? '')) ?: null,
                'sent_at' => now(),
            ]);

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    protected function isAutomatedSender(string $sender, array $parsedMessage): bool
    {
        $localPart = strstr($sender, '@', true) ?: '';

        if (in_array($localPart, ['mailer-daemon', 'postmaster', 'noreply', 'no-reply', 'donotreply', 'do-not-reply'], true)) {
            return true;
        }

        $autoSubmitted = (string) ($parsedMessage['auto_submitted'] ?? '');
        if ($autoSubmitted !== '' && $autoSubmitted !== 'no') {
            return true;
        }

        $precedence = (string) ($parsedMessage['precedence'] ?? '');
        if (in_array($precedence, ['bulk', 'junk', 'list'], true)) {
            return true;
        }

        $suppress = (string) ($parsedMessage['x_auto_response_suppress'] ?? '');
        if ($suppress !== '' && $suppress !== 'none') {
            return true;
        }

        return false;
    }

    protected function recentlyReplied(string $mailbox, string $sender): bool
    {
        $hours = max(1, (int) config('email_auto_replies.cooldown_hours', 24));

        return EmailAutoReplyLog::query()
            ->where('mailbox', EmailAddress::normalize($mailbox))
            ->where('sender_email', EmailAddress::normalize($sender))
            ->where('sent_at', '>=', now()->subHours($hours))
            ->exists();
    }

    protected function alreadyRepliedToMessage(string $mailbox, string $messageId): bool
    {
        return EmailAutoReplyLog::query()
            ->where('mailbox', EmailAddress::normalize($mailbox))
            ->where('incoming_message_id', $messageId)
            ->exists();
    }

    protected function extractHeaders(string $rawMessage): array
    {
        $headerSection = $rawMessage;

        if (preg_match("/\r?\n\r?\n/", $rawMessage, $matches, PREG_OFFSET_CAPTURE)) {
            $headerSection = substr($rawMessage, 0, $matches[0][1]);
        }

        $headers = [];
        $currentName = null;
        $currentValue = '';

        foreach (preg_split("/\r?\n/", $headerSection) as $line) {
            if ($line === '') {
                continue;
            }

            if (preg_match('/^\s+/', $line) && $currentName !== null) {
                $currentValue .= ' ' . trim($line);
                continue;
            }

            if ($currentName !== null) {
                $headers[$currentName] = trim($currentValue);
            }

            if (!preg_match('/^([^:]+):\s*(.*)$/', $line, $matches)) {
                $currentName = null;
                $currentValue = '';
                continue;
            }

            $currentName = strtolower(trim($matches[1]));
            $currentValue = $matches[2];
        }

        if ($currentName !== null) {
            $headers[$currentName] = trim($currentValue);
        }

        return $headers;
    }
}
