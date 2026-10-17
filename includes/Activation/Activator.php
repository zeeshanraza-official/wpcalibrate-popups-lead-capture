<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Activation;

use WPCalibrate\Popups\Capabilities\CapabilityManager;
use WPCalibrate\Popups\Database\MigrationManager;
use WPCalibrate\Popups\Privacy\RetentionCleaner;
use WPCalibrate\Popups\Settings\SettingsRepository;
use WPCalibrate\Popups\Support\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles plugin activation routines.
 */
final class Activator
{
    public static function activate(): void
    {
        // 1. Run database schema migrations
        MigrationManager::migrate();

        // 2. Grant administrator capabilities
        CapabilityManager::grantAdminCapabilities();

        // 3. Register default settings if not already present
        if (false === get_option(SettingsRepository::OPTION_KEY)) {
            update_option(SettingsRepository::OPTION_KEY, SettingsRepository::getDefaults());
        }

        // 4. Schedule daily data retention cleanup cron if not scheduled
        if (!wp_next_scheduled(RetentionCleaner::CRON_HOOK)) {
            wp_schedule_event(time() + 3600, 'daily', RetentionCleaner::CRON_HOOK);
        }

        Logger::info('WPCalibrate Popups activated successfully.');
    }
}
