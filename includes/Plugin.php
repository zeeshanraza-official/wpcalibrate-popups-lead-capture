<?php

declare(strict_types=1);

namespace WPCalibrate\Popups;

use WPCalibrate\Popups\Admin\AdminMenu;
use WPCalibrate\Popups\Analytics\AnalyticsManager;
use WPCalibrate\Popups\Campaigns\CampaignRepository;
use WPCalibrate\Popups\Frontend\FrontendRenderer;
use WPCalibrate\Popups\Leads\CsvExporter;
use WPCalibrate\Popups\Leads\LeadRepository;
use WPCalibrate\Popups\Notifications\EmailNotifier;
use WPCalibrate\Popups\Privacy\PrivacyPolicy;
use WPCalibrate\Popups\Privacy\RetentionCleaner;
use WPCalibrate\Popups\Rest\AnalyticsController;
use WPCalibrate\Popups\Rest\RestServer;
use WPCalibrate\Popups\Rest\SubmitLeadController;
use WPCalibrate\Popups\Rules\RuleRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Container for WPCalibrate Popups Free Core.
 */
final class Plugin
{
    private static ?self $instance = null;

    private CampaignRepository $campaignRepo;
    private LeadRepository $leadRepo;
    private AnalyticsManager $analytics;
    private RuleRegistry $ruleRegistry;
    private EmailNotifier $emailNotifier;
    private CsvExporter $csvExporter;
    private FrontendRenderer $frontendRenderer;
    private RestServer $restServer;
    private ?AdminMenu $adminMenu = null;
    private PrivacyPolicy $privacyPolicy;
    private RetentionCleaner $retentionCleaner;

    private function __construct()
    {
        $this->campaignRepo     = new CampaignRepository();
        $this->leadRepo         = new LeadRepository();
        $this->analytics        = new AnalyticsManager();
        $this->ruleRegistry     = new RuleRegistry();
        $this->emailNotifier    = new EmailNotifier($this->leadRepo);
        $this->csvExporter      = new CsvExporter($this->leadRepo, $this->campaignRepo);
        $this->frontendRenderer = new FrontendRenderer($this->campaignRepo, $this->ruleRegistry);

        $submitController       = new SubmitLeadController($this->leadRepo, $this->campaignRepo, $this->analytics, $this->emailNotifier);
        $analyticsController    = new AnalyticsController($this->analytics);
        $this->restServer       = new RestServer($submitController, $analyticsController);

        $this->privacyPolicy    = new PrivacyPolicy($this->leadRepo);
        $this->retentionCleaner = new RetentionCleaner($this->leadRepo);

        if (is_admin()) {
            $this->adminMenu = new AdminMenu(
                $this->campaignRepo,
                $this->leadRepo,
                $this->analytics,
                $this->csvExporter
            );
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getCampaignRepository(): CampaignRepository
    {
        return $this->campaignRepo;
    }

    public function getLeadRepository(): LeadRepository
    {
        return $this->leadRepo;
    }

    public function getAnalyticsManager(): AnalyticsManager
    {
        return $this->analytics;
    }

    public function getRuleRegistry(): RuleRegistry
    {
        return $this->ruleRegistry;
    }

    public function getEmailNotifier(): EmailNotifier
    {
        return $this->emailNotifier;
    }

    public function getFrontendRenderer(): FrontendRenderer
    {
        return $this->frontendRenderer;
    }

    public function getRestServer(): RestServer
    {
        return $this->restServer;
    }

    public function getAdminMenu(): ?AdminMenu
    {
        return $this->adminMenu;
    }

    public function getPrivacyPolicy(): PrivacyPolicy
    {
        return $this->privacyPolicy;
    }

    public function getRetentionCleaner(): RetentionCleaner
    {
        return $this->retentionCleaner;
    }
}
