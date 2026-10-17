<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Rules;

use WPCalibrate\Popups\Campaigns\CampaignModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Evaluates page and post targeting (entire site, specific pages, exclude pages, post types).
 */
final class PageTargetingRule implements RuleInterface
{
    public function getKey(): string
    {
        return 'page_targeting';
    }

    public function getLabel(): string
    {
        return __('Page & Post Targeting', 'wpcalibrate-popups-lead-capture');
    }

    public function evaluate(CampaignModel $campaign, array $context = []): bool
    {
        $targeting = $campaign->getTargeting();
        $mode      = $targeting['mode'] ?? 'all';
        $pageIds   = isset($targeting['page_ids']) && is_array($targeting['page_ids']) ? array_map('absint', $targeting['page_ids']) : [];
        $currentId = $context['post_id'] ?? (is_singular() ? (int) get_queried_object_id() : 0);

        if ($mode === 'specific') {
            if (empty($pageIds) || !in_array($currentId, $pageIds, true)) {
                return false;
            }
        } elseif ($mode === 'exclude') {
            if (!empty($pageIds) && in_array($currentId, $pageIds, true)) {
                return false;
            }
        }

        // Post types check if on singular post
        $postTypes = isset($targeting['post_types']) && is_array($targeting['post_types']) ? $targeting['post_types'] : [];
        if (!empty($postTypes) && is_singular()) {
            $currentPostType = get_post_type($currentId);
            if ($currentPostType && !in_array($currentPostType, $postTypes, true)) {
                return false;
            }
        }

        return true;
    }
}
