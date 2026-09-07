<?php

namespace App\Services;

use App\Models\Email_broadcasts;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EmailBroadcastLogService
{
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

    private function formatLogRow(Email_broadcasts $broadcast, bool $includeSubId = false): array
    {
        $eventAt = $broadcast->queued_at ?? $broadcast->created_at;
        $parsed = $eventAt instanceof Carbon ? $eventAt : Carbon::parse((string) $eventAt);

        $row = [
            'id' => $broadcast->broadcast_id,
            'broadcast_name' => trim((string) $broadcast->subject) !== '' ? $broadcast->subject : '-',
            'recipients' => (int) $broadcast->total_recipients,
            'emails_sent' => (int) $broadcast->sent_count,
            'datetime' => $parsed->format('d-m-Y H:i:s'),
            'created_at' => $parsed->toIso8601String(),
        ];

        if ($includeSubId) {
            $row['sub_id'] = $broadcast->subscriber_id ? (string) $broadcast->subscriber_id : '-';
        }

        return $row;
    }
}
