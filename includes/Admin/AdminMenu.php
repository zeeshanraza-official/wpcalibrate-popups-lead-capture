<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Admin;

use WPCalibrate\Popups\Analytics\AnalyticsManager;
use WPCalibrate\Popups\Campaigns\CampaignPostType;
use WPCalibrate\Popups\Campaigns\CampaignRepository;
use WPCalibrate\Popups\Capabilities\CapabilityManager;
use WPCalibrate\Popups\Leads\CsvExporter;
use WPCalibrate\Popups\Leads\LeadRepository;
use WPCalibrate\Popups\Settings\SettingsRepository;
use WPCalibrate\Popups\Support\SharedMenu;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages admin menus, submenus, request dispatching, and page views.
 */
final class AdminMenu
{
    private CampaignRepository $campaignRepo;
    private LeadRepository $leadRepo;
    private AnalyticsManager $analytics;
    private CsvExporter $csvExporter;

    public function __construct(
        CampaignRepository $campaignRepo,
        LeadRepository $leadRepo,
        AnalyticsManager $analytics,
        CsvExporter $csvExporter
    ) {
        $this->campaignRepo = $campaignRepo;
        $this->leadRepo     = $leadRepo;
        $this->analytics    = $analytics;
        $this->csvExporter  = $csvExporter;
    }

