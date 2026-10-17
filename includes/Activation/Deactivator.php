<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Activation;

use WPCalibrate\Popups\Privacy\RetentionCleaner;
use WPCalibrate\Popups\Support\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin deactivation routines.
 * Preserves campaigns, leads, analytics, and settings by design.
 */
final class Deactivator
{
    public static function deactivate(): void
    {
        // Clear scheduled retention cleanup cron
        wp_clear_scheduled_hook(RetentionCleaner::CRON_HOOK);

        // Invalidate active campaigns cache
        wp_cache_delete('wpcal_active_campaigns', 'wpcal_popups');

        Logger::info('WPCalibrate Popups deactivated. User data preserved.');
    }
}
