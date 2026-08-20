<?php

namespace App\Services;

use App\Models\Internal_communications;
use App\Models\InternalCommunicationRead;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Collection;

class NotificationService
{
    public const ADMIN_TYPES = [
        'system_update' => 'System update / maintenance',
        'new_features' => 'New Feature(s)',
        'price_limits_change' => 'Price or feature limits change',
        'promo_offers' => 'Promo offers',
        'support_tickets' => 'Support tickets',
    ];

    public const SUBSCRIBER_TYPES = [
        'new_countries' => 'New Country(ies)',
        'new_categories' => 'New Category(ies)',
        'new_products' => 'New Product (Service)',
        'service_withdrawal' => 'Withdrawal of Service(s)',
        'service_fee_updated' => 'Service fee updated',
        'application_assigned' => 'Application assigned',
        'application_closure' => 'Application decision / closure',
        'outstanding_payments' => 'Outstanding payments (closed applications)',
        'full_payment_received' => 'Full payment received',
        'visa_rules' => 'Visa Rules changes',
        'eligibility_criteria' => 'Eligibility criteria changes',
        'task_reminders' => 'Task Reminders',
        'meeting_reminders' => 'Meeting Reminders',
        'deadline_reminders' => 'Deadline Reminders',
        'support_ticket_updates' => 'Support ticket responses & closures',
    ];

    public const AFFILIATE_TYPES = [
        'system_update' => 'System update / maintenance',
        'promo_offers' => 'Promo offers',
        'new_features' => 'New features',
        'support_ticket_updates' => 'Support ticket responses & closures',
    ];

    public function resolveAudience(User $user): string
    {
        $type = strtolower((string) $user->user_type);

        if ($type === 'admin') {
            return 'admin';
        }

        if ($type === 'affiliate') {
            return 'affiliate';
        }

        return 'subscriber';
    }

    public function typeDefinitionsForUser(User $user): array
    {
        $audience = $this->resolveAudience($user);

        if ($audience === 'admin') {
            return self::ADMIN_TYPES;
        }

        if ($audience === 'affiliate') {
            return self::AFFILIATE_TYPES;
        }

        return array_merge(self::ADMIN_TYPES, self::SUBSCRIBER_TYPES);
    }

    public static function adminSendableNotificationTypes(): array
    {
        return array_merge(self::ADMIN_TYPES, self::SUBSCRIBER_TYPES);
    }

    public static function isValidNotificationType(string $type): bool
    {
        return array_key_exists($type, self::adminSendableNotificationTypes())
            || array_key_exists($type, self::AFFILIATE_TYPES);
    }

    public static function notificationTypeLabel(string $type): string
    {
        $types = self::adminSendableNotificationTypes();

        return $types[$type] ?? $type;
    }

    public function defaultPreferencesForUser(User $user): array
    {
        $preferences = [];
        foreach (array_keys($this->typeDefinitionsForUser($user)) as $key) {
            $preferences[$key] = true;
        }

        return $preferences;
    }

    public function getPreferences(User $user): array
    {
        $audience = $this->resolveAudience($user);
        $defaults = $this->defaultPreferencesForUser($user);

        $record = UserNotificationPreference::where('user_id', $user->id)->first();
        if (!$record) {
            return $defaults;
        }

        $saved = is_array($record->preferences) ? $record->preferences : [];

        return array_merge($defaults, array_intersect_key($saved, $defaults));
    }

