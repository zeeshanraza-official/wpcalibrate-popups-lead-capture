<?php
declare(strict_types=1);

/**
 * Reusable Admin Header & Navigation Component
 *
 * @var string $activeTab The slug of the current active top tab ('overview', 'campaigns', 'leads', 'analytics', 'settings')
 */

if (!defined('ABSPATH')) {
    exit;
}

$logoDarkPath = WPCAL_POPUPS_PATH . 'branding/icon-dark.png';
$logoDarkUrl  = file_exists($logoDarkPath) ? WPCAL_POPUPS_URL . 'branding/icon-dark.png' : '';
$isProActive  = defined('WPCAL_POPUPS_PRO_VERSION');
$newCampaignUrl = admin_url('post-new.php?post_type=wpcal_popup');

$navTabs = [
    'overview'  => [
        'label' => __('Overview', 'wpcalibrate-popups-lead-capture'),
        'url'   => admin_url('admin.php?page=wpcal-overview'),
    ],
    'campaigns' => [
        'label' => __('Campaigns', 'wpcalibrate-popups-lead-capture'),
        'url'   => admin_url('admin.php?page=wpcal-popups'),
    ],
    'leads'     => [
        'label' => __('Captured Leads', 'wpcalibrate-popups-lead-capture'),
        'url'   => admin_url('admin.php?page=wpcal-leads'),
    ],
    'analytics' => [
        'label' => __('Analytics', 'wpcalibrate-popups-lead-capture'),
        'url'   => admin_url('admin.php?page=wpcal-analytics'),
    ],
    'settings'  => [
        'label' => __('Settings', 'wpcalibrate-popups-lead-capture'),
        'url'   => admin_url('admin.php?page=wpcal-settings'),
    ],
];

?>
<div class="wpcal-header-bar">
    <div class="wpcal-header-left">
        <?php if ($logoDarkUrl !== ''): ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-overview')); ?>">
                <img src="<?php echo esc_url($logoDarkUrl); ?>" alt="<?php esc_attr_e('WPCalibrate', 'wpcalibrate-popups-lead-capture'); ?>" class="wpcal-brand-logo" />
            </a>
        <?php endif; ?>
        <div class="wpcal-header-title-group">
            <h1>
                <?php esc_html_e('Popups & Lead Capture', 'wpcalibrate-popups-lead-capture'); ?>
                <span class="wpcal-badge wpcal-badge-core">v<?php echo esc_html(WPCAL_POPUPS_VERSION); ?></span>
                <?php if ($isProActive): ?>
                    <span class="wpcal-badge wpcal-badge-pro"><?php esc_html_e('Pro Active', 'wpcalibrate-popups-lead-capture'); ?></span>
                <?php endif; ?>
            </h1>
            <p class="wpcal-header-subtitle">
                <?php esc_html_e('High-conversion modals, native Gutenberg block editing, granular targeting & privacy-first lead capture.', 'wpcalibrate-popups-lead-capture'); ?>
            </p>
        </div>
    </div>
    <div class="wpcal-header-right">
        <a href="<?php echo esc_url($newCampaignUrl); ?>" class="button button-primary">
            <?php esc_html_e('+ Create Campaign', 'wpcalibrate-popups-lead-capture'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wpcal-settings&tab=contact')); ?>" class="button button-secondary">
            <?php esc_html_e('Support', 'wpcalibrate-popups-lead-capture'); ?>
        </a>
    </div>
</div>

<nav class="wpcal-nav-tabs" aria-label="<?php esc_attr_e('Plugin Navigation', 'wpcalibrate-popups-lead-capture'); ?>">
    <?php foreach ($navTabs as $tabKey => $tab): ?>
        <a href="<?php echo esc_url($tab['url']); ?>"
           class="wpcal-nav-tab <?php echo ($activeTab ?? 'overview') === $tabKey ? 'is-active' : ''; ?>">
            <?php echo esc_html($tab['label']); ?>
        </a>
    <?php endforeach; ?>
</nav>
