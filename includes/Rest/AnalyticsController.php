<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Rest;

use WPCalibrate\Popups\Analytics\AnalyticsManager;
use WPCalibrate\Popups\Capabilities\CapabilityManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST controller for recording impression beacons and retrieving analytics reports.
 */
final class AnalyticsController
{
    private AnalyticsManager $analytics;

    public function __construct(AnalyticsManager $analytics)
    {
        $this->analytics = $analytics;
    }

    public function registerRoutes(): void
    {
        // Public impression beacon
        register_rest_route('wpcal-popups/v1', '/analytics/impression', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'handleImpression'],
            'permission_callback' => '__return_true',
            'args'                => [
                'popup_id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Protected stats query for admin dashboard
        register_rest_route('wpcal-popups/v1', '/analytics/stats', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'handleGetStats'],
            'permission_callback' => static function (): bool {
                return current_user_can(CapabilityManager::CAP_MANAGE_POPUPS) || current_user_can('manage_options');
            },
            'args'                => [
                'days'     => [
                    'required'          => false,
                    'type'              => 'integer',
                    'default'           => 30,
                    'sanitize_callback' => 'absint',
                ],
                'popup_id' => [
                    'required'          => false,
                    'type'              => 'integer',
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    public function handleImpression(WP_REST_Request $request): WP_REST_Response
    {
        $popupId = (int) $request->get_param('popup_id');
        if ($popupId > 0) {
            $this->analytics->recordImpression($popupId);
        }

        return new WP_REST_Response(['success' => true], 200);
    }

    public function handleGetStats(WP_REST_Request $request): WP_REST_Response
    {
        $days    = (int) $request->get_param('days');
        $popupId = (int) $request->get_param('popup_id');

        $summary = $this->analytics->getSummary($days, $popupId);
        $topCampaigns = $this->analytics->getTopCampaigns(5);

        return new WP_REST_Response([
            'success'       => true,
            'summary'       => $summary,
            'top_campaigns' => $topCampaigns,
        ], 200);
    }
}
