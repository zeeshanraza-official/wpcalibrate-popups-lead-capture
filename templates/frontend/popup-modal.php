<?php

declare(strict_types=1);

/**
 * Frontend Popup Modal Template.
 *
 * @var \WPCalibrate\Popups\Campaigns\CampaignModel $campaign
 */

if (!defined('ABSPATH')) {
    exit;
}

$id         = $campaign->getId();
$title      = $campaign->getTitle();
$appearance = $campaign->getAppearance();

$width      = esc_attr($appearance['width'] ?? '580px');
$maxWidth   = esc_attr($appearance['max_width'] ?? '92%');
$padding    = esc_attr($appearance['padding'] ?? '32px');
$radius     = esc_attr($appearance['border_radius'] ?? '12px');
$bgColor    = esc_attr($appearance['background_color'] ?? '#ffffff');
$textColor  = esc_attr($appearance['text_color'] ?? '#1e293b');
$overlayBg  = esc_attr($appearance['overlay_color'] ?? 'rgba(15, 23, 42, 0.65)');
$closePos   = esc_attr($appearance['close_button_position'] ?? 'inside_top_right');
$closeColor = esc_attr($appearance['close_button_color'] ?? '#64748b');
$closeAria  = esc_attr($appearance['close_button_aria_label'] ?? __('Close popup', 'wpcalibrate-popups-lead-capture'));
$vAlign     = esc_attr($appearance['vertical_align'] ?? 'center');
$entranceAnim = esc_attr($appearance['entrance_animation'] ?? 'zoom_in');
$textAlign  = esc_attr($appearance['text_align'] ?? 'left');

$customStyles = sprintf(
    '--wpcal-popup-width: %s; --wpcal-popup-max-width: %s; --wpcal-popup-padding: %s; --wpcal-popup-radius: %s; --wpcal-popup-bg: %s; --wpcal-popup-text: %s; --wpcal-popup-overlay: %s; --wpcal-popup-close-color: %s; text-align: %s;',
    $width,
    $maxWidth,
    $padding,
    $radius,
    $bgColor,
    $textColor,
    $overlayBg,
    $closeColor,
    $textAlign
);

$content = $campaign->getContent();
// Parse and render Gutenberg blocks inside popup
$renderedContent = do_blocks($content);
?>
<div
    id="wpcal-popup-<?php echo esc_attr((string) $id); ?>"
    class="wpcal-popup-overlay wpcal-valign-<?php echo $vAlign; ?>"
    role="dialog"
    aria-modal="true"
    aria-labelledby="wpcal-popup-title-<?php echo esc_attr((string) $id); ?>"
    aria-hidden="true"
    data-animation="<?php echo $entranceAnim; ?>"
    style="<?php echo esc_attr($customStyles); ?>">

    <div class="wpcal-popup-dialog">
        <button
            type="button"
            class="wpcal-close-btn wpcal-close-<?php echo $closePos; ?>"
            aria-label="<?php echo $closeAria; ?>"
            title="<?php echo $closeAria; ?>">
            <span aria-hidden="true">&times;</span>
        </button>

        <h2 id="wpcal-popup-title-<?php echo esc_attr((string) $id); ?>" class="screen-reader-text">
            <?php echo esc_html($title); ?>
        </h2>

        <div class="wpcal-popup-body">
            <?php echo $renderedContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
</div>
