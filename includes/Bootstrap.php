<?php

declare(strict_types=1);

namespace WPCalibrate\Popups;

use WPCalibrate\Popups\Campaigns\CampaignPostType;
use WPCalibrate\Popups\Capabilities\CapabilityManager;
use WPCalibrate\Popups\Leads\LeadFormBlock;
use WPCalibrate\Popups\Rules\DeviceRule;
use WPCalibrate\Popups\Rules\PageTargetingRule;
use WPCalibrate\Popups\Rules\UserStatusRule;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Boots core components and lifecycle events.
 */
final class Bootstrap
{
    public static function init(): void
    {
        $plugin = Plugin::getInstance();

        // 1. Load translations
        add_action('init', static function (): void {
            load_plugin_textdomain(
                'wpcalibrate-popups-lead-capture',
                false,
                dirname(WPCAL_POPUPS_BASENAME) . '/languages/'
            );
        });

        // 2. Register Post Type and Lead Form Block
        add_action('init', static function (): void {
            CampaignPostType::register();
            LeadFormBlock::register();
        });

        // 3. Register Core Targeting Rules
        $registry = $plugin->getRuleRegistry();
        $registry->register(new PageTargetingRule());
        $registry->register(new UserStatusRule());
        $registry->register(new DeviceRule());

        /**
         * Action to allow Pro and third-party extensions to register custom targeting rules.
         *
         * @param \WPCalibrate\Popups\Rules\RuleRegistry $registry Targeting rule registry.
         */
        do_action('wpcal_popups_register_rule', $registry);

        // 4. Initialize REST API Server
        $plugin->getRestServer()->init();

        // 5. Initialize Privacy Tools & Data Retention
        $plugin->getPrivacyPolicy()->init();
        $plugin->getRetentionCleaner()->init();

        // 6. Initialize Admin Menu & Controllers
        if (is_admin()) {
            $adminMenu = $plugin->getAdminMenu();
            if ($adminMenu) {
                $adminMenu->init();
            }
            \WPCalibrate\Popups\Support\GitHubUpdater::init();
        } else {
            // Frontend modal runner
            $plugin->getFrontendRenderer()->init();

            // Preview mode handler
            add_action('wp', [self::class, 'handlePreviewMode']);
        }

        /**
         * Core plugin fully initialized hook.
         *
         * @param Plugin $plugin Core plugin instance.
         */
        do_action('wpcal_popups_loaded', $plugin);
    }

    /**
     * Handles isolated live preview of popup campaigns for authorized administrators.
     */
    public static function handlePreviewMode(): void
    {
        if (!isset($_GET['wpcal_preview'])) {
            return;
        }

        $popupId = absint($_GET['wpcal_preview']);
        if ($popupId <= 0) {
            return;
        }

        // Verify authorization and preview nonce
        if (!current_user_can(CapabilityManager::CAP_MANAGE_POPUPS) && !current_user_can('edit_posts')) {
            return;
        }

        $nonce = sanitize_text_field($_GET['_wpnonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'wpcal_preview_' . $popupId)) {
            return;
        }

        $campaign = Plugin::getInstance()->getCampaignRepository()->get($popupId);
        if (!$campaign) {
            return;
        }

        // Enqueue assets for preview
        add_action('wp_enqueue_scripts', static function () use ($campaign): void {
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

            wp_enqueue_script(
                'wpcal-popup-runtime',
                WPCAL_POPUPS_URL . 'assets/frontend/js/popup-runtime.js',
                [],
                WPCAL_POPUPS_VERSION,
                ['in_footer' => true]
            );

            wp_localize_script('wpcal-popup-runtime', 'wpcalPopupsData', [
                'restUrl'   => esc_url_raw(rest_url()),
                'restNonce' => wp_create_nonce('wp_rest'),
                'isPreview' => true,
                'campaigns' => [[
                    'id'            => $campaign->getId(),
                    'trigger'       => ['type' => 'time_delay', 'time_delay_ms' => 200],
                    'frequency'     => ['rule' => 'every_visit', 'suppress_on_conversion' => false],
                    'appearance'    => $campaign->getAppearance(),
                    'lead_settings' => $campaign->getLeadSettings(),
                ]],
            ]);
        }, 30);

        // Render preview modal in footer with preview banner
        add_action('wp_footer', static function () use ($campaign): void {
            echo '<div style="position: fixed; top: 0; left: 0; width: 100%; background: #2563eb; color: #fff; text-align: center; font-size: 13px; font-weight: 600; padding: 8px 12px; z-index: 99999999; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">'
                . esc_html__('PREVIEW MODE: Frequency limits and production analytics are disabled.', 'wpcalibrate-popups-lead-capture')
                . '</div>';

            $template = WPCAL_POPUPS_PATH . 'templates/frontend/popup-modal.php';
            if (file_exists($template)) {
                include $template;
            }
        }, 60);
    }
}
