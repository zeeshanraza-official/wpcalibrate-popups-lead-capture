<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Rest;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Boots and coordinates REST API routes for WPCalibrate Popups.
 */
final class RestServer
{
    private SubmitLeadController $submitController;
    private AnalyticsController $analyticsController;

    public function __construct(
        SubmitLeadController $submitController,
        AnalyticsController $analyticsController
    ) {
        $this->submitController    = $submitController;
        $this->analyticsController = $analyticsController;
    }

    public function init(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        $this->submitController->registerRoutes();
        $this->analyticsController->registerRoutes();
    }
}
