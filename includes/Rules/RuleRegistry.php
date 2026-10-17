<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Rules;

use WPCalibrate\Popups\Campaigns\CampaignModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registry holding all active targeting rules.
 */
final class RuleRegistry
{
    /**
     * @var array<string, RuleInterface>
     */
    private array $rules = [];

    public function register(RuleInterface $rule): void
    {
        $this->rules[$rule->getKey()] = $rule;
    }

    /**
     * @return array<string, RuleInterface>
     */
    public function getAll(): array
    {
        return $this->rules;
    }

    public function get(string $key): ?RuleInterface
    {
        return $this->rules[$key] ?? null;
    }

    /**
     * Evaluates all registered rules against a campaign.
     * All rules must return true for campaign to pass (AND logic).
     * Pro group logic handles OR conditions across custom condition groups.
     */
    public function evaluateAll(CampaignModel $campaign, array $context = []): bool
    {
        foreach ($this->rules as $rule) {
            if (!$rule->evaluate($campaign, $context)) {
                return false;
            }
        }

        /**
         * Filter overall targeting eligibility result.
         *
         * @param bool $eligible Current eligibility result.
         * @param CampaignModel $campaign The campaign evaluated.
         * @param array $context Current request context.
         */
        return (bool) apply_filters('wpcal_popups_evaluate_targeting', true, $campaign, $context);
    }
}
