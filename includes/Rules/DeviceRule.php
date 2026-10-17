<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Rules;

use WPCalibrate\Popups\Campaigns\CampaignModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Basic server-side device evaluation rule with client-side verification support.
 */
final class DeviceRule implements RuleInterface
{
    public function getKey(): string
    {
        return 'device_targeting';
    }

    public function getLabel(): string
    {
        return __('Device Targeting', 'wpcalibrate-popups-lead-capture');
    }

    public function evaluate(CampaignModel $campaign, array $context = []): bool
    {
        $targeting = $campaign->getTargeting();
        $devices   = $targeting['devices'] ?? ['desktop', 'tablet', 'mobile'];

        // If all devices selected, always pass
        if (in_array('desktop', $devices, true) && in_array('tablet', $devices, true) && in_array('mobile', $devices, true)) {
            return true;
        }

        // On server side, wp_is_mobile() distinguishes mobile/tablets from desktop
        $isMobile = $context['is_mobile'] ?? wp_is_mobile();

        if ($isMobile) {
            // Mobile or tablet
            if (!in_array('mobile', $devices, true) && !in_array('tablet', $devices, true)) {
                return false;
            }
        } else {
            // Desktop
            if (!in_array('desktop', $devices, true)) {
                return false;
            }
        }

        return true;
    }
}
