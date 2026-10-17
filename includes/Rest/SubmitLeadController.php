<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Rest;

use WPCalibrate\Popups\Analytics\AnalyticsManager;
use WPCalibrate\Popups\Campaigns\CampaignRepository;
use WPCalibrate\Popups\Leads\LeadModel;
use WPCalibrate\Popups\Leads\LeadRepository;
use WPCalibrate\Popups\Notifications\EmailNotifier;
use WPCalibrate\Popups\Security\RateLimiter;
use WPCalibrate\Popups\Support\Logger;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST controller for processing frontend lead capture form submissions.
 */
final class SubmitLeadController
{
    private LeadRepository $leadRepo;
    private CampaignRepository $campaignRepo;
    private AnalyticsManager $analytics;
    private EmailNotifier $notifier;

    public function __construct(
        LeadRepository $leadRepo,
        CampaignRepository $campaignRepo,
        AnalyticsManager $analytics,
        EmailNotifier $notifier
    ) {
        $this->leadRepo     = $leadRepo;
        $this->campaignRepo = $campaignRepo;
        $this->analytics    = $analytics;
        $this->notifier     = $notifier;
    }

    public function registerRoutes(): void
    {
        register_rest_route('wpcal-popups/v1', '/submit', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'handleSubmit'],
            'permission_callback' => [$this, 'checkPermission'],
            'args'                => [
                'popup_id'   => [
                    'required'          => true,
                    'type'              => 'integer',
                    'validate_callback' => static fn($param) => is_numeric($param) && (int) $param > 0,
                    'sanitize_callback' => 'absint',
                ],
                'email'      => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_email',
                ],
                'dedupe_key' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * Public submissions allowed with strict validation & rate limiting.
     */
    public function checkPermission(): bool
    {
        return true;
    }

    public function handleSubmit(WP_REST_Request $request): WP_REST_Response
    {
        // 1. Rate limiting check
        if (!RateLimiter::checkRateLimit('lead_submission')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Too many submissions. Please wait a moment before trying again.', 'wpcalibrate-popups-lead-capture'),
            ], 429);
        }

        // 2. Honeypot verification (catches automated spam bots)
        $honeypot = (string) $request->get_param('_wpcal_hp');
        if (!empty($honeypot)) {
            // Return generic user-facing response to reject bots cleanly
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Invalid submission detected.', 'wpcalibrate-popups-lead-capture'),
            ], 400);
        }

        // 3. Form render timestamp sanity check (reject submissions faster than 1 second)
        $timestamp = (int) $request->get_param('_wpcal_ts');
        if ($timestamp > 0 && (time() - $timestamp) < 1) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Submission rejected: Automated submission speed detected.', 'wpcalibrate-popups-lead-capture'),
            ], 400);
        }

        // 4. Nonce verification
        $nonce = (string) $request->get_param('_wpcal_nonce');
        if (!wp_verify_nonce($nonce, 'wpcal_lead_submit')) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Security verification failed. Please refresh the page.', 'wpcalibrate-popups-lead-capture'),
            ], 403);
        }

        $popupId = (int) $request->get_param('popup_id');
        $campaign = $this->campaignRepo->get($popupId);
        if (!$campaign) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('The requested campaign was not found.', 'wpcalibrate-popups-lead-capture'),
            ], 404);
        }

        // 5. Idempotency / Deduplication check
        $dedupeKey = (string) $request->get_param('dedupe_key');
        if (!empty($dedupeKey)) {
            $existingLead = $this->leadRepo->getByDedupeKey($dedupeKey);
            if ($existingLead) {
                // Return success for existing lead without re-inserting or re-notifying
                return new WP_REST_Response([
                    'success' => true,
                    'lead_id' => $existingLead->getId(),
                    'uuid'    => $existingLead->getUuid(),
                    'message' => __('Submission received.', 'wpcalibrate-popups-lead-capture'),
                ], 200);
            }
        }

        // 6. Data sanitization
        $email     = sanitize_email((string) $request->get_param('email'));
        $firstName = sanitize_text_field((string) $request->get_param('first_name'));
        $lastName  = sanitize_text_field((string) $request->get_param('last_name'));
        $phone     = sanitize_text_field((string) $request->get_param('phone'));
        $sourceUrl = esc_url_raw((string) $request->get_param('source_url'));

        // Validate email format if provided
        if ($email !== '' && !is_email($email)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Please provide a valid email address.', 'wpcalibrate-popups-lead-capture'),
            ], 400);
        }

        $rawFields = $request->get_param('fields');
        $cleanFields = [];
        if (is_array($rawFields)) {
            foreach ($rawFields as $k => $v) {
                $sanitizedKey = sanitize_key((string) $k);
                if (is_array($v)) {
                    $cleanFields[$sanitizedKey] = array_map('sanitize_text_field', $v);
                } else {
                    $cleanFields[$sanitizedKey] = sanitize_textarea_field((string) $v);
                }
            }
        }

        $rawConsent = $request->get_param('consent');
        $cleanConsent = [];
        if (is_array($rawConsent)) {
            foreach ($rawConsent as $k => $v) {
                $sanitizedKey = sanitize_key((string) $k);
                $cleanConsent[$sanitizedKey] = [
                    'checked' => !empty($v['checked']),
                    'text'    => sanitize_text_field($v['text'] ?? ''),
                    'time'    => current_time('mysql'),
                ];
            }
        }

        // 7. Assemble Lead entity
        $lead = new LeadModel();
        $lead->setPopupId($popupId);
        $lead->setEmail($email !== '' ? $email : null);
        $lead->setFirstName($firstName !== '' ? $firstName : null);
        $lead->setLastName($lastName !== '' ? $lastName : null);
        $lead->setPhone($phone !== '' ? $phone : null);
        $lead->setFields($cleanFields);
        $lead->setConsent($cleanConsent);
        $lead->setSourceUrl($sourceUrl !== '' ? $sourceUrl : null);
        $lead->setStatus('new');
        $lead->setDedupeKey($dedupeKey !== '' ? $dedupeKey : null);

        /**
         * Hook before lead is inserted.
         */
        do_action('wpcal_popups_before_lead_created', $lead, $campaign, $request);

        $leadId = $this->leadRepo->insert($lead);
        if ($leadId === 0) {
            Logger::error('Failed to insert lead into database.', ['popup_id' => $popupId]);
            return new WP_REST_Response([
                'success' => false,
                'message' => __('An error occurred while saving your submission. Please try again.', 'wpcalibrate-popups-lead-capture'),
            ], 500);
        }

        // 8. Record conversion in analytics atomically
        $this->analytics->recordConversion($popupId);

        /**
         * Action hook triggered after lead is successfully persisted.
         * Used by Pro generic webhooks and custom notification subscribers.
         *
         * @param LeadModel $lead Persisted lead model.
         * @param CampaignModel $campaign Associated campaign.
         */
        do_action('wpcal_popups_lead_created', $lead, $campaign);

        // 9. Dispatch email notification safely
        $this->notifier->notify($lead, $campaign);

        /**
         * Hook after all processing is complete.
         */
        do_action('wpcal_popups_after_lead_created', $lead, $campaign);

        return new WP_REST_Response([
            'success' => true,
            'lead_id' => $leadId,
            'uuid'    => $lead->getUuid(),
            'message' => __('Submission successfully received.', 'wpcalibrate-popups-lead-capture'),
        ], 200);
    }
}
