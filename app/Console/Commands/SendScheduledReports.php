<?php

namespace App\Console\Commands;

use App\Models\ReportSetting;
use App\Services\ScheduledReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:dispatch-scheduled';

    protected $description = 'Generate and send scheduled report PDFs as attachment or link based on report settings.';

    public function handle(ScheduledReportService $scheduledReportService)
    {
        $settings = ReportSetting::all();

        foreach ($settings as $setting) {
            if (!$scheduledReportService->shouldRunForSetting($setting)) {
                continue;
            }

            $result = $scheduledReportService->dispatchForSetting($setting, 'scheduled');
            $message = 'user_id ' . $setting->user_id . ': ' . $result['message'];
            $this->info($message);
            Log::info('[scheduled-reports] ' . $message);
        }

        return Command::SUCCESS;
    }
}
