(function (wp) {
    'use strict';

    if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.components || !wp.data) {
        return;
    }

    var el = wp.element.createElement;
    var __ = wp.i18n.__;
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
    var useSelect = wp.data.useSelect;
    var useDispatch = wp.data.useDispatch;

    var PanelRow = wp.components.PanelRow;
    var TextControl = wp.components.TextControl;
    var SelectControl = wp.components.SelectControl;
    var RangeControl = wp.components.RangeControl;
    var ToggleControl = wp.components.ToggleControl;

    function CampaignSettingsComponent() {
        var postType = useSelect(function (select) {
            return select('core/editor').getCurrentPostType();
        }, []);

        var meta = useSelect(function (select) {
            return select('core/editor').getEditedPostAttribute('meta') || {};
        }, []);

        var editPost = useDispatch('core/editor').editPost;

        if (postType !== 'wpcal_popup') {
            return null;
        }

        var config = meta._wpcal_popup_config || {};
        var trigger = config.trigger || { type: 'time_delay', time_delay_ms: 3000, scroll_percentage: 50, click_selector: '' };
        var targeting = config.targeting || { mode: 'all', auth_state: 'all', post_types: ['page', 'post'], devices: ['desktop', 'tablet', 'mobile'] };
        var frequency = config.frequency || { rule: 'every_visit', days: 7, suppress_on_conversion: true };
        var appearance = config.appearance || { width: '580px', max_width: '92%', padding: '32px', border_radius: '12px', background_color: '#ffffff', text_color: '#1e293b', text_align: 'left', overlay_color: 'rgba(15, 23, 42, 0.65)', close_button_position: 'inside_top_right', entrance_animation: 'zoom_in', exit_animation: 'fade_out', close_on_overlay_click: true, lock_scroll: true, close_button_aria_label: 'Close popup' };
        var leadSettings = config.lead_settings || { close_on_submit: true, close_delay_ms: 2500, custom_success_message: 'Thank you! Your submission has been received.', redirect_url: '', notification_email: '' };

        function updateConfig(section, key, value) {
            var newConfig = Object.assign({}, config);
            if (typeof section === 'string' && section !== '') {
                newConfig[section] = Object.assign({}, newConfig[section] || {}, (function () {
                    var obj = {};
                    obj[key] = value;
                    return obj;
                })());
            } else {
                newConfig[key] = value;
            }

            editPost({
                meta: Object.assign({}, meta, { _wpcal_popup_config: newConfig })
            });
        }

        return el(wp.element.Fragment, null,
            // 1. Campaign Activation
            el(PluginDocumentSettingPanel, {
                name: 'wpcal-status-panel',
                title: __('Campaign Status & Switch', 'wpcalibrate-popups-lead-capture'),
                icon: 'controls-repeat'
            },
                el(ToggleControl, {
                    label: __('Enable Campaign', 'wpcalibrate-popups-lead-capture'),
                    help: __('When disabled, popup will not trigger on the frontend regardless of post status.', 'wpcalibrate-popups-lead-capture'),
                    checked: config.enabled !== false,
                    onChange: function (val) { updateConfig('', 'enabled', val); }
                })
            ),

            // 2. Trigger Configuration
            el(PluginDocumentSettingPanel, {
                name: 'wpcal-trigger-panel',
                title: __('Trigger Engine', 'wpcalibrate-popups-lead-capture'),
                icon: 'controls-play'
            },
                el(SelectControl, {
                    label: __('Display Trigger', 'wpcalibrate-popups-lead-capture'),
                    value: trigger.type || 'time_delay',
                    options: [
                        { label: __('Time Delay / Page Load', 'wpcalibrate-popups-lead-capture'), value: 'time_delay' },
                        { label: __('Scroll Percentage', 'wpcalibrate-popups-lead-capture'), value: 'scroll' },
                        { label: __('Click on CSS Selector', 'wpcalibrate-popups-lead-capture'), value: 'click' },
                        { label: __('Manual JavaScript API', 'wpcalibrate-popups-lead-capture'), value: 'manual' },
                        { label: __('Exit Intent (Pro)', 'wpcalibrate-popups-lead-capture'), value: 'exit_intent' },
                        { label: __('User Inactivity (Pro)', 'wpcalibrate-popups-lead-capture'), value: 'inactivity' },
                        { label: __('Page Views Count (Pro)', 'wpcalibrate-popups-lead-capture'), value: 'page_views' }
                    ],
                    onChange: function (val) { updateConfig('trigger', 'type', val); }
                }),
                trigger.type === 'time_delay' && el(RangeControl, {
                    label: __('Delay (Seconds)', 'wpcalibrate-popups-lead-capture'),
                    value: Math.round((trigger.time_delay_ms || 3000) / 1000),
                    min: 0,
                    max: 60,
                    onChange: function (val) { updateConfig('trigger', 'time_delay_ms', val * 1000); }
                }),
                trigger.type === 'scroll' && el(RangeControl, {
                    label: __('Scroll Depth (%)', 'wpcalibrate-popups-lead-capture'),
                    value: trigger.scroll_percentage || 50,
                    min: 1,
                    max: 100,
                    onChange: function (val) { updateConfig('trigger', 'scroll_percentage', val); }
                }),
                trigger.type === 'click' && el(TextControl, {
                    label: __('CSS Selector to Watch', 'wpcalibrate-popups-lead-capture'),
                    help: __('e.g., .open-newsletter-popup or #cta-button', 'wpcalibrate-popups-lead-capture'),
                    value: trigger.click_selector || '',
                    onChange: function (val) { updateConfig('trigger', 'click_selector', val); }
                }),
                trigger.type === 'inactivity' && el(RangeControl, {
                    label: __('Inactivity Seconds (Pro)', 'wpcalibrate-popups-lead-capture'),
                    value: trigger.inactivity_seconds || 15,
                    min: 3,
                    max: 300,
                    onChange: function (val) { updateConfig('trigger', 'inactivity_seconds', val); }
                }),
                trigger.type === 'page_views' && el(RangeControl, {
                    label: __('Page Views Before Trigger (Pro)', 'wpcalibrate-popups-lead-capture'),
                    value: trigger.page_views_count || 3,
                    min: 1,
                    max: 50,
                    onChange: function (val) { updateConfig('trigger', 'page_views_count', val); }
                })
            ),

            // 3. Targeting Configuration
            el(PluginDocumentSettingPanel, {
                name: 'wpcal-targeting-panel',
                title: __('Targeting Rules', 'wpcalibrate-popups-lead-capture'),
                icon: 'location'
            },
                el(SelectControl, {
                    label: __('Page Display Mode', 'wpcalibrate-popups-lead-capture'),
                    value: targeting.mode || 'all',
                    options: [
                        { label: __('Entire Website', 'wpcalibrate-popups-lead-capture'), value: 'all' },
                        { label: __('Specific Pages/Posts', 'wpcalibrate-popups-lead-capture'), value: 'specific' },
                        { label: __('Exclude Specific Pages/Posts', 'wpcalibrate-popups-lead-capture'), value: 'exclude' }
                    ],
                    onChange: function (val) { updateConfig('targeting', 'mode', val); }
                }),
                (targeting.mode === 'specific' || targeting.mode === 'exclude') && el(TextControl, {
                    label: __('Comma-separated Post/Page IDs', 'wpcalibrate-popups-lead-capture'),
                    help: __('Enter IDs, e.g. 12, 45, 108', 'wpcalibrate-popups-lead-capture'),
                    value: (targeting.page_ids || []).join(', '),
                    onChange: function (val) {
                        var ids = val.split(',').map(function (s) { return parseInt(s.trim(), 10); }).filter(function (n) { return !isNaN(n) && n > 0; });
                        updateConfig('targeting', 'page_ids', ids);
                    }
                }),
                el(SelectControl, {
                    label: __('User Logged-In Status', 'wpcalibrate-popups-lead-capture'),
                    value: targeting.auth_state || 'all',
                    options: [
                        { label: __('All Visitors', 'wpcalibrate-popups-lead-capture'), value: 'all' },
                        { label: __('Logged-In Users Only', 'wpcalibrate-popups-lead-capture'), value: 'logged_in' },
                        { label: __('Logged-Out Guests Only', 'wpcalibrate-popups-lead-capture'), value: 'logged_out' }
                    ],
                    onChange: function (val) { updateConfig('targeting', 'auth_state', val); }
                })
            ),

            // 4. Frequency Controls
            el(PluginDocumentSettingPanel, {
                name: 'wpcal-frequency-panel',
                title: __('Frequency Controls', 'wpcalibrate-popups-lead-capture'),
                icon: 'calendar-alt'
            },
                el(SelectControl, {
                    label: __('Display Frequency', 'wpcalibrate-popups-lead-capture'),
                    value: frequency.rule || 'every_visit',
                    options: [
                        { label: __('Every eligible visit', 'wpcalibrate-popups-lead-capture'), value: 'every_visit' },
                        { label: __('Once per browser session', 'wpcalibrate-popups-lead-capture'), value: 'once_per_session' },
                        { label: __('Once every X days', 'wpcalibrate-popups-lead-capture'), value: 'once_per_days' }
                    ],
                    onChange: function (val) { updateConfig('frequency', 'rule', val); }
                }),
                frequency.rule === 'once_per_days' && el(RangeControl, {
                    label: __('Days to wait before showing again', 'wpcalibrate-popups-lead-capture'),
                    value: frequency.days || 7,
                    min: 1,
                    max: 365,
                    onChange: function (val) { updateConfig('frequency', 'days', val); }
                }),
                el(ToggleControl, {
                    label: __('Stop showing after conversion', 'wpcalibrate-popups-lead-capture'),
                    help: __('Hide this popup permanently for visitors who submitted the lead form.', 'wpcalibrate-popups-lead-capture'),
                    checked: frequency.suppress_on_conversion !== false,
                    onChange: function (val) { updateConfig('frequency', 'suppress_on_conversion', val); }
                })
            ),

            // 5. Appearance Settings
            el(PluginDocumentSettingPanel, {
                name: 'wpcal-appearance-panel',
                title: __('Appearance & Styling', 'wpcalibrate-popups-lead-capture'),
                icon: 'art'
            },
                el(TextControl, {
                    label: __('Modal Width', 'wpcalibrate-popups-lead-capture'),
                    value: appearance.width || '580px',
                    onChange: function (val) { updateConfig('appearance', 'width', val); }
                }),
                el(TextControl, {
                    label: __('Padding', 'wpcalibrate-popups-lead-capture'),
                    value: appearance.padding || '32px',
                    onChange: function (val) { updateConfig('appearance', 'padding', val); }
                }),
                el(TextControl, {
                    label: __('Border Radius', 'wpcalibrate-popups-lead-capture'),
                    value: appearance.border_radius || '12px',
                    onChange: function (val) { updateConfig('appearance', 'border_radius', val); }
                }),
                el(TextControl, {
                    label: __('Background Color', 'wpcalibrate-popups-lead-capture'),
                    value: appearance.background_color || '#ffffff',
                    onChange: function (val) { updateConfig('appearance', 'background_color', val); }
                }),
                el(TextControl, {
                    label: __('Text Color', 'wpcalibrate-popups-lead-capture'),
                    value: appearance.text_color || '#1e293b',
                    onChange: function (val) { updateConfig('appearance', 'text_color', val); }
                }),
                el(TextControl, {
                    label: __('Overlay Background Color', 'wpcalibrate-popups-lead-capture'),
                    value: appearance.overlay_color || 'rgba(15, 23, 42, 0.65)',
                    onChange: function (val) { updateConfig('appearance', 'overlay_color', val); }
                }),
                el(SelectControl, {
                    label: __('Entrance Animation', 'wpcalibrate-popups-lead-capture'),
                    value: appearance.entrance_animation || 'zoom_in',
                    options: [
                        { label: __('Zoom In', 'wpcalibrate-popups-lead-capture'), value: 'zoom_in' },
                        { label: __('Fade In', 'wpcalibrate-popups-lead-capture'), value: 'fade_in' },
                        { label: __('Slide Down', 'wpcalibrate-popups-lead-capture'), value: 'slide_down' },
                        { label: __('Slide Up', 'wpcalibrate-popups-lead-capture'), value: 'slide_up' }
                    ],
                    onChange: function (val) { updateConfig('appearance', 'entrance_animation', val); }
                }),
                el(SelectControl, {
                    label: __('Close Button Position', 'wpcalibrate-popups-lead-capture'),
                    value: appearance.close_button_position || 'inside_top_right',
                    options: [
                        { label: __('Inside Top-Right', 'wpcalibrate-popups-lead-capture'), value: 'inside_top_right' },
                        { label: __('Outside Top-Right', 'wpcalibrate-popups-lead-capture'), value: 'outside_top_right' },
                        { label: __('Inside Top-Left', 'wpcalibrate-popups-lead-capture'), value: 'inside_top_left' }
                    ],
                    onChange: function (val) { updateConfig('appearance', 'close_button_position', val); }
                }),
                el(ToggleControl, {
                    label: __('Close on Overlay Click', 'wpcalibrate-popups-lead-capture'),
                    checked: appearance.close_on_overlay_click !== false,
                    onChange: function (val) { updateConfig('appearance', 'close_on_overlay_click', val); }
                }),
                el(ToggleControl, {
                    label: __('Lock Background Page Scroll', 'wpcalibrate-popups-lead-capture'),
                    checked: appearance.lock_scroll !== false,
                    onChange: function (val) { updateConfig('appearance', 'lock_scroll', val); }
                })
            ),

            // 6. Lead Handling & Completion
            el(PluginDocumentSettingPanel, {
                name: 'wpcal-lead-handling-panel',
                title: __('Lead Submission Handling', 'wpcalibrate-popups-lead-capture'),
                icon: 'email'
            },
                el(ToggleControl, {
                    label: __('Close Popup After Submission', 'wpcalibrate-popups-lead-capture'),
                    checked: leadSettings.close_on_submit !== false,
                    onChange: function (val) { updateConfig('lead_settings', 'close_on_submit', val); }
                }),
                leadSettings.close_on_submit !== false && el(RangeControl, {
                    label: __('Close Delay (Seconds)', 'wpcalibrate-popups-lead-capture'),
                    value: Math.round((leadSettings.close_delay_ms || 2500) / 1000),
                    min: 1,
                    max: 10,
                    onChange: function (val) { updateConfig('lead_settings', 'close_delay_ms', val * 1000); }
                }),
                el(TextControl, {
                    label: __('Redirect URL (Optional)', 'wpcalibrate-popups-lead-capture'),
                    help: __('Leave empty to display the on-screen success message.', 'wpcalibrate-popups-lead-capture'),
                    value: leadSettings.redirect_url || '',
                    onChange: function (val) { updateConfig('lead_settings', 'redirect_url', val); }
                }),
                el(TextControl, {
                    label: __('Custom Notification Recipient', 'wpcalibrate-popups-lead-capture'),
                    help: __('Email address to receive lead alerts for this campaign (overrides global default).', 'wpcalibrate-popups-lead-capture'),
                    value: leadSettings.notification_email || '',
                    onChange: function (val) { updateConfig('lead_settings', 'notification_email', val); }
                })
            )
        );
    }

    registerPlugin('wpcal-campaign-settings', {
        render: CampaignSettingsComponent
    });
})(window.wp);
