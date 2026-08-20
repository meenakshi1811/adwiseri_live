<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mailbox auto-replies (care@, hello@, etc.)
    |--------------------------------------------------------------------------
    |
    | Configure automatic replies for inbound platform mailboxes.
    | Wire each mailbox in WHW/cPanel via an email pipe/forwarder, e.g.:
    |   |/usr/bin/php /path/to/artisan mailbox:process-auto-reply hello@adwiseri.com
    |
    | Auto-replies are sent to external senders only (non-internal domains).
    |
    */

    'enabled' => env('MAILBOX_AUTO_REPLY_ENABLED', true),

    'cooldown_hours' => (int) env('MAILBOX_AUTO_REPLY_COOLDOWN_HOURS', 24),

    'internal_domains' => array_values(array_filter(array_map(
        'strtolower',
        array_map('trim', explode(',', env(
            'MAILBOX_AUTO_REPLY_INTERNAL_DOMAINS',
            'adwiseri.com,adwisery.com'
        )))
    ))),

    'mailboxes' => [
        'care@adwiseri.com' => [
            'enabled' => env('MAILBOX_AUTO_REPLY_CARE_ENABLED', true),
            'from_name' => 'Adwiseri Team',
            'subject' => 'Thank you for contacting Adwiseri',
            'body' => <<<'TEXT'
Dear Client,

Thank you for contacting Adwiseri.

We will respond to your queries as soon as possible, generally within 48 hours except weekends & public holidays.

Kind Regards,
Adwiseri Team
TEXT,
        ],

        'hello@adwiseri.com' => [
            'enabled' => env('MAILBOX_AUTO_REPLY_HELLO_ENABLED', true),
            'from_name' => 'Adwiseri',
            'subject' => 'Thank you for contacting Adwiseri',
            'body' => <<<'TEXT'
Hello,

Thank you for contacting Adwiseri.

We aim to reply emails, generally within 48 hours except weekends & public holidays.

Sincerely,
Adwiseri
TEXT,
        ],
    ],

];
