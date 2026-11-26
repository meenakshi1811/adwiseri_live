<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\User;

class EmailTemplateService
{
    /**
     * Emails adwiseri itself sends: to subscribers about their own account (renewal and win-back
     * reminders, termination, wallet alerts, automatic report emails) and to newsletter sign-ups.
     * They are adwiseri's messages, so only the admin edits them. They used to be listed under the
     * subscriber's templates, where a subscriber could rewrite (or blank) the reminders adwiseri
     * sends them, while the admin could not edit them at all.
     */
    public const PLATFORM_TEMPLATE_KEYS = [
        'newsletter',
        'subscription_expiry_reminder',
        'subscription_winback_reminder',
        'subscription_winback_final_reminder',
        'subscription_termination',
        'wallet_credit_alert',
        'wallet_debit_alert',
        'reports',
    ];

    public const ADMIN_TEMPLATE_KEYS = [
        'otp_email',
        'forgot_password_email',
        'welcome_email_admin_to_subscriber',
        'demo_request_notification_email',
        'contact_us_notification_email',
        'support_ticket_notification_email',
        'newsletter',
        'subscription_expiry_reminder',
        'subscription_winback_reminder',
        'subscription_winback_final_reminder',
        'subscription_termination',
        'wallet_credit_alert',
        'wallet_debit_alert',
        'reports',
    ];

    /** Emails a subscriber sends to their own clients and staff, which they may reword. */
    public const SUBSCRIBER_TEMPLATE_KEYS = [
        'payment_reminder',
        'document_reminder',
        'application_reminder',
        'other',
    ];

    public static function isPlatformTemplate(string $templateKey): bool
    {
        return in_array(strtolower(trim($templateKey)), self::PLATFORM_TEMPLATE_KEYS, true);
    }

    /** adwiseri's own version of a platform email (the admin's edit, else the default). */
    public function platformTemplate(string $templateKey): ?EmailTemplate
    {
        return EmailTemplate::whereNull('owner_user_id')
            ->where('template_key', $this->normalizeTemplateKey($templateKey))
            ->orderByRaw("CASE WHEN audience = 'admin' THEN 0 ELSE 1 END")
            ->first();
    }

    public function getTemplateForUser(?User $owner, string $audience, string $templateKey): ?EmailTemplate
    {
        $templateKey = $this->normalizeTemplateKey($templateKey);
        if (self::isPlatformTemplate($templateKey)) {
            // Never a subscriber's copy: these are adwiseri's own emails.
            return $this->platformTemplate($templateKey);
        }
        $ownerId = $owner?->id;

        if ($ownerId) {
            $custom = EmailTemplate::where('owner_user_id', $ownerId)
                ->where('audience', $audience)
                ->where('template_key', $templateKey)
                ->first();

            if ($custom && !empty(trim((string) $custom->body))) {
                return $custom;
            }
        }

        return EmailTemplate::whereNull('owner_user_id')
            ->where('audience', $audience)
            ->where('template_key', $templateKey)
            ->first();
    }

    public function getTemplatesForSettings(?User $owner): array
    {
        $ownerId = $owner?->id;

        $defaults = EmailTemplate::whereNull('owner_user_id')->get()->keyBy(function ($row) {
            return $row->audience . '::' . $row->template_key;
        });

        $custom = collect();
        if ($ownerId) {
            $custom = EmailTemplate::where('owner_user_id', $ownerId)->get()->keyBy(function ($row) {
                return $row->audience . '::' . $row->template_key;
            });
        }

        $merged = $defaults->merge($custom);
        $isAdminKey = fn ($t) => in_array($this->normalizeTemplateKey((string) $t->template_key), self::ADMIN_TEMPLATE_KEYS, true);

        return [
            // adwiseri's templates (its own defaults only), whatever audience older rows were saved with.
            'admin' => $defaults->filter($isAdminKey)->values(),
            // The subscriber's templates: their copy when they have one, else the default.
            'subscriber' => $merged->filter(fn ($t) => $t->audience === 'subscriber' && !$isAdminKey($t))->values(),
        ];
    }


    public function resolveTemplateOwner($data): ?User
    {
        $payload = is_array($data) ? $data : (array) $data;

        if (!empty($payload['subscriber_id'])) {
            $subscriber = User::find($payload['subscriber_id']);
            if ($subscriber) {
                return $subscriber;
            }
        }

        if (!empty($payload['email'])) {
            $recipient = User::where('email', $payload['email'])->first();
            if ($recipient) {
                if (strtolower((string) $recipient->user_type) === 'admin') {
                    return $recipient;
                }

                if (!empty($recipient->added_by)) {
                    $subscriber = User::find($recipient->added_by);
                    if ($subscriber) {
                        return $subscriber;
                    }
                }

                return $recipient;
            }
        }

        return null;
    }

   public function saveTemplate(User $owner, array $payload): ?EmailTemplate
    {
        $audience = strtolower(trim((string) $payload['audience']));
        $templateKey = $this->normalizeTemplateKey((string) $payload['template_key']);

        if ($owner->user_type === 'admin') {
            // adwiseri's own (default) row only; it used to match the first row with this key, which
            // could be a subscriber's copy.
            $template = EmailTemplate::whereNull('owner_user_id')
                ->where('template_key', $templateKey)
                ->orderByRaw("CASE WHEN audience = 'admin' THEN 0 ELSE 1 END")
                ->first();


            // Only update if template exists
            if ($template) {
                $template->update([
                    'template_name' => trim((string) $payload['template_name']),
                    'custom_name' => $payload['custom_name'] ?? null,
                    'subject' => $payload['subject'] ?? null,
                    'body' => $payload['body'] ?? null,
                ]);

                return $template;
            }

            // Do NOT create new record for admin
            return null;
        }

        // Subscribers may not override adwiseri's own emails.
        if (in_array($templateKey, self::ADMIN_TEMPLATE_KEYS, true)) {
            return null;
        }

        // For non-admin users allow create or update
        return EmailTemplate::updateOrCreate(
            [
                'owner_user_id' => $owner->id,
                'audience' => $audience,
                'template_key' => $templateKey,
            ],
            [
                'template_name' => trim((string) $payload['template_name']),
                'custom_name' => $payload['custom_name'] ?? null,
                'subject' => $payload['subject'] ?? null,
                'body' => $payload['body'] ?? null,
            ]
        );
    }
    private function normalizeTemplateKey(string $templateKey): string
    {
        return strtolower(trim($templateKey));
    }
}
