<?php

namespace App\Services;

use App\Jobs\ProcessEmailBroadcastJob;
use App\Models\Associate;
use App\Models\Clients;
use App\Models\Email_broadcasts;
use App\Models\User;
use App\Support\EmailAddress;
use Illuminate\Support\Facades\Log;

class EmailBroadcastService
{
    public function __construct(
        private readonly OfferBenefitService $offerBenefitService
    ) {
    }
    private const STAFF_GROUP_DESIGNATIONS = [
        'branch_manager' => ['Branch Manager'],
        'advisors' => ['Consultant/Advisor'],
        'sales_team' => ['Sales Team Member'],
        'support_team' => ['Support Team Member'],
        'hr_accountant' => ['HR Executive', 'Accounts Team Member'],
    ];

    /**
     * Team keys ("advisors", "sales_team", ...) a staff member with this designation belongs to.
     *
     * @return array<int, string>
     */
    public static function staffTeamsFor(?string $designation): array
    {
        $designation = strtolower(trim((string) $designation));

        return array_keys(array_filter(
            self::STAFF_GROUP_DESIGNATIONS,
            fn (array $designations) => in_array($designation, array_map('strtolower', $designations), true)
        ));
    }

    /**
     * Splits a pasted list of emails (separated by commas, semicolons, new lines or spaces; "Name <email>" is
     * fine too) into unique valid addresses and the entries that aren't valid addresses.
     *
     * @return array{valid: array<int, string>, invalid: array<int, string>}
     */
    public function parseEmailList(string $list): array
    {
        $valid = [];
        $invalid = [];

        foreach (preg_split('/[,;\r\n]+/', $list) as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            $candidates = preg_match('/<([^<>]+)>/', $entry, $match) ? [$match[1]] : preg_split('/\s+/', $entry);

            foreach ($candidates as $candidate) {
                $candidate = trim($candidate, " \t\"'<>");
                if ($candidate === '') {
                    continue;
                }

                if (EmailAddress::isValidRecipient($candidate)) {
                    $valid[strtolower($candidate)] ??= $candidate;
                } else {
                    $invalid[$candidate] = $candidate;
                }
            }
        }

        return ['valid' => array_values($valid), 'invalid' => array_values($invalid)];
    }

    /**
     * Today's "Marketing - Potential Clients" allowance for a subscriber (all their users together).
     *
     * @return array{limit:int,used:int,remaining:int}
     */
    public function marketingUsageToday(int $subscriberId): array
    {
        $limit = max(0, (int) config('mail.broadcast_marketing_daily_limit', 5000));
        $used = (int) Email_broadcasts::query()
            ->where('subscriber_id', $subscriberId)
            ->where('communicate_type', 'marketing')
            ->whereNotIn('status', ['failed', 'cancelled'])
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('total_recipients');

        return ['limit' => $limit, 'used' => $used, 'remaining' => max(0, $limit - $used)];
    }

    /**
     * Staff who can receive a broadcast: active accounts only (deactivated staff are left out).
     */
    private function activeStaffQuery(int $subscriberId)
    {
        return User::where('added_by', $subscriberId)
            ->where('user_type', 'User')
            ->where('status', 'true');
    }

