<?php

namespace App\Support;

class ModuleIcons
{
    /** @var array<string, string> */
    public const ICONS = [
        'dashboard' => 'fas fa-home',
        'clients' => 'fas fa-users',
        'applications' => 'fa-solid fa-window-restore',
        'invoices' => 'fas fa-file',
        'payments' => 'fas fa-dollar',
        'users' => 'fas fa-user',
        'communications' => 'fa-solid fa-comment',
        'meeting_notes' => 'fa-solid fa-comment',
        'associates' => 'fa-solid fa-handshake',
        'reports' => 'fa-solid fa-file-lines',
        'analytics' => 'fa-solid fa-chart-simple',
        'referrals' => 'fa-solid fa-asterisk',
        'wallet' => 'fas fa-wallet',
        'subscription' => 'fa-solid fa-money-bill-wave',
        'settings' => 'fa-solid fa-gear',
        'support' => 'fa-solid fa-circle-info',
        'support_tickets' => 'fa-solid fa-circle-info',
        'documents' => 'fa-solid fa-folder-open',
        'activities' => 'fa-solid fa-clock',
        'affiliates' => 'fas fa-payment',
    ];

    public static function for(string $module): string
    {
        return self::ICONS[$module] ?? 'fa-solid fa-circle';
    }
}
