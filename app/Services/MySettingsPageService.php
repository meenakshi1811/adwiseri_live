<?php

namespace App\Services;

use App\Models\ApplicationReminder;
use App\Models\Clients;
use App\Models\Countries;
use App\Models\Currency;
use App\Models\Invoice_settings;
use App\Models\PaymentReminderSetting;
use App\Models\ReportSetting;
use App\Models\User;
use App\Models\UserRoles;
use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MySettingsPageService
{
    public function buildViewData(User $user): array
    {
        $subscriber = app(CountryCategorySettingsService::class)->resolveSubscriber($user);
        $subscriberId = empty($user->added_by) ? (int) $user->id : (int) $user->added_by;

        $invSetting = $this->safe(fn () => Invoice_settings::forUser((int) $user->id, Invoice_settings::RECIPIENT_CLIENTS));
        $invSettingAssociates = $this->safe(fn () => Invoice_settings::forUser((int) $user->id, Invoice_settings::RECIPIENT_ASSOCIATES));

        $paymentReminderSetting = $this->safe(fn () => PaymentReminderSetting::forUserPayments((int) $user->id));
        $documentsReminderSetting = $this->safe(fn () => PaymentReminderSetting::forUserDocuments((int) $user->id));

        $selectedRemindersTo = PaymentReminderSetting::normalizeRemindersTo(
            optional($paymentReminderSetting)->reminders_to,
            optional($paymentReminderSetting)->email_to
        );
        $selectedEmailTo = PaymentReminderSetting::normalizeEmailTo(optional($paymentReminderSetting)->email_to);
        if (!in_array($selectedEmailTo, PaymentReminderSetting::allowedEmailToValuesForRemindersTo($selectedRemindersTo), true)) {
            $selectedEmailTo = PaymentReminderSetting::defaultEmailToForRemindersTo($selectedRemindersTo);
        }

        $selectedDocumentsEmailTo = PaymentReminderSetting::normalizeEmailTo(optional($documentsReminderSetting)->email_to);
        if (!in_array(
            $selectedDocumentsEmailTo,
            PaymentReminderSetting::allowedEmailToValuesForRemindersTo(PaymentReminderSetting::REMINDERS_TO_CLIENTS),
            true
        )) {
            $selectedDocumentsEmailTo = PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY;
        }

        $ccService = app(CountryCategorySettingsService::class);
        $dashboardService = app(DashboardPreferenceService::class);
        $enquiryFormService = app(EnquiryFormSettingsService::class);

        $ccDocumentLists = $this->safe(fn () => $ccService->getDocumentLists($subscriber), []);

        return [
            'tzlist' => $this->safe(fn () => DateTimeZone::listIdentifiers(DateTimeZone::ALL), ['UTC']),
            'roles' => $this->safe(fn () => UserRoles::where('user_id', '=', $user->id)->first()),
            'user' => $user,
            'page' => 'settings',
            'currencies' => $this->safe(fn () => Currency::orderBy('currency_code')->get(), collect()),
            'inv_setting' => $invSetting,
            'inv_setting_associates' => $invSettingAssociates,
            'invoiceSettingsByRecipient' => [
                Invoice_settings::RECIPIENT_CLIENTS => $invSetting
                    ? $invSetting->toSettingsArray()
                    : $this->emptyInvoiceSettings(),
                Invoice_settings::RECIPIENT_ASSOCIATES => $invSettingAssociates
                    ? $invSettingAssociates->toSettingsArray()
                    : $this->emptyInvoiceSettings(),
            ],
            'clients' => $this->safe(
                fn () => Clients::where('subscriber_id', '=', $subscriber->id)->orderBy('created_at', 'desc')->get(),
                collect()
            ),
            'reportSetting' => $this->safe(fn () => ReportSetting::where('user_id', $user->id)->first()),
            'paymentReminderSetting' => $paymentReminderSetting,
            'documentsReminderSetting' => $documentsReminderSetting,
            'selectedRemindersTo' => $selectedRemindersTo,
            'selectedEmailTo' => $selectedEmailTo,
            'selectedDocumentsEmailTo' => $selectedDocumentsEmailTo,
            'applicationReminders' => $this->loadApplicationReminders((int) $user->id),
            'reportModules' => $this->reportModulesFor($user),
            'emailTemplates' => $this->safe(
                fn () => app(EmailTemplateService::class)->getTemplatesForSettings($user),
                ['admin' => collect(), 'subscriber' => collect()]
            ),
            'emailTemplateAudience' => strtolower($user->user_type) === 'admin' ? 'admin' : 'subscriber',
            'subscriber' => $subscriber,
            'allCountries' => $this->safe(fn () => Countries::orderBy('country_name', 'asc')->get(), collect()),
            'allVisaCategories' => $this->safe(fn () => $ccService->getSelectableVisaCategoryNames($subscriber), collect()),
            'defaultCountries' => $this->safe(fn () => $ccService->getDefaultCountryNames($subscriber), collect()),
            'defaultVisaCategories' => $this->safe(fn () => $ccService->getDefaultVisaCategoryNames($subscriber), collect()),
            'selectedCountries' => $this->safe(fn () => $ccService->resolveCountryNames($subscriber), collect()),
            'selectedVisaCategories' => $this->safe(fn () => $ccService->resolveVisaCategoryNames($subscriber), collect()),
            'ccSetting' => $this->safe(fn () => $ccService->getSetting($subscriber)),
            'ccUsingDefaults' => $this->safe(fn () => !$ccService->hasSavedCcSelection($subscriber), true),
            'documentTypes' => $ccService->getDocumentTypes(),
            'ccDocumentLists' => is_array($ccDocumentLists) ? $ccDocumentLists : [],
            'ccCommonDocuments' => $ccService->getCommonDocumentSet(),
            'notificationTypes' => $this->safe(
                fn () => app(NotificationService::class)->typeDefinitionsForUser($user),
                []
            ),
            'notificationPreferences' => $this->safe(
                fn () => app(NotificationService::class)->getPreferences($user),
                []
            ),
            'dashboardHeaderOptions' => $dashboardService->headerOptions(),
            'dashboardChartModules' => $dashboardService->chartModules(),
            'dashboardChartTypes' => $dashboardService->chartTypes(),
            'dashboardDurations' => $dashboardService->durations(),
            'dashboardHeaders' => $this->safe(fn () => $dashboardService->resolveHeaders($subscriber), array_fill(0, DashboardPreferenceService::HEADER_SLOTS, '')),
            'dashboardCharts' => $this->safe(fn () => $dashboardService->resolveCharts($subscriber), array_fill(0, DashboardPreferenceService::CHART_SLOTS, null)),
            'dashboardChartCount' => $this->safe(fn () => $dashboardService->resolveChartCount($subscriber), DashboardPreferenceService::DEFAULT_CHART_COUNT),
            'dashboardChartAvailability' => [],
            'dashboardUsingDefaults' => $this->safe(fn () => !$dashboardService->hasSavedPreferences($subscriber), true),
            'dashboardHeaderSlots' => DashboardPreferenceService::HEADER_SLOTS,
            'dashboardChartSlots' => DashboardPreferenceService::CHART_SLOTS,
            'dashboardChartCountOptions' => DashboardPreferenceService::CHART_COUNT_OPTIONS,
            'enquiryFormSectionOptions' => $enquiryFormService->sectionOptions(),
            'enquiryFormSections' => $this->safe(fn () => $enquiryFormService->resolveSections($subscriber), $enquiryFormService->defaultSections()),
            'enquiryFormUsingDefaults' => $this->safe(fn () => !$enquiryFormService->hasSavedSettings($subscriber), true),
            'serviceCountryOptions' => $this->safe(fn () => $ccService->resolveServiceCountryOptions($subscriber), collect()),
            'serviceNameOptions' => $this->safe(fn () => $ccService->resolveServiceNameOptions($subscriber), collect()),
            'serviceCcPreferences' => $this->safe(
                fn () => $ccService->resolveSavedServicePreferences($subscriber),
                ['countries' => collect(), 'visa_categories' => collect(), 'has_saved' => false]
            ),
            'appointments' => $this->loadAppointments($subscriberId),
        ];
    }

    private function loadApplicationReminders(int $userId): Collection
    {
        return $this->safe(function () use ($userId) {
            if (!Schema::hasTable('application_reminders')) {
                return collect();
            }

            return ApplicationReminder::where('user_id', $userId)
                ->with(['client', 'application'])
                ->orderByDesc('deadline')
                ->get();
        }, collect());
    }

    private function loadAppointments(int $subscriberId): Collection
    {
        return $this->safe(
            fn () => app(AppointmentService::class)->loadForSubscriber($subscriberId),
            collect()
        );
    }

    private function reportModulesFor(User $user): array
    {
        $modules = [
            'clients' => 'Clients',
            'applications' => 'Applications',
            'invoices' => 'Invoices',
            'payments' => 'Payments',
            'referrals' => 'Referrals',
            'wallets' => 'Wallets',
        ];

        if (strtolower($user->user_type) === 'admin') {
            $modules['subscribers'] = 'Subscribers';
            $modules['affiliates'] = 'Affiliates';
        }

        return $modules;
    }

    private function emptyInvoiceSettings(): array
    {
        return [
            'tax' => '',
            'tax_label' => 'Tax',
            'discount' => '',
            'payment_link' => '',
            'payment_qr_code' => '',
            'payment_qr_url' => '',
            'invoice_note' => '',
        ];
    }

    /**
     * @template T
     * @param  callable(): T  $callback
     * @param  T|null  $default
     * @return T|null
     */
    private function safe(callable $callback, $default = null)
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::warning('my_settings data load failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return $default;
        }
    }
}