    public function savePreferences(User $user, array $input): UserNotificationPreference
    {
        $definitions = $this->typeDefinitionsForUser($user);
        $preferences = [];

        foreach (array_keys($definitions) as $key) {
            $preferences[$key] = !empty($input[$key]);
        }

        return UserNotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'audience' => $this->resolveAudience($user),
                'preferences' => $preferences,
            ]
        );
    }

    public function enabledTypes(User $user): array
    {
        return array_keys(array_filter($this->getPreferences($user)));
    }

    public function bellCount(User $user): int
    {
        $enabledTypes = $this->enabledTypes($user);
        if (empty($enabledTypes)) {
            return 0;
        }

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->whereIn('type', $enabledTypes)
            ->count();
    }

    public function envelopeCount(User $user): int
    {
        return $this->unreadMessagesForUser($user)->count();
    }

    public function messageStatusCountsForUser(User $user): array
    {
        $counts = [
            'unread' => 0,
            'read' => 0,
            'sent' => 0,
        ];

        foreach ($this->messagesVisibleToUser($user) as $message) {
            $status = $this->messageStatusForUser($user, $message);
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    public function unreadMessagesForUser(User $user): Collection
    {
        $readIds = InternalCommunicationRead::query()
            ->where('user_id', $user->id)
            ->pluck('communication_id')
            ->all();

        $query = Internal_communications::query()
            ->where('send_by', '!=', $user->id)
            ->when(!empty($readIds), function ($builder) use ($readIds) {
                $builder->whereNotIn('id', $readIds);
            })
            ->where(function ($builder) use ($user) {
                $builder->whereRaw('JSON_CONTAINS(send_to, ?)', [json_encode((int) $user->id)]);

                if (strtolower((string) $user->user_type) !== 'admin') {
                    $builder->orWhere('user_id', $user->id);
                }
            })
            ->orderByDesc('created_at');

        return $query->get()->filter(function ($message) use ($user) {
            return $this->userIsMessageRecipient($user, $message);
        })->values();
    }

    public function userIsMessageRecipient(User $user, Internal_communications $message): bool
    {
        $recipients = $this->decodeIdList($message->send_to);

        if (in_array((int) $user->id, $recipients, true)) {
            return true;
        }

        if (strtolower((string) $user->user_type) !== 'admin' && (int) $message->user_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    public function userSentMessage(User $user, Internal_communications $message): bool
    {
        $userId = (int) $user->id;

        if ((int) $message->send_by === $userId) {
            return true;
        }

        // Legacy rows may only have user_id populated for the sender.
        if ((int) $message->user_id === $userId && (int) $message->send_by === $userId) {
            return true;
        }

        return false;
    }

    public function markMessageRead(User $user, int $communicationId): void
    {
        $message = Internal_communications::find($communicationId);
        if (!$message) {
            return;
        }

        // Only recipients can mark a message as read; senders keep "Sent".
        if ($this->userSentMessage($user, $message)) {
            return;
        }

        if (!$this->userIsMessageRecipient($user, $message)) {
            return;
        }

        InternalCommunicationRead::updateOrCreate(
            [
                'user_id' => $user->id,
                'communication_id' => $communicationId,
            ],
            ['read_at' => now()]
        );
    }

    public function readCommunicationIdsForUser(User $user): array
    {
        return InternalCommunicationRead::query()
            ->where('user_id', $user->id)
            ->pluck('communication_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    public function isMessageUnreadForUser(User $user, Internal_communications $message): bool
    {
        if ($this->userSentMessage($user, $message)) {
            return false;
        }

        if (!$this->userIsMessageRecipient($user, $message)) {
            return false;
        }

        return !in_array((int) $message->id, $this->readCommunicationIdsForUser($user), true);
    }

    public function messageStatusForUser(User $user, Internal_communications $message): string
    {
        if ($this->userSentMessage($user, $message)) {
            return 'sent';
        }

        if ($this->userIsMessageRecipient($user, $message)) {
            return $this->isMessageUnreadForUser($user, $message) ? 'unread' : 'read';
        }

        return 'read';
    }

    public function messagesVisibleToUser(User $user): Collection
    {
        $userId = (int) $user->id;
        $isAdmin = strtolower((string) $user->user_type) === 'admin';

        return Internal_communications::query()
            ->where(function ($query) use ($userId, $isAdmin) {
                $query->where('send_by', $userId)
                    ->orWhereRaw('JSON_CONTAINS(send_to, ?)', [json_encode($userId)]);

                if (!$isAdmin) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->orderByDesc('created_at')
            ->get()
            ->filter(function ($message) use ($user) {
                return $this->userSentMessage($user, $message)
                    || $this->userIsMessageRecipient($user, $message);
            })
            ->values();
    }

    public function messageStatusBadgeHtml(User $user, Internal_communications $message): string
    {
        $status = $this->messageStatusForUser($user, $message);

        if ($status === 'unread') {
            return '<span class="comm-status-badge comm-status-unread"><span class="comm-status-dot" aria-hidden="true"></span><i class="fas fa-envelope"></i><strong>Unread</strong></span>';
        }

        if ($status === 'read') {
            return '<span class="comm-status-badge comm-status-read"><i class="fas fa-envelope-open"></i><strong>Read</strong></span>';
        }

        if ($status === 'sent') {
            return '<span class="comm-status-badge comm-status-sent"><i class="fas fa-paper-plane"></i><strong>Sent</strong></span>';
        }

        return '';
    }

    public function markAllMessagesRead(User $user): void
    {
        foreach ($this->unreadMessagesForUser($user) as $message) {
            $this->markMessageRead($user, (int) $message->id);
        }
    }

    public function markNotificationRead(User $user, int $notificationId): bool
    {
        $notification = UserNotification::query()
            ->where('user_id', $user->id)
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return false;
        }

        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
        }

        return true;
    }

    public function markAllNotificationsRead(User $user): int
    {
        $enabledTypes = $this->enabledTypes($user);

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->whereIn('type', $enabledTypes)
            ->update(['read_at' => now()]);
    }

    public function deleteNotification(User $user, int $notificationId): bool
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->where('id', $notificationId)
            ->delete() > 0;
    }

    public function deleteNotifications(User $user, array $notificationIds): int
    {
        $ids = array_values(array_filter(array_map('intval', $notificationIds)));

        if (empty($ids)) {
            return 0;
        }

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $ids)
            ->delete();
    }

    public function clearAllNotifications(User $user): int
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->delete();
    }

    public function notifyUser(User $user, string $type, string $title, ?string $body = null, ?string $link = null, array $meta = []): ?UserNotification
    {
        if (!self::isValidNotificationType($type)) {
            return null;
        }

        $preferences = $this->getPreferences($user);
        if (empty($preferences[$type])) {
            return null;
        }

        return UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'meta' => $meta,
        ]);
    }

    /**
     * @param  iterable<int, User>  $users
     */
    public function notifyUsers(iterable $users, string $type, string $title, ?string $body = null, ?string $link = null, array $meta = []): int
    {
        $count = 0;
        foreach ($users as $user) {
            if ($this->notifyUser($user, $type, $title, $body, $link, $meta)) {
                $count++;
            }
        }

        return $count;
    }

    public function notifyAudience(string $audience, string $type, string $title, ?string $body = null, ?string $link = null, array $meta = []): int
    {
        $query = User::query();

        if ($audience === 'admin') {
            $query->where('user_type', 'admin');
        } elseif ($audience === 'affiliate') {
            $query->where('user_type', 'Affiliate');
        } else {
            $query->whereIn('user_type', ['Subscriber', 'User']);
        }

        return $this->notifyUsers($query->get(), $type, $title, $body, $link, $meta);
    }

    public function notifyConsultancyUsers(User $subscriber, string $type, string $title, ?string $body = null, ?string $link = null, array $meta = []): int
    {
        $users = User::query()
            ->where(function ($query) use ($subscriber) {
                $query->where('id', $subscriber->id)
                    ->orWhere('added_by', $subscriber->id);
            })
            ->whereIn('user_type', ['Subscriber', 'User'])
            ->get();

        return $this->notifyUsers($users, $type, $title, $body, $link, $meta);
    }

    /**
     * Notify staff (Advisor / Counsellor) under a subscriber — excludes the subscriber owner.
     */
    public function notifyConsultancyStaff(User $subscriber, string $type, string $title, ?string $body = null, ?string $link = null, array $meta = []): int
    {
        $users = User::query()
            ->where('added_by', $subscriber->id)
            ->where('user_type', 'User')
            ->get();

        return $this->notifyUsers($users, $type, $title, $body, $link, $meta);
    }

    public function recentNotifications(User $user, int $limit = 50): Collection
    {
        $enabledTypes = $this->enabledTypes($user);

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereIn('type', $enabledTypes)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function notificationsRouteForUser(User $user): string
    {
        if (strtolower((string) $user->user_type) === 'admin') {
            return route('admin_notifications');
        }

        if (strtolower((string) $user->user_type) === 'affiliate') {
            return route('affiliate_notifications');
        }

        return route('notifications');
    }

    public function messagesRouteForUser(User $user): string
    {
        if (strtolower((string) $user->user_type) === 'admin') {
            return route('communication');
        }

        if (strtolower((string) $user->user_type) === 'affiliate') {
            return route('support_affiliate');
        }

        return route('communications');
    }

    protected function decodeIdList(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_map('intval', $decoded);
    }
}
