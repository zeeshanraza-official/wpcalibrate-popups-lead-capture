<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Privacy;

use WPCalibrate\Popups\Leads\LeadRepository;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Privacy Policy disclosure and WordPress Personal Data Exporter / Eraser integrations.
 */
final class PrivacyPolicy
{
    private LeadRepository $leadRepo;

    public function __construct(LeadRepository $leadRepo)
    {
        $this->leadRepo = $leadRepo;
    }

    public function init(): void
    {
        add_action('admin_init', [$this, 'addPrivacyPolicyContent']);
        add_filter('wp_privacy_personal_data_exporters', [$this, 'registerDataExporter']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'registerDataEraser']);
    }

    /**
     * Add default suggested privacy policy content to WordPress.
     */
    public function addPrivacyPolicyContent(): void
    {
        if (!function_exists('wp_add_privacy_policy_content')) {
            return;
        }

        $content = sprintf(
            '<h2>%s</h2>' .
            '<p>%s</p>' .
            '<ul>' .
            '<li>%s</li>' .
            '<li>%s</li>' .
            '<li>%s</li>' .
            '<li>%s</li>' .
            '</ul>' .
            '<p>%s</p>',
            esc_html__('WPCalibrate Popups & Lead Capture', 'wpcalibrate-popups-lead-capture'),
            esc_html__('When you submit a lead-capture form on our website through a popup, we collect the personal information you enter into the form:', 'wpcalibrate-popups-lead-capture'),
            esc_html__('Your contact details such as name, email address, and telephone number.', 'wpcalibrate-popups-lead-capture'),
            esc_html__('Custom form responses and selections you submit.', 'wpcalibrate-popups-lead-capture'),
            esc_html__('The page URL where the submission took place and the timestamp.', 'wpcalibrate-popups-lead-capture'),
            esc_html__('Your consent record and the version of consent text accepted.', 'wpcalibrate-popups-lead-capture'),
            esc_html__('We do not collect raw IP addresses, passwords, payment details, or cross-site visitor tracking profiles. Data is retained according to our configured data retention schedule or until you request its deletion.', 'wpcalibrate-popups-lead-capture')
        );

        wp_add_privacy_policy_content(
            __('WPCalibrate Popups & Lead Capture', 'wpcalibrate-popups-lead-capture'),
            $content
        );
    }

    public function registerDataExporter(array $exporters): array
    {
        $exporters['wpcalibrate-popups'] = [
            'exporter_friendly_name' => __('WPCalibrate Popups Lead Submissions', 'wpcalibrate-popups-lead-capture'),
            'callback'               => [$this, 'exportPersonalData'],
        ];

        return $exporters;
    }

    public function exportPersonalData(string $emailAddress, int $page = 1): array
    {
        $leads = $this->leadRepo->getLeadsByEmail($emailAddress);
        $dataToExport = [];

        foreach ($leads as $lead) {
            $itemData = [
                ['name' => __('Submission ID', 'wpcalibrate-popups-lead-capture'), 'value' => $lead->getId()],
                ['name' => __('Date Submitted', 'wpcalibrate-popups-lead-capture'), 'value' => $lead->getCreatedAt()],
                ['name' => __('First Name', 'wpcalibrate-popups-lead-capture'), 'value' => $lead->getFirstName() ?? ''],
                ['name' => __('Last Name', 'wpcalibrate-popups-lead-capture'), 'value' => $lead->getLastName() ?? ''],
                ['name' => __('Email Address', 'wpcalibrate-popups-lead-capture'), 'value' => $lead->getEmail() ?? ''],
                ['name' => __('Phone', 'wpcalibrate-popups-lead-capture'), 'value' => $lead->getPhone() ?? ''],
                ['name' => __('Source Page', 'wpcalibrate-popups-lead-capture'), 'value' => $lead->getSourceUrl() ?? ''],
            ];

            $custom = $lead->getFields();
            if (!empty($custom)) {
                $itemData[] = ['name' => __('Custom Responses', 'wpcalibrate-popups-lead-capture'), 'value' => wp_json_encode($custom)];
            }

            $dataToExport[] = [
                'group_id'    => 'wpcal_leads',
                'group_label' => __('Popup Lead Submissions', 'wpcalibrate-popups-lead-capture'),
                'item_id'     => 'wpcal-lead-' . $lead->getId(),
                'data'        => $itemData,
            ];
        }

        return [
            'data' => $dataToExport,
            'done' => true,
        ];
    }

    public function registerDataEraser(array $erasers): array
    {
        $erasers['wpcalibrate-popups'] = [
            'eraser_friendly_name' => __('WPCalibrate Popups Lead Submissions', 'wpcalibrate-popups-lead-capture'),
            'callback'              => [$this, 'erasePersonalData'],
        ];

        return $erasers;
    }

    public function erasePersonalData(string $emailAddress, int $page = 1): array
    {
        $deletedCount = $this->leadRepo->deleteLeadsByEmail($emailAddress);

        return [
            'items_removed'  => $deletedCount > 0,
            'items_retained' => false,
            'messages'       => [
                sprintf(
                    /* translators: %d: number of leads deleted */
                    __('Removed %d lead submission(s) matching this email.', 'wpcalibrate-popups-lead-capture'),
                    $deletedCount
                ),
            ],
            'done'           => true,
        ];
    }
}
