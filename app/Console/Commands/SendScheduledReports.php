<?php

namespace App\Console\Commands;

use App\Models\ReportSetting;
use App\Models\User;
use App\Services\AffiliateReportSettingService;
use App\Services\ScheduledReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:dispatch-scheduled';

    protected $description = 'Generate and send scheduled report PDFs as attachment or link based on report settings.';

    public function handle(
        ScheduledReportService $scheduledReportService,
        AffiliateReportSettingService $affiliateReportSettingService
    ) {
        $backfilled = $affiliateReportSettingService->backfillMissingSettings();
        if ($backfilled > 0) {
            Log::info('[scheduled-reports] Created default affiliate report settings.', ['count' => $backfilled]);
        }

        $settings = ReportSetting::query()
            ->whereIn('user_id', function ($query) {
                $query->select('id')
                    ->from('users')
                    ->where('status', 'true');
            })
            ->get();

        foreach ($settings as $setting) {
            $owner = User::find($setting->user_id);
            if ($affiliateReportSettingService->isAffiliateUser($owner)) {
                continue;
            }

            if (!$scheduledReportService->shouldRunForSetting($setting)) {
                continue;
            }

            $result = $scheduledReportService->dispatchForSetting($setting, 'scheduled');
            $message = 'user_id ' . $setting->user_id . ': ' . $result['message'];
            $this->info($message);
            Log::info('[scheduled-reports] ' . $message);
        }

        foreach ($affiliateReportSettingService->activeAffiliateUsers() as $affiliateUser) {
            $setting = $affiliateReportSettingService->ensureReportSetting($affiliateUser);

            if (!$scheduledReportService->shouldRunForSetting($setting)) {
                continue;
            }

            $result = $scheduledReportService->dispatchForSetting($setting, 'scheduled');
            $message = 'affiliate user_id ' . $affiliateUser->id . ': ' . $result['message'];
            $this->info($message);
            Log::info('[scheduled-reports] ' . $message);
        }

        return Command::SUCCESS;
    }
}
