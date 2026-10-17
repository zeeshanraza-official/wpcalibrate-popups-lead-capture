<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Leads;

use WPCalibrate\Popups\Campaigns\CampaignRepository;
use WPCalibrate\Popups\Capabilities\CapabilityManager;
use WPCalibrate\Popups\Security\FormulaSanitizer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Streams filtered lead records into a CSV export with formula injection protection.
 */
final class CsvExporter
{
    private LeadRepository $leadRepo;
    private CampaignRepository $campaignRepo;

    public function __construct(LeadRepository $leadRepo, CampaignRepository $campaignRepo)
    {
        $this->leadRepo     = $leadRepo;
        $this->campaignRepo = $campaignRepo;
    }

    /**
     * Handle CSV export request.
     */
    public function handleExport(): void
    {
        if (!current_user_can(CapabilityManager::CAP_EXPORT_LEADS) && !current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to export leads.', 'wpcalibrate-popups-lead-capture'), 403);
        }

        check_admin_referer('wpcal_export_leads');

        $popupId = isset($_GET['popup_id']) ? absint($_GET['popup_id']) : 0;
        $status  = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

        $leads = $this->leadRepo->getLeadsForExport([
            'popup_id' => $popupId,
            'status'   => $status,
        ]);

        $filename = sprintf('wpcal-leads-%s.csv', gmdate('Y-m-d-His'));

        // Prevent output buffering interference
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        if (!$output) {
            wp_die(esc_html__('Failed to open output stream for CSV export.', 'wpcalibrate-popups-lead-capture'), 500);
        }

        // Output UTF-8 BOM for Microsoft Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Header row
        $headers = [
            'ID',
            'UUID',
            'Campaign ID',
            'Campaign Title',
            'Created Date',
            'Email',
            'First Name',
            'Last Name',
            'Phone',
            'Status',
            'Source URL',
            'Custom Fields',
            'Consent Granted',
        ];

        fputcsv($output, FormulaSanitizer::sanitizeRow($headers));

        // Cache campaign titles to avoid querying repeatedly
        $campaignTitles = [];

        foreach ($leads as $lead) {
            $cId = $lead->getPopupId();
            if (!isset($campaignTitles[$cId])) {
                $c = $this->campaignRepo->get($cId);
                $campaignTitles[$cId] = $c ? $c->getTitle() : sprintf(__('Campaign #%d', 'wpcalibrate-popups-lead-capture'), $cId);
            }

            $customFields = $lead->getFields();
            $customFieldsStr = !empty($customFields) ? wp_json_encode($customFields) : '';

            $consent = $lead->getConsent();
            $consentStr = !empty($consent) ? wp_json_encode($consent) : 'No';

            $row = [
                (string) $lead->getId(),
                $lead->getUuid(),
                (string) $lead->getPopupId(),
                $campaignTitles[$cId],
                $lead->getCreatedAt(),
                $lead->getEmail() ?? '',
                $lead->getFirstName() ?? '',
                $lead->getLastName() ?? '',
                $lead->getPhone() ?? '',
                $lead->getStatus(),
                $lead->getSourceUrl() ?? '',
                $customFieldsStr,
                $consentStr,
            ];

            // Formula injection defense on all cells
            fputcsv($output, FormulaSanitizer::sanitizeRow($row));
        }

        fclose($output);
        exit;
    }
}