    public function init(): void
    {
        add_action('admin_menu', [$this, 'registerMenus'], 10);
        add_action('admin_init', [$this, 'handleAdminActions']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    public function registerMenus(): void
    {
        // 1. Ensure shared WPCalibrate parent menu exists
        SharedMenu::registerParentMenu();

        // 2. Submenu: Overview
        add_submenu_page(
            SharedMenu::PARENT_SLUG,
            __('Popups & Lead Capture Overview', 'wpcalibrate-popups-lead-capture'),
            __('Overview', 'wpcalibrate-popups-lead-capture'),
            CapabilityManager::CAP_MANAGE_POPUPS,
            'wpcal-overview',
            [$this, 'renderOverviewPage']
        );

        // 3. Submenu: Campaigns
        add_submenu_page(
            SharedMenu::PARENT_SLUG,
            __('Popups & Lead Capture', 'wpcalibrate-popups-lead-capture'),
            __('Campaigns', 'wpcalibrate-popups-lead-capture'),
            CapabilityManager::CAP_MANAGE_POPUPS,
            'wpcal-popups',
            [$this, 'renderCampaignsPage']
        );

        // 4. Submenu: Leads
        add_submenu_page(
            SharedMenu::PARENT_SLUG,
            __('Captured Leads', 'wpcalibrate-popups-lead-capture'),
            __('Leads', 'wpcalibrate-popups-lead-capture'),
            CapabilityManager::CAP_MANAGE_LEADS,
            'wpcal-leads',
            [$this, 'renderLeadsPage']
        );

        // 5. Submenu: Analytics
        add_submenu_page(
            SharedMenu::PARENT_SLUG,
            __('Popup Analytics', 'wpcalibrate-popups-lead-capture'),
            __('Analytics', 'wpcalibrate-popups-lead-capture'),
            CapabilityManager::CAP_MANAGE_POPUPS,
            'wpcal-analytics',
            [$this, 'renderAnalyticsPage']
        );

        // 6. Submenu: Settings
        add_submenu_page(
            SharedMenu::PARENT_SLUG,
            __('Popup Settings', 'wpcalibrate-popups-lead-capture'),
            __('Settings', 'wpcalibrate-popups-lead-capture'),
            CapabilityManager::CAP_MANAGE_SETTINGS,
            'wpcal-settings',
            [$this, 'renderSettingsPage']
        );
    }

    public function enqueueAdminAssets(string $hook): void
    {
        global $post_type;

        $isPluginPage = in_array($hook, [
            'wpcalibrate_page_wpcal-overview',
            'wpcalibrate_page_wpcal-popups',
            'wpcalibrate_page_wpcal-leads',
            'wpcalibrate_page_wpcal-analytics',
            'wpcalibrate_page_wpcal-settings',
            'toplevel_page_wpcalibrate-dashboard',
        ], true);

        $isCampaignEditor = ($post_type === CampaignPostType::POST_TYPE);

        if (!$isPluginPage && !$isCampaignEditor) {
            return;
        }

        if ($isPluginPage) {
            wp_enqueue_style(
                'wpcal-admin-style-css',
                WPCAL_POPUPS_URL . 'assets/admin/css/admin-style.css',
                [],
                WPCAL_POPUPS_VERSION
            );
        }

        if ($isCampaignEditor) {
            wp_enqueue_script(
                'wpcal-campaign-editor',
                WPCAL_POPUPS_URL . 'assets/editor/js/campaign-editor.js',
                ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'],
                WPCAL_POPUPS_VERSION,
                true
            );

            wp_enqueue_style(
                'wpcal-campaign-editor-css',
                WPCAL_POPUPS_URL . 'assets/editor/css/campaign-editor.css',
                ['wp-edit-post'],
                WPCAL_POPUPS_VERSION
            );
        }
    }

    public function handleAdminActions(): void
    {
        $page   = sanitize_key($_GET['page'] ?? '');
        $action = sanitize_key($_REQUEST['action'] ?? ($_REQUEST['wpcal_action'] ?? ''));

        if ($action === '') {
            return;
        }

        // CSV Export
        if ($page === 'wpcal-leads' && $action === 'export_csv') {
            $this->csvExporter->handleExport();
            return;
        }

        // Duplicate Campaign
        if ($page === 'wpcal-popups' && $action === 'duplicate') {
            if (!current_user_can(CapabilityManager::CAP_MANAGE_POPUPS)) {
                wp_die(esc_html__('Permission denied.', 'wpcalibrate-popups-lead-capture'), 403);
            }

            $campaignId = absint($_GET['campaign_id'] ?? 0);
            check_admin_referer('wpcal_duplicate_campaign_' . $campaignId);

            $duplicate = $this->campaignRepo->duplicate($campaignId);
            if ($duplicate) {
                wp_safe_redirect(add_query_arg([
                    'page'    => 'wpcal-popups',
                    'message' => 'duplicated',
                ], admin_url('admin.php')));
                exit;
            }
        }

        // Trash Campaign
        if ($page === 'wpcal-popups' && $action === 'trash') {
            if (!current_user_can(CapabilityManager::CAP_MANAGE_POPUPS)) {
                wp_die(esc_html__('Permission denied.', 'wpcalibrate-popups-lead-capture'), 403);
            }

            $campaignId = absint($_GET['campaign_id'] ?? 0);
            check_admin_referer('wpcal_trash_campaign_' . $campaignId);

            wp_trash_post($campaignId);
            wp_safe_redirect(add_query_arg([
                'page'    => 'wpcal-popups',
                'trashed' => 1,
            ], admin_url('admin.php')));
            exit;
        }

        // Bulk Trash Campaigns
        $bulkAction = sanitize_key($_REQUEST['action2'] ?? $action);
        if ($page === 'wpcal-popups' && $bulkAction === 'bulk_trash') {
            if (!current_user_can(CapabilityManager::CAP_MANAGE_POPUPS)) {
                wp_die(esc_html__('Permission denied.', 'wpcalibrate-popups-lead-capture'), 403);
            }

            check_admin_referer('bulk-campaigns');
            $campaignIds = array_map('absint', (array) ($_REQUEST['campaign_ids'] ?? []));
            $trashedCount = 0;

            foreach ($campaignIds as $cid) {
                if ($cid > 0 && wp_trash_post($cid)) {
                    $trashedCount++;
                }
            }

            wp_safe_redirect(add_query_arg([
                'page'    => 'wpcal-popups',
                'trashed' => $trashedCount,
            ], admin_url('admin.php')));
            exit;
        }


        // Delete Single Lead
        if ($page === 'wpcal-leads' && $action === 'delete') {
            if (!current_user_can(CapabilityManager::CAP_MANAGE_LEADS)) {
                wp_die(esc_html__('Permission denied.', 'wpcalibrate-popups-lead-capture'), 403);
            }

            $leadId = absint($_GET['lead_id'] ?? 0);
            check_admin_referer('wpcal_delete_lead_' . $leadId);

            $this->leadRepo->delete($leadId);
            wp_safe_redirect(add_query_arg(['page' => 'wpcal-leads', 'deleted' => 1], admin_url('admin.php')));
            exit;
        }

        // Save Settings
        if ($page === 'wpcal-settings' && $action === 'save_settings' && isset($_POST['wpcal_settings_nonce'])) {
            if (!current_user_can(CapabilityManager::CAP_MANAGE_SETTINGS)) {
                wp_die(esc_html__('Permission denied.', 'wpcalibrate-popups-lead-capture'), 403);
            }

            check_admin_referer('wpcal_save_settings', 'wpcal_settings_nonce');

            $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : [];
            SettingsRepository::save($settings);

            $tab = sanitize_key($_POST['current_tab'] ?? 'general');
            wp_safe_redirect(add_query_arg(['page' => 'wpcal-settings', 'tab' => $tab, 'updated' => 1], admin_url('admin.php')));
            exit;
        }

        // Clear Cache Tool
        if ($page === 'wpcal-settings' && $action === 'clear_cache' && isset($_POST['wpcal_settings_nonce'])) {
            if (!current_user_can(CapabilityManager::CAP_MANAGE_SETTINGS)) {
                wp_die(esc_html__('Permission denied.', 'wpcalibrate-popups-lead-capture'), 403);
            }

            check_admin_referer('wpcal_save_settings', 'wpcal_settings_nonce');

            wp_cache_delete('wpcal_active_campaigns', 'wpcal_popups');
            wp_safe_redirect(add_query_arg(['page' => 'wpcal-settings', 'tab' => 'tools', 'updated' => 1], admin_url('admin.php')));
            exit;
        }
    }

    public function renderOverviewPage(): void
    {
        if (!current_user_can(CapabilityManager::CAP_MANAGE_POPUPS)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcalibrate-popups-lead-capture'), 403);
        }

        include WPCAL_POPUPS_PATH . 'templates/admin/overview-page.php';
    }

    public function renderCampaignsPage(): void
    {
        if (!current_user_can(CapabilityManager::CAP_MANAGE_POPUPS)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcalibrate-popups-lead-capture'), 403);
        }

        $table = new CampaignListTable($this->campaignRepo, $this->analytics);
        $table->prepare_items();
        $stats = $this->analytics->getSummary(30);

        include WPCAL_POPUPS_PATH . 'templates/admin/campaigns-page.php';
    }

    public function renderLeadsPage(): void
    {
        if (!current_user_can(CapabilityManager::CAP_MANAGE_LEADS)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcalibrate-popups-lead-capture'), 403);
        }

        $action = sanitize_key($_GET['action'] ?? '');
        $singleLead = null;

        if ($action === 'view' && !empty($_GET['lead_id'])) {
            $singleLead = $this->leadRepo->get(absint($_GET['lead_id']));
        }

        $table = null;
        if (!$singleLead) {
            $table = new LeadListTable($this->leadRepo, $this->campaignRepo);
            $table->prepare_items();
        }

        include WPCAL_POPUPS_PATH . 'templates/admin/leads-page.php';
    }

    public function renderAnalyticsPage(): void
    {
        if (!current_user_can(CapabilityManager::CAP_MANAGE_POPUPS)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcalibrate-popups-lead-capture'), 403);
        }

        $selectedDays    = isset($_GET['days']) ? absint($_GET['days']) : 30;
        $selectedPopupId = isset($_GET['popup_id']) ? absint($_GET['popup_id']) : 0;

        $summary      = $this->analytics->getSummary($selectedDays, $selectedPopupId);
        $topCampaigns = $this->analytics->getTopCampaigns(5);

        // Fetch campaigns for dropdown filter
        $campaigns = $this->campaignRepo->getActiveCampaigns();
        $campaignOptions = [];
        foreach ($campaigns as $c) {
            $campaignOptions[$c->getId()] = $c->getTitle();
        }

        include WPCAL_POPUPS_PATH . 'templates/admin/analytics-page.php';
    }

    public function renderSettingsPage(): void
    {
        if (!current_user_can(CapabilityManager::CAP_MANAGE_SETTINGS)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcalibrate-popups-lead-capture'), 403);
        }

        $activeTab = sanitize_key($_GET['tab'] ?? 'general');
        $settings  = SettingsRepository::getAll();

        include WPCAL_POPUPS_PATH . 'templates/admin/settings-page.php';
    }
}
