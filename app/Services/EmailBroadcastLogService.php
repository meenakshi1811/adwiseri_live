<?php

namespace App\Services;

use App\Models\Email_broadcasts;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EmailBroadcastLogService
{
    private const RECIPIENT_PREVIEW_LIMIT = 10;

    private const ALL_RECIPIENTS_LABELS = [
        'internal' => 'All Staff',
        'external' => 'All Clients',
        'subscribers' => 'All Subscribers',
    ];

    private const STAFF_GROUP_LABELS = [
        'group:all' => 'All Staff',
        'group:branch_manager' => 'Branch Manager',
        'group:advisors' => 'Advisors',
        'group:sales_team' => 'Sales Team',
        'group:support_team' => 'Support Team',
        'group:hr_accountant' => 'HR / Accountant',
    ];

    public function getSubscriberLogs(int $subscriberId): Collection
    {
        if ($subscriberId <= 0) {
            return collect();
        }

        return Email_broadcasts::query()
            ->where('subscriber_id', $subscriberId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Email_broadcasts $broadcast) => $this->formatLogRow($broadcast));
    }

    public function getAdminLogs(?int $subscriberId = null): Collection
    {
        $query = Email_broadcasts::query()->orderByDesc('created_at');

        if ($subscriberId) {
            $query->where('subscriber_id', $subscriberId);
        }

        return $query->get()->map(fn (Email_broadcasts $broadcast) => $this->formatLogRow($broadcast, true));
    }

    /**
     * Full recipient list of one broadcast. Pass the subscriber id to limit the lookup to that
     * subscriber's broadcasts; null (admin) looks across all of them.
     */
    public function getBroadcastRecipients(string $broadcastId, ?int $subscriberId = null): ?array
    {
        if ($subscriberId !== null && $subscriberId <= 0) {
            return null;
        }

        $query = Email_broadcasts::query()->where('broadcast_id', $broadcastId);

        if ($subscriberId !== null) {
            $query->where('subscriber_id', $subscriberId);
        }

        $broadcast = $query->first();

        if (!$broadcast) {
            return null;
        }

        return [
            'id' => $broadcast->broadcast_id,
            'recipient_groups' => $this->recipientGroups($broadcast),
            'recipients' => $this->recipientList($broadcast),
        ];
    }

    private function formatLogRow(Email_broadcasts $broadcast, bool $includeSubId = false): array
    {
        $eventAt = $broadcast->queued_at ?? $broadcast->created_at;
        $parsed = $eventAt instanceof Carbon ? $eventAt : Carbon::parse((string) $eventAt);

        $row = [
            'id' => $broadcast->broadcast_id,
            'broadcast_name' => trim((string) $broadcast->subject) !== '' ? $broadcast->subject : '-',
            'recipients' => (int) $broadcast->total_recipients,
            'recipient_groups' => $this->recipientGroups($broadcast),
            'recipient_preview' => array_slice($this->recipientList($broadcast), 0, self::RECIPIENT_PREVIEW_LIMIT),
            'emails_sent' => (int) $broadcast->sent_count,
            'sent_by' => $this->sentByLabel($broadcast),
            'datetime' => $parsed->format('d-m-Y H:i:s'),
            'created_at' => $parsed->toIso8601String(),
        ];

        if ($includeSubId) {
            $row['sub_id'] = $broadcast->subscriber_id ? (string) $broadcast->subscriber_id : '-';
        }

        return $row;
    }

    /**
     * "SUB" when the subscriber sent the broadcast themselves, otherwise "Name (user id)".
     */
    private function sentByLabel(Email_broadcasts $broadcast): string
    {
        if ($broadcast->subscriber_id && (int) $broadcast->user_id === (int) $broadcast->subscriber_id) {
            return 'SUB';
        }

        $name = trim((string) $broadcast->sender_name);

        return ($name !== '' ? $name : 'User') . ' (' . $broadcast->user_id . ')';
    }

    /**
     * Names of the "all" / group options picked when the broadcast was sent (individual picks are left out).
     *
     * @return array<int, string>
     */
    private function recipientGroups(Email_broadcasts $broadcast): array
    {
        $groups = [];

        foreach ((array) ($broadcast->recipient_labels ?? []) as $label) {
            $label = (string) $label;

            if ($label === 'all') {
                $groups[] = self::ALL_RECIPIENTS_LABELS[$broadcast->communicate_type] ?? 'All';
            } elseif ($label === 'associate:all') {
                $groups[] = 'All Associates';
            } elseif (isset(self::STAFF_GROUP_LABELS[$label])) {
                $groups[] = self::STAFF_GROUP_LABELS[$label];
            }
        }

        return array_values(array_unique($groups));
    }

    /**
     * @return array<int, array{name:string,email:string}>
     */
    private function recipientList(Email_broadcasts $broadcast): array
    {
        $recipients = [];

        foreach ((array) ($broadcast->recipient_payload ?? []) as $recipient) {
            if (!is_array($recipient)) {
                continue;
            }

            $email = trim((string) ($recipient['email'] ?? ''));

            if ($email === '') {
                continue;
            }

            $recipients[] = [
                'name' => trim((string) ($recipient['name'] ?? '')),
                'email' => $email,
            ];
        }

        return $recipients;
    }
}
