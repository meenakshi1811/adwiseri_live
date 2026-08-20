#!/usr/bin/env php
<?php

/**
 * WHW / cPanel email pipe entry point.
 *
 * Example forwarder target:
 *   |/usr/bin/php /home/USER/adwiseri_latest/scripts/mailbox-auto-reply.php hello@adwiseri.com
 */

$mailbox = $argv[1] ?? '';

if ($mailbox === '') {
    fwrite(STDERR, "Mailbox argument required.\n");
    exit(1);
}

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->call('mailbox:process-auto-reply', [
    'mailbox' => $mailbox,
], $kernel->output());

exit($status);
