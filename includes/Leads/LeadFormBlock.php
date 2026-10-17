<?php

declare(strict_types=1);

namespace WPCalibrate\Popups\Leads;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers and renders the Lead Form Gutenberg block.
 */
final class LeadFormBlock
{
    public static function register(): void
    {
        $blockJsonPath = WPCAL_POPUPS_PATH . 'blocks/lead-form';

        register_block_type($blockJsonPath, [
            'render_callback' => [self::class, 'render'],
        ]);
    }

    /**
     * Render the lead form dynamically.
     *
     * @param array $attributes Block attributes.
     * @param string $content Block content.
     * @param \WP_Block|null $block Block instance.
     * @return string Rendered HTML.
     */
    public static function render(array $attributes, string $content = '', ?\WP_Block $block = null): string
    {
        global $post;
        $popupId = $post instanceof \WP_Post ? $post->ID : 0;

        $fields = isset($attributes['fields']) && is_array($attributes['fields']) ? $attributes['fields'] : [];
        if (empty($fields)) {
            return '';
        }

        $submitText      = esc_html($attributes['submitButtonText'] ?? __('Subscribe Now', 'wpcalibrate-popups-lead-capture'));
        $btnBgColor      = esc_attr($attributes['submitButtonColor'] ?? '#2563eb');
        $btnTextColor    = esc_attr($attributes['submitButtonTextColor'] ?? '#ffffff');
        $successMessage  = esc_attr($attributes['successMessage'] ?? __('Thank you! Your submission has been received.', 'wpcalibrate-popups-lead-capture'));

        $nonce = wp_create_nonce('wpcal_lead_submit');
        $timestamp = time();

        ob_start();
        ?>
        <form class="wpcal-lead-form" data-popup-id="<?php echo esc_attr((string) $popupId); ?>" data-success-msg="<?php echo $successMessage; ?>" novalidate>
            <!-- Honeypot & Anti-Spam Tokens -->
            <input type="text" name="_wpcal_hp" class="wpcal-hp-field" tabindex="-1" autocomplete="off" aria-hidden="true" value="" />
            <input type="hidden" name="_wpcal_ts" value="<?php echo esc_attr((string) $timestamp); ?>" />
            <input type="hidden" name="_wpcal_nonce" value="<?php echo esc_attr($nonce); ?>" />
            <input type="hidden" name="popup_id" value="<?php echo esc_attr((string) $popupId); ?>" />

            <?php
            foreach ($fields as $field) {
                self::renderField($field, $popupId);
            }
            ?>

            <div class="wpcal-form-actions">
                <button type="submit" class="wpcal-submit-btn" style="background-color: <?php echo $btnBgColor; ?>; color: <?php echo $btnTextColor; ?>;">
                    <span class="wpcal-btn-text"><?php echo $submitText; ?></span>
                    <span class="wpcal-btn-spinner" style="display: none; margin-left: 8px;">⏳</span>
                </button>
            </div>

            <div class="wpcal-form-feedback" role="alert" aria-live="polite"></div>
        </form>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Renders a single form field based on its configuration.
     */
    private static function renderField(array $field, int $popupId): void
    {
        $type        = sanitize_key($field['type'] ?? 'text');
        $key         = sanitize_key($field['key'] ?? ('field_' . wp_rand(100, 999)));
        $label       = esc_html($field['label'] ?? ucfirst($type));
        $placeholder = esc_attr($field['placeholder'] ?? '');
        $required    = !empty($field['required']);
        $helpText    = esc_html($field['helpText'] ?? '');
        $widthClass  = 'wpcal-width-' . (in_array($field['width'] ?? '', ['50', '33'], true) ? $field['width'] : '100');
        $fieldId     = sprintf('wpcal_%d_%s', $popupId, $key);
        $errorId     = sprintf('%s_error', $fieldId);

        if ($type === 'hidden') {
            $defaultValue = esc_attr($field['defaultValue'] ?? '');
            printf('<input type="hidden" name="fields[%s]" id="%s" value="%s">', esc_attr($key), esc_attr($fieldId), $defaultValue);
            return;
        }

        ?>
        <div class="wpcal-form-group <?php echo esc_attr($widthClass); ?>" data-field-type="<?php echo esc_attr($type); ?>" data-field-key="<?php echo esc_attr($key); ?>">
            <?php if ($type !== 'checkbox' && $type !== 'consent'): ?>
                <label for="<?php echo esc_attr($fieldId); ?>">
                    <?php echo $label; ?>
                    <?php if ($required): ?>
                        <span class="wpcal-required-asterisk" aria-hidden="true">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>

            <?php
            switch ($type) {
                case 'textarea':
                    ?>
                    <textarea
                        id="<?php echo esc_attr($fieldId); ?>"
                        name="fields[<?php echo esc_attr($key); ?>]"
                        placeholder="<?php echo $placeholder; ?>"
                        <?php echo $required ? 'required aria-required="true"' : ''; ?>
                        aria-describedby="<?php echo esc_attr($errorId); ?>"></textarea>
                    <?php
                    break;

                case 'select':
                    $options = self::parseOptions($field['options'] ?? '');
                    ?>
                    <select
                        id="<?php echo esc_attr($fieldId); ?>"
                        name="fields[<?php echo esc_attr($key); ?>]"
                        <?php echo $required ? 'required aria-required="true"' : ''; ?>
                        aria-describedby="<?php echo esc_attr($errorId); ?>">
                        <?php if (!empty($placeholder)): ?>
                            <option value=""><?php echo $placeholder; ?></option>
                        <?php endif; ?>
                        <?php foreach ($options as $optVal => $optLabel): ?>
                            <option value="<?php echo esc_attr($optVal); ?>"><?php echo esc_html($optLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                    break;

                case 'radio':
                    $options = self::parseOptions($field['options'] ?? '');
                    ?>
                    <div class="wpcal-radio-group" role="radiogroup" aria-labelledby="<?php echo esc_attr($fieldId); ?>_label">
                        <?php foreach ($options as $optVal => $optLabel): ?>
                            <label class="wpcal-radio-label">
                                <input
                                    type="radio"
                                    name="fields[<?php echo esc_attr($key); ?>]"
                                    value="<?php echo esc_attr($optVal); ?>"
                                    <?php echo $required ? 'required aria-required="true"' : ''; ?>>
                                <span><?php echo esc_html($optLabel); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;

                case 'checkbox_group':
                    $options = self::parseOptions($field['options'] ?? '');
                    ?>
                    <div class="wpcal-checkbox-group" role="group">
                        <?php foreach ($options as $optVal => $optLabel): ?>
                            <label class="wpcal-checkbox-label">
                                <input
                                    type="checkbox"
                                    name="fields[<?php echo esc_attr($key); ?>][]"
                                    value="<?php echo esc_attr($optVal); ?>">
                                <span><?php echo esc_html($optLabel); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;

                case 'single_checkbox':
                case 'checkbox':
                    ?>
                    <label class="wpcal-checkbox-label" for="<?php echo esc_attr($fieldId); ?>">
                        <input
                            type="checkbox"
                            id="<?php echo esc_attr($fieldId); ?>"
                            name="fields[<?php echo esc_attr($key); ?>]"
                            value="1"
                            <?php echo $required ? 'required aria-required="true"' : ''; ?>>
                        <span>
                            <?php echo $label; ?>
                            <?php if ($required): ?>
                                <span class="wpcal-required-asterisk" aria-hidden="true">*</span>
                            <?php endif; ?>
                        </span>
                    </label>
                    <?php
                    break;

                case 'consent':
                    ?>
                    <label class="wpcal-checkbox-label" for="<?php echo esc_attr($fieldId); ?>">
                        <input
                            type="checkbox"
                            id="<?php echo esc_attr($fieldId); ?>"
                            name="consent[<?php echo esc_attr($key); ?>]"
                            value="1"
                            <?php echo $required ? 'required aria-required="true"' : ''; ?>>
                        <span>
                            <?php echo $label; ?>
                            <?php if ($required): ?>
                                <span class="wpcal-required-asterisk" aria-hidden="true">*</span>
                            <?php endif; ?>
                        </span>
                    </label>
                    <input type="hidden" name="consent_text[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($label); ?>" />
                    <?php
                    break;

                case 'email':
                case 'tel':
                case 'number':
                case 'first_name':
                case 'last_name':
                case 'full_name':
                case 'text':
                default:
                    $inputType = match ($type) {
                        'email'  => 'email',
                        'tel'    => 'tel',
                        'number' => 'number',
                        default  => 'text',
                    };
                    $inputName = match ($type) {
                        'email'      => 'email',
                        'first_name' => 'first_name',
                        'last_name'  => 'last_name',
                        'tel'        => 'phone',
                        default      => "fields[{$key}]",
                    };
                    ?>
                    <input
                        type="<?php echo esc_attr($inputType); ?>"
                        id="<?php echo esc_attr($fieldId); ?>"
                        name="<?php echo esc_attr($inputName); ?>"
                        placeholder="<?php echo $placeholder; ?>"
                        <?php echo $required ? 'required aria-required="true"' : ''; ?>
                        aria-describedby="<?php echo esc_attr($errorId); ?>" />
                    <?php
                    break;
            }
            ?>

            <?php if (!empty($helpText)): ?>
                <span class="wpcal-form-help"><?php echo $helpText; ?></span>
            <?php endif; ?>

            <span id="<?php echo esc_attr($errorId); ?>" class="wpcal-field-error" role="alert" aria-live="assertive"></span>
        </div>
        <?php
    }

    /**
     * Parses newline-delimited options strings into key-value pairs.
     *
     * @return array<string, string>
     */
    private static function parseOptions(string $raw): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $raw)));
        $parsed = [];

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$val, $lbl] = explode(':', $line, 2);
                $parsed[trim($val)] = trim($lbl);
            } else {
                $parsed[$line] = $line;
            }
        }

        return $parsed;
    }
}
