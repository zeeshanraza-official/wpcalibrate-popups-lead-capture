<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Capabilities;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages plugin-specific user capabilities and authorization checks.
 */
final class CapabilityManager
{
    public const CAP_MANAGE_POPUPS   = 'wpcal_manage_popups';
    public const CAP_MANAGE_LEADS    = 'wpcal_manage_popup_leads';
    public const CAP_EXPORT_LEADS    = 'wpcal_export_popup_leads';
    public const CAP_MANAGE_SETTINGS = 'wpcal_manage_popup_settings';

    /**
     * All capabilities registered by this plugin.
     *
     * @return array<string>
     */
    public static function getAllCapabilities(): array
    {
        return [
            self::CAP_MANAGE_POPUPS,
            self::CAP_MANAGE_LEADS,
            self::CAP_EXPORT_LEADS,
            self::CAP_MANAGE_SETTINGS,
        ];
    }

    /**
     * Grant capabilities to administrator role upon plugin activation.
     */
    public static function grantAdminCapabilities(): void
    {
        $adminRole = get_role('administrator');
        if (!$adminRole) {
            return;
        }

        foreach (self::getAllCapabilities() as $cap) {
            $adminRole->add_cap($cap);
        }
    }

    /**
     * Remove capabilities from administrator role upon plugin uninstallation if configured.
     */
    public static function removeAdminCapabilities(): void
    {
        $adminRole = get_role('administrator');
        if (!$adminRole) {
            return;
        }

        foreach (self::getAllCapabilities() as $cap) {
            $adminRole->remove_cap($cap);
        }
    }

    /**
     * Map meta capabilities if needed.
     */
    public static function mapMetaCaps(array $caps, string $cap, int $userId, array $args): array
    {
        switch ($cap) {
            case self::CAP_MANAGE_POPUPS:
            case self::CAP_MANAGE_LEADS:
            case self::CAP_EXPORT_LEADS:
            case self::CAP_MANAGE_SETTINGS:
                // Administrators automatically have access if not explicitly assigned.
                if (user_can($userId, 'manage_options')) {
                    return ['exist'];
                }
                break;
        }

        return $caps;
    }
}
