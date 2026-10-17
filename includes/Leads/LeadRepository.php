<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Leads;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Data repository for storing, retrieving, querying, and pruning lead records.
 */
final class LeadRepository
{
    private string $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'wpcal_popup_leads';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Retrieve a lead by its primary database ID.
     */
    public function get(int $id): ?LeadModel
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->tableName} WHERE id = %d LIMIT 1", $id), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        );

        return $row ? $this->mapRowToModel($row) : null;
    }

    /**
     * Retrieve a lead by its public UUID.
     */
    public function getByUuid(string $uuid): ?LeadModel
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->tableName} WHERE uuid = %s LIMIT 1", $uuid), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        );

        return $row ? $this->mapRowToModel($row) : null;
    }

    /**
     * Check if a lead with the given deduplication idempotency key already exists.
     */
    public function getByDedupeKey(string $dedupeKey): ?LeadModel
    {
        global $wpdb;

        if (trim($dedupeKey) === '') {
            return null;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->tableName} WHERE dedupe_key = %s LIMIT 1", $dedupeKey), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        );

        return $row ? $this->mapRowToModel($row) : null;
    }

    /**
     * Insert a new lead into custom leads table.
     */
    public function insert(LeadModel $lead): int
    {
        global $wpdb;

        $now = current_time('mysql');
        $uuid = $lead->getUuid() !== '' ? $lead->getUuid() : wp_generate_uuid4();

        $data = [
            'uuid'                => $uuid,
            'popup_id'            => $lead->getPopupId(),
            'created_at'          => $now,
            'updated_at'          => $now,
            'email'               => $lead->getEmail(),
            'first_name'          => $lead->getFirstName(),
            'last_name'           => $lead->getLastName(),
            'phone'               => $lead->getPhone(),
            'fields'              => wp_json_encode($lead->getFields()),
            'consent'             => wp_json_encode($lead->getConsent()),
            'source_url'          => $lead->getSourceUrl(),
            'status'              => $lead->getStatus(),
            'notification_status' => $lead->getNotificationStatus(),
            'integration_status'  => $lead->getIntegrationStatus(),
            'dedupe_key'          => $lead->getDedupeKey(),
        ];

        $format = ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'];

        $result = $wpdb->insert($this->tableName, $data, $format);
        if ($result === false) {
            return 0;
        }

        $leadId = (int) $wpdb->insert_id;
        $lead->setId($leadId);
        $lead->setUuid($uuid);
        $lead->setCreatedAt($now);
        $lead->setUpdatedAt($now);

        return $leadId;
    }

    /**
     * Update an existing lead record.
     */
    public function update(LeadModel $lead): bool
    {
        global $wpdb;

        if ($lead->getId() <= 0) {
            return false;
        }

        $now = current_time('mysql');
        $data = [
            'status'              => $lead->getStatus(),
            'notification_status' => $lead->getNotificationStatus(),
            'integration_status'  => $lead->getIntegrationStatus(),
            'updated_at'          => $now,
        ];

        $result = $wpdb->update($this->tableName, $data, ['id' => $lead->getId()], ['%s', '%s', '%s', '%s'], ['%d']);
        return $result !== false;
    }

    /**
     * Delete a single lead.
     */
    public function delete(int $id): bool
    {
        global $wpdb;

        $result = $wpdb->delete($this->tableName, ['id' => $id], ['%d']);
        return $result !== false && $result > 0;
    }

    /**
     * Bulk delete leads by array of IDs.
     */
    public function bulkDelete(array $ids): int
    {
        global $wpdb;

        $cleanIds = array_filter(array_map('absint', $ids));
        if (empty($cleanIds)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '%d'));
        $sql = "DELETE FROM {$this->tableName} WHERE id IN ({$placeholders})";

        $result = $wpdb->query($wpdb->prepare($sql, ...$cleanIds)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return is_numeric($result) ? (int) $result : 0;
    }

    /**
     * Paginated and filtered query for admin table.
     *
     * @return array{leads: array<LeadModel>, total: int}
     */
    public function queryForAdmin(array $args = []): array
    {
        global $wpdb;

        $page    = max(1, absint($args['page'] ?? 1));
        $perPage = max(1, min(100, absint($args['per_page'] ?? 20)));
        $offset  = ($page - 1) * $perPage;

        $allowedOrderBy = ['id', 'created_at', 'email', 'status', 'popup_id'];
        $orderby = in_array($args['orderby'] ?? '', $allowedOrderBy, true) ? $args['orderby'] : 'id';
        $order   = (strtoupper((string) ($args['order'] ?? 'DESC')) === 'ASC') ? 'ASC' : 'DESC';

        $where = ['1=1'];
        $params = [];

        // Filter by popup ID
        if (!empty($args['popup_id'])) {
            $where[] = 'popup_id = %d';
            $params[] = absint($args['popup_id']);
        }

        // Filter by status
        if (!empty($args['status']) && $args['status'] !== 'all') {
            $where[] = 'status = %s';
            $params[] = sanitize_text_field($args['status']);
        }

        // Search term
        if (!empty($args['search'])) {
            $searchLike = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
            $where[] = '(email LIKE %s OR first_name LIKE %s OR last_name LIKE %s OR phone LIKE %s)';
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$whereClause}";
        $total = (int) $wpdb->get_var(!empty($params) ? $wpdb->prepare($countSql, $params) : $countSql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        // Get paginated rows
        $dataSql = "SELECT * FROM {$this->tableName} WHERE {$whereClause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $queryParams = array_merge($params, [$perPage, $offset]);

        $rows = $wpdb->get_results($wpdb->prepare($dataSql, $queryParams), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $leads = [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $leads[] = $this->mapRowToModel($row);
            }
        }

        return [
            'leads' => $leads,
            'total' => $total,
        ];
    }

    /**
     * Retrieve all leads matching criteria for CSV export.
     *
     * @return array<LeadModel>
     */
    public function getLeadsForExport(array $args = []): array
    {
        global $wpdb;

        $where = ['1=1'];
        $params = [];

        if (!empty($args['popup_id'])) {
            $where[] = 'popup_id = %d';
            $params[] = absint($args['popup_id']);
        }

        if (!empty($args['status']) && $args['status'] !== 'all') {
            $where[] = 'status = %s';
            $params[] = sanitize_text_field($args['status']);
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$this->tableName} WHERE {$whereClause} ORDER BY id DESC";

        $rows = !empty($params) ? $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A) : $wpdb->get_results($sql, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $leads = [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $leads[] = $this->mapRowToModel($row);
            }
        }

        return $leads;
    }

    /**
     * Retention policy: Delete leads older than X days.
     */
    public function deleteByRetentionDays(int $days): int
    {
        global $wpdb;

        if ($days <= 0) {
            return 0;
        }

        $threshold = gmdate('Y-m-d H:i:s', time() - ($days * 86400));
        $sql = "DELETE FROM {$this->tableName} WHERE created_at < %s";

        $result = $wpdb->query($wpdb->prepare($sql, $threshold)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return is_numeric($result) ? (int) $result : 0;
    }

    /**
     * Retrieve leads by visitor email (WP Privacy Tools Exporter).
     *
     * @return array<LeadModel>
     */
    public function getLeadsByEmail(string $email): array
    {
        global $wpdb;

        $cleanEmail = sanitize_email($email);
        if ($cleanEmail === '') {
            return [];
        }

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$this->tableName} WHERE email = %s", $cleanEmail), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        );

        $leads = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $leads[] = $this->mapRowToModel($row);
            }
        }

        return $leads;
    }

    /**
     * Delete leads by visitor email (WP Privacy Tools Eraser).
     */
    public function deleteLeadsByEmail(string $email): int
    {
        global $wpdb;

        $cleanEmail = sanitize_email($email);
        if ($cleanEmail === '') {
            return 0;
        }

        $result = $wpdb->query(
            $wpdb->prepare("DELETE FROM {$this->tableName} WHERE email = %s", $cleanEmail) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );

        return is_numeric($result) ? (int) $result : 0;
    }

    /**
     * Map database row array to LeadModel instance.
     */
    private function mapRowToModel(array $row): LeadModel
    {
        $model = new LeadModel((int) ($row['id'] ?? 0));
        $model->setUuid((string) ($row['uuid'] ?? ''));
        $model->setPopupId((int) ($row['popup_id'] ?? 0));
        $model->setCreatedAt((string) ($row['created_at'] ?? ''));
        $model->setUpdatedAt((string) ($row['updated_at'] ?? ''));
        $model->setEmail(!empty($row['email']) ? (string) $row['email'] : null);
        $model->setFirstName(!empty($row['first_name']) ? (string) $row['first_name'] : null);
        $model->setLastName(!empty($row['last_name']) ? (string) $row['last_name'] : null);
        $model->setPhone(!empty($row['phone']) ? (string) $row['phone'] : null);
        $model->setSourceUrl(!empty($row['source_url']) ? (string) $row['source_url'] : null);
        $model->setStatus((string) ($row['status'] ?? 'new'));
        $model->setNotificationStatus((string) ($row['notification_status'] ?? 'pending'));
        $model->setIntegrationStatus((string) ($row['integration_status'] ?? 'none'));
        $model->setDedupeKey(!empty($row['dedupe_key']) ? (string) $row['dedupe_key'] : null);

        $fields = !empty($row['fields']) ? json_decode((string) $row['fields'], true) : [];
        $model->setFields(is_array($fields) ? $fields : []);

        $consent = !empty($row['consent']) ? json_decode((string) $row['consent'], true) : [];
        $model->setConsent(is_array($consent) ? $consent : []);

        return $model;
    }
}
