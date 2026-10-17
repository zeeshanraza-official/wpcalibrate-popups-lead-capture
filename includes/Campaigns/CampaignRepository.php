<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Campaigns;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Repository for managing Campaign post entities and meta data.
 */
final class CampaignRepository
{
    /**
     * Retrieve a campaign by post ID.
     */
    public function get(int $id): ?CampaignModel
    {
        $post = get_post($id);
        if (!$post || $post->post_type !== CampaignPostType::POST_TYPE) {
            return null;
        }

        $model = new CampaignModel($post->ID);
        $model->setTitle($post->post_title);
        $model->setContent($post->post_content);
        $model->setStatus($post->post_status);

        $meta = get_post_meta($post->ID, CampaignPostType::META_KEY, true);
        if (is_array($meta)) {
            $model->loadFromConfigArray($meta);
        }

        return $model;
    }

    /**
     * Save a campaign model to the database.
     */
    public function save(CampaignModel $campaign): int
    {
        $postData = [
            'post_type'    => CampaignPostType::POST_TYPE,
            'post_title'   => $campaign->getTitle(),
            'post_content' => $campaign->getContent(),
            'post_status'  => $campaign->getStatus(),
        ];

        if ($campaign->getId() > 0) {
            $postData['ID'] = $campaign->getId();
            $postId = wp_update_post($postData);
        } else {
            $postId = wp_insert_post($postData);
        }

        if (is_wp_error($postId) || $postId === 0) {
            return 0;
        }

        $campaign->setId($postId);
        update_post_meta($postId, CampaignPostType::META_KEY, $campaign->toConfigArray());

        // Invalidate frontend campaign caches.
        wp_cache_delete('wpcal_active_campaigns', 'wpcal_popups');

        return $postId;
    }

    /**
     * Delete a campaign.
     */
    public function delete(int $id, bool $force = false): bool
    {
        $result = wp_delete_post($id, $force);
        if ($result) {
            wp_cache_delete('wpcal_active_campaigns', 'wpcal_popups');
            return true;
        }
        return false;
    }

    /**
     * Duplicate an existing campaign.
     */
    public function duplicate(int $id): ?CampaignModel
    {
        $original = $this->get($id);
        if (!$original) {
            return null;
        }

        $duplicate = new CampaignModel(0);
        $duplicate->setTitle(sprintf(
            /* translators: %s: original campaign title */
            __('%s (Copy)', 'wpcalibrate-popups-lead-capture'),
            $original->getTitle()
        ));
        $duplicate->setContent($original->getContent());
        $duplicate->setStatus('draft');
        $duplicate->setEnabled(false); // Duplicates start disabled
        $duplicate->setTrigger($original->getTrigger());
        $duplicate->setTargeting($original->getTargeting());
        $duplicate->setFrequency($original->getFrequency());
        $duplicate->setAppearance($original->getAppearance());
        $duplicate->setLeadSettings($original->getLeadSettings());
        $duplicate->setScheduling($original->getScheduling());
        $duplicate->setProSettings($original->getProSettings());

        $newId = $this->save($duplicate);
        if ($newId === 0) {
            return null;
        }

        return $this->get($newId);
    }

    /**
     * Retrieve all published, enabled campaigns for frontend evaluation.
     * Cached efficiently in WordPress object cache.
     *
     * @return array<CampaignModel>
     */
    public function getActiveCampaigns(): array
    {
        $cached = wp_cache_get('wpcal_active_campaigns', 'wpcal_popups');
        if (is_array($cached)) {
            return $cached;
        }

        $query = new \WP_Query([
            'post_type'              => CampaignPostType::POST_TYPE,
            'post_status'            => 'publish',
            'posts_per_page'         => 100,
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
        ]);

        $campaigns = [];
        foreach ($query->posts as $post) {
            $campaign = $this->get($post->ID);
            if ($campaign && $campaign->isEnabled()) {
                $campaigns[] = $campaign;
            }
        }

        wp_cache_set('wpcal_active_campaigns', $campaigns, 'wpcal_popups', 3600);

        return $campaigns;
    }

    /**
     * Query campaigns for admin list table with pagination, search, status filter.
     *
     * @return array{campaigns: array<CampaignModel>, total: int}
     */
    public function queryForAdmin(array $args = []): array
    {
        $page    = max(1, absint($args['page'] ?? 1));
        $perPage = max(1, min(100, absint($args['per_page'] ?? 20)));
        $status  = sanitize_key($args['status'] ?? 'all');
        $search  = sanitize_text_field($args['search'] ?? '');
        $orderby = in_array($args['orderby'] ?? '', ['title', 'date', 'ID'], true) ? $args['orderby'] : 'date';
        $order   = (strtoupper((string) ($args['order'] ?? 'DESC')) === 'ASC') ? 'ASC' : 'DESC';

        $queryArgs = [
            'post_type'      => CampaignPostType::POST_TYPE,
            'posts_per_page' => $perPage,
            'paged'          => $page,
            'orderby'        => $orderby,
            'order'          => $order,
        ];

        if ($status !== 'all' && !empty($status)) {
            $queryArgs['post_status'] = $status;
        } else {
            $queryArgs['post_status'] = ['publish', 'draft', 'pending', 'private'];
        }

        if ($search !== '') {
            $queryArgs['s'] = $search;
        }

        $query = new \WP_Query($queryArgs);
        $campaigns = [];

        foreach ($query->posts as $post) {
            $campaign = $this->get($post->ID);
            if ($campaign) {
                $campaigns[] = $campaign;
            }
        }

        return [
            'campaigns' => $campaigns,
            'total'     => (int) $query->found_posts,
        ];
    }
}
