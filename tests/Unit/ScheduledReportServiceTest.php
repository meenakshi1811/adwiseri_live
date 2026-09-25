<?php

namespace Tests\Unit;

use App\Services\AffiliateReportSettingService;
use App\Services\ScheduledReportService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ScheduledReportServiceTest extends TestCase
{
    private function service(): ScheduledReportService
    {
        return new ScheduledReportService(new AffiliateReportSettingService());
    }

    public function testExtractRecipientsKeepsAllCommaSeparatedEmails(): void
    {
        $service = $this->service();
        $method = (new ReflectionClass($service))->getMethod('extractRecipients');
        $method->setAccessible(true);

        $recipients = $method->invoke(
            $service,
            'first@example.com, second@example.com, third@example.com',
            'owner@example.com'
        );

        $this->assertSame([
            'owner@example.com',
            'first@example.com',
            'second@example.com',
            'third@example.com',
        ], $recipients);
    }

    public function testExtractRecipientsSplitsSupportedSeparatorsAndDeduplicatesCaseInsensitively(): void
    {
        $service = $this->service();
        $method = (new ReflectionClass($service))->getMethod('extractRecipients');
        $method->setAccessible(true);

        $recipients = $method->invoke(
            $service,
            "FIRST@example.com; second@example.com\nfirst@example.com, invalid-email",
            'owner@example.com'
        );

        $this->assertSame([
            'owner@example.com',
            'FIRST@example.com',
            'second@example.com',
        ], $recipients);
    }
}
