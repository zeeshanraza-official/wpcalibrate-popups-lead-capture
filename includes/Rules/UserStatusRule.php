<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Rules;

use WPCalibrate\Popups\Campaigns\CampaignModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Evaluates whether visitor is logged in or logged out.
 */
final class UserStatusRule implements RuleInterface
{
    public function getKey(): string
    {
        return 'user_status';
    }

    public function getLabel(): string
    {
        return __('User Logged-In Status', 'wpcalibrate-popups-lead-capture');
    }

    public function evaluate(CampaignModel $campaign, array $context = []): bool
    {
        $targeting = $campaign->getTargeting();
        $authState = $targeting['auth_state'] ?? 'all';

        if ($authState === 'all') {
            return true;
        }

        $isLoggedIn = $context['is_logged_in'] ?? is_user_logged_in();

        if ($authState === 'logged_in' && !$isLoggedIn) {
            return false;
        }

        if ($authState === 'logged_out' && $isLoggedIn) {
            return false;
        }

        return true;
    }
}
