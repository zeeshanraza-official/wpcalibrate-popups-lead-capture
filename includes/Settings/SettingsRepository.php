<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Settings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages plugin settings persistence and sanitization.
 */
final class SettingsRepository
{
    public const OPTION_KEY = 'wpcal_popups_settings';

    /**
     * Get all settings merged with defaults.
     *
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        $defaults = self::getDefaults();
        $saved = get_option(self::OPTION_KEY, []);

        return is_array($saved) ? array_merge($defaults, $saved) : $defaults;
    }

    /**
     * Get a single setting by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::getAll();
        return $all[$key] ?? $default;
    }

    /**
     * Save updated settings with sanitization.
     */
    public static function save(array $settings): bool
    {
        $clean = self::sanitize($settings);
        return update_option(self::OPTION_KEY, $clean);
    }

    /**
     * Default configuration options.
     */
    public static function getDefaults(): array
    {
        return [
            // General
            'disable_runtime_caching' => false,
            // Lead Handling & Notifications
            'enable_notifications'    => true,
            'notification_recipient' => get_option('admin_email'),
            'email_subject_template' => '[New Lead] {campaign_title} on {site_title}',
            // Privacy & Data
            'collect_source_url'      => true,
            'retention_days'          => 0, // 0 = retain indefinitely
            'delete_on_uninstall'     => false,
            // Performance & Debug
            'enable_debug_logging'    => false,
        ];
    }

    /**
     * Sanitize settings array.
     */
    public static function sanitize(array $input): array
    {
        $clean = [];

        $clean['disable_runtime_caching'] = !empty($input['disable_runtime_caching']);
        $clean['enable_notifications']    = !empty($input['enable_notifications']);
        $clean['notification_recipient'] = sanitize_email($input['notification_recipient'] ?? get_option('admin_email'));
        $clean['email_subject_template'] = sanitize_text_field($input['email_subject_template'] ?? '[New Lead] {campaign_title}');
        $clean['collect_source_url']      = !empty($input['collect_source_url']);
        $clean['retention_days']          = max(0, absint($input['retention_days'] ?? 0));
        $clean['delete_on_uninstall']     = !empty($input['delete_on_uninstall']);
        $clean['enable_debug_logging']    = !empty($input['enable_debug_logging']);

        return apply_filters('wpcal_popups_sanitize_settings', $clean, $input);
    }
}
