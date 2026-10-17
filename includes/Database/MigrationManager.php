<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Database;

use WPCalibrate\Popups\Support\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Migration Manager for custom database tables.
 * Safely creates and upgrades tables using dbDelta() and ordered versioned migrations.
 */
final class MigrationManager
{
    public const SCHEMA_VERSION = '1.0.0';
    public const OPTION_KEY     = 'wpcal_popups_db_version';

    /**
     * Run any pending database migrations.
     */
    public static function migrate(): bool
    {
        $installedVersion = get_option(self::OPTION_KEY, '0.0.0');

        if (version_compare((string) $installedVersion, self::SCHEMA_VERSION, '>=')) {
            return true;
        }

        try {
            self::install100();
            update_option(self::OPTION_KEY, self::SCHEMA_VERSION);
            Logger::info('Database schema successfully migrated to version ' . self::SCHEMA_VERSION);
            return true;
        } catch (\Throwable $e) {
            Logger::error('Database migration failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Initial 1.0.0 schema creation.
     */
    private static function install100(): void
    {
        global $wpdb;

        if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $charsetCollate = $wpdb->get_charset_collate();

        // 1. Leads Table
        $leadsTable = $wpdb->prefix . 'wpcal_popup_leads';
        $leadsSql = "CREATE TABLE {$leadsTable} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid CHAR(36) NOT NULL,
            popup_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            email VARCHAR(320) DEFAULT NULL,
            first_name VARCHAR(100) DEFAULT NULL,
            last_name VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            fields LONGTEXT DEFAULT NULL,
            consent LONGTEXT DEFAULT NULL,
            source_url TEXT DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'new',
            notification_status VARCHAR(50) NOT NULL DEFAULT 'pending',
            integration_status VARCHAR(50) NOT NULL DEFAULT 'none',
            dedupe_key VARCHAR(64) DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY uuid (uuid),
            KEY popup_id (popup_id),
            KEY email (email(191)),
            KEY status (status),
            KEY dedupe_key (dedupe_key),
            KEY created_at (created_at)
        ) {$charsetCollate};";

        // 2. Daily Stats Table
        $statsTable = $wpdb->prefix . 'wpcal_popup_stats_daily';
        $statsSql = "CREATE TABLE {$statsTable} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            popup_id BIGINT UNSIGNED NOT NULL,
            stat_date DATE NOT NULL,
            impressions BIGINT UNSIGNED NOT NULL DEFAULT 0,
            conversions BIGINT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY popup_stat_date (popup_id, stat_date),
            KEY stat_date (stat_date)
        ) {$charsetCollate};";

        dbDelta($leadsSql);
        dbDelta($statsSql);
    }

    /**
     * Drop plugin tables upon complete uninstall if configured.
     */
    public static function dropTables(): void
    {
        global $wpdb;

        $leadsTable = $wpdb->prefix . 'wpcal_popup_leads';
        $statsTable = $wpdb->prefix . 'wpcal_popup_stats_daily';

        $wpdb->query("DROP TABLE IF EXISTS {$leadsTable}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("DROP TABLE IF EXISTS {$statsTable}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        delete_option(self::OPTION_KEY);
    }
}