    public function generateBroadcastId(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        do {
            $id = '';
            for ($i = 0; $i < 7; $i++) {
                $id .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (Email_broadcasts::where('broadcast_id', $id)->exists());

        return $id;
    }

    public function resolveStaffRecipients(int $subscriberId, array $recipients): array
    {
        $resolved = [];

        foreach ($recipients as $recipient) {
            if ($recipient === 'all') {
                $staffMembers = $this->activeStaffQuery($subscriberId)->get();

                foreach ($staffMembers as $staff) {
                    $this->addRecipient($resolved, $staff->email, $staff->name);
                }
                continue;
            }

            if (str_starts_with((string) $recipient, 'group:')) {
                $group = substr((string) $recipient, 6);
                $query = $this->activeStaffQuery($subscriberId);

                if ($group === 'all') {
                    $staffMembers = $query->get();
                } elseif (isset(self::STAFF_GROUP_DESIGNATIONS[$group])) {
                    $staffMembers = $query->whereIn('designation', self::STAFF_GROUP_DESIGNATIONS[$group])->get();
                } else {
                    continue;
                }

                foreach ($staffMembers as $staff) {
                    $this->addRecipient($resolved, $staff->email, $staff->name);
                }
                continue;
            }

            $staff = $this->activeStaffQuery($subscriberId)
                ->where('id', $recipient)
                ->first();

            if ($staff) {
                $this->addRecipient($resolved, $staff->email, $staff->name);
            }
        }

        return array_values($resolved);
    }

    public function resolveClientRecipients(int $subscriberId, ?int $staffUserId, array $recipients): array
    {
        $resolved = [];

        foreach ($recipients as $recipient) {
            if ($recipient === 'all') {
                $query = Clients::where('subscriber_id', $subscriberId);
                if ($staffUserId) {
                    $query->where('user_id', $staffUserId);
                }

                foreach ($query->get() as $client) {
                    $this->addRecipient($resolved, $client->email, $client->name);
                }
                continue;
            }

            $query = Clients::where('id', $recipient)->where('subscriber_id', $subscriberId);
            if ($staffUserId) {
                $query->where('user_id', $staffUserId);
            }

            $client = $query->first();
            if ($client) {
                $this->addRecipient($resolved, $client->email, $client->name);
            }
        }

        return array_values($resolved);
    }

    /**
     * "External" picks: clients ("all" or client ids) and, when allowed, associates ("associate:all" or
     * "associate:{id}"). Someone in both lists is emailed once.
     */
    public function resolveExternalRecipients(int $subscriberId, ?int $staffUserId, array $recipients, bool $includeAssociates): array
    {
        $clientPicks = [];
        $associatePicks = [];

        foreach ($recipients as $recipient) {
            if (str_starts_with((string) $recipient, 'associate:')) {
                $associatePicks[] = substr((string) $recipient, 10);
            } else {
                $clientPicks[] = $recipient;
            }
        }

        $resolved = [];
        $picked = $this->resolveClientRecipients($subscriberId, $staffUserId, $clientPicks);
        if ($includeAssociates) {
            $picked = array_merge($picked, $this->resolveAssociateRecipients($subscriberId, $associatePicks));
        }

        foreach ($picked as $recipient) {
            $this->addRecipient($resolved, $recipient['email'], $recipient['name']);
        }

        return array_values($resolved);
    }

    public function resolveAssociateRecipients(int $subscriberId, array $recipients): array
    {
        $resolved = [];

        foreach ($recipients as $recipient) {
            if ($recipient === 'all') {
                foreach (Associate::where('added_by', $subscriberId)->get() as $associate) {
                    $this->addRecipient($resolved, $associate->email, $associate->name);
                }
                continue;
            }

            $associate = Associate::where('id', $recipient)->where('added_by', $subscriberId)->first();
            if ($associate) {
                $this->addRecipient($resolved, $associate->email, $associate->name);
            }
        }

        return array_values($resolved);
    }

    public function resolveSubscriberRecipients(array $recipients): array
    {
        $resolved = [];

        foreach ($recipients as $recipient) {
            if ($recipient === 'all') {
                foreach (User::where('user_type', 'Subscriber')->get() as $subscriber) {
                    $this->addRecipient($resolved, $subscriber->email, $subscriber->name);
                }
                continue;
            }

            $subscriber = User::where('id', $recipient)->where('user_type', 'Subscriber')->first();
            if ($subscriber) {
                $this->addRecipient($resolved, $subscriber->email, $subscriber->name);
            }
        }

        return array_values($resolved);
    }

    public function queueBroadcast(
        User $sender,
        string $communicateType,
        string $subject,
        string $body,
        array $recipients,
        ?int $subscriberId = null,
        array $recipientLabels = []
    ): array {
        $senderEmail = trim((string) ($sender->email ?? ''));
        $senderName = trim((string) ($sender->name ?? ''));

        if ($subscriberId) {
            $subscriber = User::find($subscriberId);
            $subscriberEmail = trim((string) ($subscriber->email ?? ''));
            if ($subscriberEmail === '') {
                return [
                    'queued' => false,
                    'error' => 'The subscriber account does not have an email address configured in Profile.',
                ];
            }
        } elseif ($senderEmail === '') {
            return [
                'queued' => false,
                'error' => 'Your account does not have an email address configured.',
            ];
        }

        if (count($recipients) === 0) {
            return [
                'queued' => false,
                'error' => 'No valid recipients with email addresses were found.',
            ];
        }

        $maxRecipients = max(0, (int) config('mail.broadcast_max_recipients', 0));
        if ($maxRecipients > 0 && count($recipients) > $maxRecipients) {
            return [
                'queued' => false,
                'error' => 'This broadcast exceeds the maximum of '
                    . number_format($maxRecipients)
                    . ' recipients. Please reduce your selection or split it into smaller broadcasts.',
            ];
        }

        if ($subscriberId) {
            if (!$this->offerBenefitService->canSendEmailBroadcast($sender, count($recipients))) {
                return [
                    'queued' => false,
                    'error' => $this->offerBenefitService->emailBroadcastLimitExceededMessage($sender, count($recipients)),
                ];
            }
        }

        if (config('queue.default') === 'sync') {
            Log::warning('Email broadcast queued while QUEUE_CONNECTION=sync. Configure a real queue driver and run queue:work for large broadcasts.');
        }

        $broadcast = Email_broadcasts::create([
            'broadcast_id' => $this->generateBroadcastId(),
            'subscriber_id' => $subscriberId,
            'user_id' => $sender->id,
            'sender_name' => $senderName,
            'sender_email' => $senderEmail,
            'communicate_type' => $communicateType,
            'subject' => $subject,
            'body' => $body,
            'recipient_labels' => $recipientLabels,
            'recipient_payload' => $recipients,
            'total_recipients' => count($recipients),
            'sent_count' => 0,
            'failed_count' => 0,
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        ProcessEmailBroadcastJob::dispatch($broadcast->id);

        return [
            'queued' => true,
            'broadcast_id' => $broadcast->broadcast_id,
            'total_recipients' => count($recipients),
        ];
    }

    private function addRecipient(array &$resolved, ?string $email, ?string $name): void
    {
        $email = trim((string) $email);
        if ($email === '') {
            return;
        }

        $key = strtolower($email);
        if (isset($resolved[$key])) {
            return;
        }

        $resolved[$key] = [
            'email' => $email,
            'name' => trim((string) $name),
        ];
    }
}
