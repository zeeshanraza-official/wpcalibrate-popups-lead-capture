<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Admin;

use WPCalibrate\Popups\Campaigns\CampaignRepository;
use WPCalibrate\Popups\Leads\LeadModel;
use WPCalibrate\Popups\Leads\LeadRepository;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\WP_List_Table') && file_exists(ABSPATH . 'wp-admin/includes/class-wp-list-table.php')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


/**
 * Isolated WP_List_Table implementation for managing leads.
 */
final class LeadListTable extends \WP_List_Table
{
    private LeadRepository $leadRepo;
    private CampaignRepository $campaignRepo;

    public function __construct(LeadRepository $leadRepo, CampaignRepository $campaignRepo)
    {
        parent::__construct([
            'singular' => 'lead',
            'plural'   => 'leads',
            'ajax'     => false,
        ]);

        $this->leadRepo     = $leadRepo;
        $this->campaignRepo = $campaignRepo;
    }

    public function get_columns(): array
    {
        return [
            'cb'         => '<input type="checkbox" />',
            'lead'       => __('Lead Details', 'wpcalibrate-popups-lead-capture'),
            'popup'      => __('Campaign', 'wpcalibrate-popups-lead-capture'),
            'phone'      => __('Phone', 'wpcalibrate-popups-lead-capture'),
            'status'     => __('Status', 'wpcalibrate-popups-lead-capture'),
            'created_at' => __('Date Submitted', 'wpcalibrate-popups-lead-capture'),
        ];
    }

    public function get_sortable_columns(): array
    {
        return [
            'lead'       => ['email', false],
            'created_at' => ['created_at', true],
            'status'     => ['status', false],
        ];
    }

    public function column_default($item, $column_name): string
    {
        return match ($column_name) {
            'popup'      => $this->column_popup($item),
            'phone'      => $this->column_phone($item),
            'status'     => $this->column_status($item),
            'created_at' => $this->column_created_at($item),
            default      => '',
        };
    }

    public function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="lead_ids[]" value="%d" />',
            absint($item->getId())
        );
    }

    public function column_lead(LeadModel $item): string
    {
        $fullName = esc_html($item->getFullName());
        $email    = esc_html($item->getEmail() ?? __('No email', 'wpcalibrate-popups-lead-capture'));

        $viewUrl = add_query_arg([
            'page'    => 'wpcal-leads',
            'action'  => 'view',
            'lead_id' => $item->getId(),
        ], admin_url('admin.php'));

        $deleteUrl = wp_nonce_url(
            add_query_arg([
                'page'    => 'wpcal-leads',
                'action'  => 'delete',
                'lead_id' => $item->getId(),
            ], admin_url('admin.php')),
            'wpcal_delete_lead_' . $item->getId()
        );

        $actions = [
            'view'   => sprintf('<a href="%s">%s</a>', esc_url($viewUrl), esc_html__('View Details', 'wpcalibrate-popups-lead-capture')),
            'delete' => sprintf('<a href="%s" onclick="return confirm(\'%s\');" style="color: #a00;">%s</a>',
                esc_url($deleteUrl),
                esc_js(__('Are you sure you want to delete this lead record?', 'wpcalibrate-popups-lead-capture')),
                esc_html__('Delete', 'wpcalibrate-popups-lead-capture')
            ),
        ];

        return sprintf(
            '<strong><a href="%s">%s</a></strong><br><span style="color: #64748b;">%s</span>%s',
            esc_url($viewUrl),
            $fullName,
            $email,
            $this->row_actions($actions)
        );
    }

    public function column_popup(LeadModel $item): string
    {
        $campaign = $this->campaignRepo->get($item->getPopupId());
        if (!$campaign) {
            return sprintf('<span style="color: #94a3b8;">%s</span>', esc_html__('Unknown / Deleted', 'wpcalibrate-popups-lead-capture'));
        }

        $editUrl = get_edit_post_link($campaign->getId());
        return sprintf('<strong><a href="%s">%s</a></strong>', esc_url($editUrl ?: '#'), esc_html($campaign->getTitle()));
    }

    public function column_phone(LeadModel $item): string
    {
        return $item->getPhone() ? esc_html($item->getPhone()) : '—';
    }

    public function column_status(LeadModel $item): string
    {
        $status = $item->getStatus();
        $badgeColors = [
            'new'       => 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
            'contacted' => 'background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;',
            'archived'  => 'background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;',
        ];
        $style = $badgeColors[$status] ?? 'background: #f1f5f9; color: #475569;';

        return sprintf(
            '<span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; %s">%s</span>',
            esc_attr($style),
            esc_html(ucfirst($status))
        );
    }

    public function column_created_at(LeadModel $item): string
    {
        return esc_html($item->getCreatedAt());
    }

    protected function get_bulk_actions(): array
    {
        return [
            'bulk_delete'    => __('Delete Permanently', 'wpcalibrate-popups-lead-capture'),
            'mark_contacted' => __('Mark as Contacted', 'wpcalibrate-popups-lead-capture'),
            'mark_archived'  => __('Mark as Archived', 'wpcalibrate-popups-lead-capture'),
        ];
    }

    public function prepare_items(): void
    {
        $perPage = 20;
        $currentPage = $this->get_pagenum();

        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'id';
        $order   = isset($_GET['order']) && strtoupper((string) $_GET['order']) === 'ASC' ? 'ASC' : 'DESC';
        $search  = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $popupId = isset($_GET['popup_id']) ? absint($_GET['popup_id']) : 0;
        $status  = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

        // Critical: Set column headers so WP_List_Table can render the table <thead>, <tbody>, and columns.
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable, 'lead'];

        $query = $this->leadRepo->queryForAdmin([
            'page'     => $currentPage,
            'per_page' => $perPage,
            'orderby'  => $orderby,
            'order'    => $order,
            'search'   => $search,
            'popup_id' => $popupId,
            'status'   => $status,
        ]);

        $this->items = $query['leads'];

        $this->set_pagination_args([
            'total_items' => $query['total'],
            'per_page'    => $perPage,
            'total_pages' => ceil($query['total'] / $perPage),
        ]);
    }
}

