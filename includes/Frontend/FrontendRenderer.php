<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Frontend;

use WPCalibrate\Popups\Campaigns\CampaignModel;
use WPCalibrate\Popups\Campaigns\CampaignRepository;
use WPCalibrate\Popups\Rules\RuleRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages frontend asset enqueuing, targeting evaluation, and modal rendering.
 */
final class FrontendRenderer
{
    private CampaignRepository $campaignRepo;
    private RuleRegistry $ruleRegistry;

    /**
     * @var array<CampaignModel>
     */
    private array $eligibleCampaigns = [];

    private bool $evaluated = false;

    public function __construct(CampaignRepository $campaignRepo, RuleRegistry $ruleRegistry)
    {
        $this->campaignRepo = $campaignRepo;
        $this->ruleRegistry = $ruleRegistry;
    }

    public function init(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], 20);
        add_action('wp_footer', [$this, 'renderModals'], 50);
    }

    /**
     * Determine eligible campaigns for the current request.
     *
     * @return array<CampaignModel>
     */
    public function getEligibleCampaigns(): array
    {
        if ($this->evaluated) {
            return $this->eligibleCampaigns;
        }

        $this->evaluated = true;

        if (is_admin() || wp_doing_ajax() || wp_is_json_request()) {
            return [];
        }

        $active = $this->campaignRepo->getActiveCampaigns();
        if (empty($active)) {
            return [];
        }

        $context = [
            'post_id'      => is_singular() ? (int) get_queried_object_id() : 0,
            'is_logged_in' => is_user_logged_in(),
            'is_mobile'    => wp_is_mobile(),
        ];

        foreach ($active as $campaign) {
            if ($this->ruleRegistry->evaluateAll($campaign, $context)) {
                $this->eligibleCampaigns[] = $campaign;
            }
        }

        return $this->eligibleCampaigns;
    }

    /**
     * Enqueue styles and scripts conditionally.
     * ZERO assets are loaded if no active campaigns match current page!
     */
    public function enqueueAssets(): void
    {
        $eligible = $this->getEligibleCampaigns();
        if (empty($eligible)) {
            return;
        }

        // Enqueue styles
        wp_enqueue_style(
            'wpcal-popup-runtime',
            WPCAL_POPUPS_URL . 'assets/frontend/css/popup-runtime.css',
            [],
            WPCAL_POPUPS_VERSION
        );

        wp_enqueue_style(
            'wpcal-lead-form-style',
            WPCAL_POPUPS_URL . 'blocks/lead-form/style.css',
            [],
            WPCAL_POPUPS_VERSION
        );

        // Enqueue script deferred
        wp_enqueue_script(
            'wpcal-popup-runtime',
            WPCAL_POPUPS_URL . 'assets/frontend/js/popup-runtime.js',
            [],
            WPCAL_POPUPS_VERSION,
            ['strategy' => 'defer', 'in_footer' => true]
        );

        $campaignsData = [];
        foreach ($eligible as $camp) {
            $campaignsData[] = [
                'id'            => $camp->getId(),
                'trigger'       => $camp->getTrigger(),
                'frequency'     => $camp->getFrequency(),
                'appearance'    => $camp->getAppearance(),
                'lead_settings' => $camp->getLeadSettings(),
            ];
        }

        wp_localize_script('wpcal-popup-runtime', 'wpcalPopupsData', [
            'restUrl'   => esc_url_raw(rest_url()),
            'restNonce' => wp_create_nonce('wp_rest'),
            'isPreview' => false,
            'campaigns' => $campaignsData,
        ]);
    }

    /**
     * Render eligible popup modals in the page footer.
     */
    public function renderModals(): void
    {
        $eligible = $this->getEligibleCampaigns();
        if (empty($eligible)) {
            return;
        }

        $template = WPCAL_POPUPS_PATH . 'templates/frontend/popup-modal.php';
        if (!file_exists($template)) {
            return;
        }

        foreach ($eligible as $campaign) {
            include $template;
        }
    }
}
