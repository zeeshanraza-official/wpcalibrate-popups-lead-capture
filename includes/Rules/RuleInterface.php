<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Rules;

use WPCalibrate\Popups\Campaigns\CampaignModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface that all targeting rules must implement.
 */
interface RuleInterface
{
    /**
     * Unique key for this rule.
     */
    public function getKey(): string;

    /**
     * Human-readable label for rule.
     */
    public function getLabel(): string;

    /**
     * Evaluates whether a campaign is eligible under this rule.
     *
     * @param CampaignModel $campaign The campaign being evaluated.
     * @param array $context Current request context (e.g., page ID, user info).
     * @return bool True if campaign is allowed, false if rejected.
     */
    public function evaluate(CampaignModel $campaign, array $context = []): bool;
}
