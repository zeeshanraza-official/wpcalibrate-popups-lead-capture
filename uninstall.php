<?php
/**
 * WPCalibrate Popups & Lead Capture Uninstall Handler.
 * Preserves user data by default unless explicitly configured otherwise.
 */

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$settings = get_option('wpcal_popups_settings', []);
$deleteOnUninstall = !empty($settings['delete_on_uninstall']);

// Default: Preserve data if not explicitly requested
if (!$deleteOnUninstall) {
    return;
}

global $wpdb;

// 1. Delete all plugin custom post type records (wpcal_popup)
$popupPosts = get_posts([
    'post_type'      => 'wpcal_popup',
    'post_status'    => 'any',
    'numberposts'    => -1,
    'fields'         => 'ids',
]);

foreach ($popupPosts as $postId) {
    wp_delete_post($postId, true);
}

// 2. Drop custom tables safely
$leadsTable = $wpdb->prefix . 'wpcal_popup_leads';
$statsTable = $wpdb->prefix . 'wpcal_popup_stats_daily';

$wpdb->query("DROP TABLE IF EXISTS {$leadsTable}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query("DROP TABLE IF EXISTS {$statsTable}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

// 3. Clear scheduled cron hooks
wp_clear_scheduled_hook('wpcal_popups_daily_retention_cleanup');

// 4. Remove plugin capabilities from administrator role
$adminRole = get_role('administrator');
if ($adminRole) {
    $caps = [
        'wpcal_manage_popups',
        'wpcal_manage_popup_leads',
        'wpcal_export_popup_leads',
        'wpcal_manage_popup_settings',
    ];
    foreach ($caps as $cap) {
        $adminRole->remove_cap($cap);
    }
}

// 5. Delete plugin-specific options (never touch other WPCalibrate plugin options)
delete_option('wpcal_popups_settings');
delete_option('wpcal_popups_db_version');

// 6. Delete transient rate-limiting and cache entries
$wpdb->query(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpcal_%' OR option_name LIKE '_transient_timeout_wpcal_%'"
);
