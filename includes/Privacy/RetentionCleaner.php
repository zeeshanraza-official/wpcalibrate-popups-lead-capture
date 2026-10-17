<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Privacy;

use WPCalibrate\Popups\Leads\LeadRepository;
use WPCalibrate\Popups\Settings\SettingsRepository;
use WPCalibrate\Popups\Support\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scheduled background job for enforcing data retention policy on captured leads.
 */
final class RetentionCleaner
{
    public const CRON_HOOK = 'wpcal_popups_daily_retention_cleanup';

    private LeadRepository $leadRepo;

    public function __construct(LeadRepository $leadRepo)
    {
        $this->leadRepo = $leadRepo;
    }

    public function init(): void
    {
        add_action(self::CRON_HOOK, [$this, 'runCleanup']);
    }

    /**
     * Executes the retention pruning.
     */
    public function runCleanup(): void
    {
        $retentionDays = (int) SettingsRepository::get('retention_days', 0);
        if ($retentionDays <= 0) {
            return;
        }

        try {
            $deletedCount = $this->leadRepo->deleteByRetentionDays($retentionDays);
            if ($deletedCount > 0) {
                Logger::info(sprintf('Data retention cleanup removed %d lead records older than %d days.', $deletedCount, $retentionDays));
            }
        } catch (\Throwable $e) {
            Logger::error('Retention cleanup failed: ' . $e->getMessage());
        }
    }
}
