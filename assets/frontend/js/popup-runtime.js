/**
 * WPCalibrate Popups Frontend Runtime
 * Vanilla JavaScript modal engine, trigger system, frequency controller, focus trap, and lead submission handler.
 * WCAG 2.2 AA compliant. Zero jQuery dependency.
 */
(function (window, document) {
    'use strict';

    var storageAvailable = (function () {
        try {
            var testKey = '__wpcal_test__';
            window.localStorage.setItem(testKey, testKey);
            window.localStorage.removeItem(testKey);
            return true;
        } catch (e) {
            return false;
        }
    })();

    var sessionStorageAvailable = (function () {
        try {
            var testKey = '__wpcal_test__';
            window.sessionStorage.setItem(testKey, testKey);
            window.sessionStorage.removeItem(testKey);
            return true;
        } catch (e) {
            return false;
        }
    })();

    var PopupManager = {
        campaigns: {},
        activePopupId: null,
        previousActiveElement: null,
        recordedImpressions: {},

        init: function () {
            var self = this;
            var configData = window.wpcalPopupsData || {};
            var campaignList = configData.campaigns || [];

            campaignList.forEach(function (campaign) {
                self.campaigns[campaign.id] = campaign;
            });

            // Bind global triggers after DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    self.setupCampaigns();
                });
            } else {
                self.setupCampaigns();
            }

            self.bindGlobalEvents();
            self.setupFormSubmissions();
        },

        setupCampaigns: function () {
            var self = this;
            Object.keys(self.campaigns).forEach(function (id) {
                var campaign = self.campaigns[id];
                if (!self.isEligibleByFrequency(campaign)) {
                    return;
                }
                self.initTrigger(campaign);
            });
        },

        isEligibleByFrequency: function (campaign) {
            var id = campaign.id;
            var freq = campaign.frequency || {};

            // Check if user already converted and conversion suppression is on
            if (freq.suppress_on_conversion && storageAvailable) {
                var converted = window.localStorage.getItem('wpcal_popup_converted_' + id);
                if (converted === '1') {
                    return false;
                }
            }

            var rule = freq.rule || 'every_visit';

            if (rule === 'once_per_session' && sessionStorageAvailable) {
                if (window.sessionStorage.getItem('wpcal_popup_viewed_' + id)) {
                    return false;
                }
            } else if (rule === 'once_per_days' && storageAvailable) {
                var lastViewed = window.localStorage.getItem('wpcal_popup_viewed_' + id);
                if (lastViewed) {
                    var days = parseInt(freq.days || 7, 10);
                    var elapsedMs = Date.now() - parseInt(lastViewed, 10);
                    if (elapsedMs < days * 86400 * 1000) {
                        return false;
                    }
                }
            }

            return true;
        },

        recordFrequencyImpression: function (campaignId) {
            if (storageAvailable) {
                window.localStorage.setItem('wpcal_popup_viewed_' + campaignId, Date.now().toString());
            }
            if (sessionStorageAvailable) {
                window.sessionStorage.setItem('wpcal_popup_viewed_' + campaignId, '1');
            }
        },

        recordConversion: function (campaignId) {
            if (storageAvailable) {
                window.localStorage.setItem('wpcal_popup_converted_' + campaignId, '1');
            }
        },

        initTrigger: function (campaign) {
            var self = this;
            var trigger = campaign.trigger || {};
            var type = trigger.type || 'time_delay';

            switch (type) {
                case 'time_delay':
                    var delay = parseInt(trigger.time_delay_ms || 3000, 10);
                    setTimeout(function () {
                        self.open(campaign.id);
                    }, Math.max(0, delay));
                    break;

                case 'scroll':
                    var targetPercent = Math.min(100, Math.max(0, parseInt(trigger.scroll_percentage || 50, 10)));
                    var scrollFired = false;
                    var onScroll = function () {
                        if (scrollFired) return;
                        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                        if (docHeight <= 0) return;
                        var currentPercent = (scrollTop / docHeight) * 100;

                        if (currentPercent >= targetPercent) {
                            scrollFired = true;
                            window.removeEventListener('scroll', onScroll);
                            self.open(campaign.id);
                        }
                    };
                    window.addEventListener('scroll', onScroll, { passive: true });
                    break;

                case 'click':
                    var selector = trigger.click_selector;
                    if (!selector || typeof selector !== 'string') return;
                    document.addEventListener('click', function (e) {
                        try {
                            var target = e.target.closest(selector);
                            if (target) {
                                e.preventDefault();
                                self.open(campaign.id);
                            }
                        } catch (err) {
                            // Invalid selector; ignore safely without breaking runtime
                        }
                    });
                    break;

                case 'manual':
                    // Triggered solely via manual JS API: wpcalPopups.open(id)
                    break;

                default:
                    // Hook for Pro triggers (exit_intent, inactivity, page_views)
                    document.dispatchEvent(new CustomEvent('wpcal:init_pro_trigger', {
                        detail: { campaign: campaign, manager: self }
                    }));
                    break;
            }
        },

        open: function (popupId) {
            var overlay = document.getElementById('wpcal-popup-' + popupId);
            if (!overlay || overlay.classList.contains('is-active')) {
                return;
            }

            var campaign = this.campaigns[popupId];
            if (!campaign) {
                return;
            }

            this.previousActiveElement = document.activeElement;
            this.activePopupId = popupId;

            // Mark impression
            this.recordFrequencyImpression(popupId);
            this.sendAnalyticsImpression(popupId);

            overlay.setAttribute('aria-hidden', 'false');
            overlay.classList.add('is-active');

            if (campaign.appearance && campaign.appearance.lock_scroll !== false) {
                document.body.classList.add('wpcal-scroll-locked');
            }

            this.trapFocus(overlay);

            document.dispatchEvent(new CustomEvent('wpcal:popup:opened', { detail: { popupId: popupId } }));
        },

        close: function (popupId) {
            var id = popupId || this.activePopupId;
            if (!id) return;

            var overlay = document.getElementById('wpcal-popup-' + id);
            if (!overlay) return;

            overlay.classList.remove('is-active');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('wpcal-scroll-locked');

            if (this.previousActiveElement && typeof this.previousActiveElement.focus === 'function') {
                this.previousActiveElement.focus();
            }

            this.activePopupId = null;
            document.dispatchEvent(new CustomEvent('wpcal:popup:closed', { detail: { popupId: id } }));
        },

        trapFocus: function (modalElement) {
            var focusable = modalElement.querySelectorAll('button:not([disabled]), [href], input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
            if (focusable.length === 0) return;

            var first = focusable[0];
            var last = focusable[focusable.length - 1];

            // Focus first element with a small delay for animation completion
            setTimeout(function () {
                first.focus();
            }, 50);

            modalElement.onkeydown = function (e) {
                if (e.key === 'Tab') {
                    if (e.shiftKey) {
                        if (document.activeElement === first) {
                            e.preventDefault();
                            last.focus();
                        }
                    } else {
                        if (document.activeElement === last) {
                            e.preventDefault();
                            first.focus();
                        }
                    }
                }
            };
        },

        bindGlobalEvents: function () {
            var self = this;

            // Escape key closes active popup
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && self.activePopupId) {
                    self.close(self.activePopupId);
                }
            });

            // Overlay click to close
            document.addEventListener('click', function (e) {
                if (!self.activePopupId) return;
                var overlay = document.getElementById('wpcal-popup-' + self.activePopupId);
                if (!overlay) return;

                // Close button clicked
                if (e.target.closest('.wpcal-close-btn')) {
                    e.preventDefault();
                    self.close(self.activePopupId);
                    return;
                }

                // Clicked outside dialog content
                if (e.target === overlay) {
                    var campaign = self.campaigns[self.activePopupId];
                    if (campaign && campaign.appearance && campaign.appearance.close_on_overlay_click !== false) {
                        self.close(self.activePopupId);
                    }
                }
            });
        },

        setupFormSubmissions: function () {
            var self = this;

            document.addEventListener('submit', function (e) {
                var form = e.target.closest('.wpcal-lead-form');
                if (!form) return;

                e.preventDefault();

                var popupId = form.getAttribute('data-popup-id');
                var submitBtn = form.querySelector('.wpcal-submit-btn');
                var feedback = form.querySelector('.wpcal-form-feedback');

                // Validate required fields
                var isValid = true;
                var firstInvalidField = null;

                // Reset field errors
                var errorElements = form.querySelectorAll('.wpcal-field-error');
                errorElements.forEach(function (el) {
                    el.textContent = '';
                    el.classList.remove('is-visible');
                });

                var inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(function (input) {
                    if (input.type === 'hidden') return;

                    var isRequired = input.hasAttribute('required');
                    var val = input.value.trim();
                    var fieldGroup = input.closest('.wpcal-form-group');
                    var errorSpan = fieldGroup ? fieldGroup.querySelector('.wpcal-field-error') : null;

                    if (isRequired) {
                        if ((input.type === 'checkbox' || input.type === 'radio') && !input.checked) {
                            isValid = false;
                            if (errorSpan) {
                                errorSpan.textContent = 'This field is required.';
                                errorSpan.classList.add('is-visible');
                            }
                            if (!firstInvalidField) firstInvalidField = input;
                        } else if (val === '') {
                            isValid = false;
                            if (errorSpan) {
                                errorSpan.textContent = 'Please fill out this field.';
                                errorSpan.classList.add('is-visible');
                            }
                            if (!firstInvalidField) firstInvalidField = input;
                        }
                    }

                    // Email format validation
                    if (val !== '' && (input.type === 'email' || input.name === 'email')) {
                        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailPattern.test(val)) {
                            isValid = false;
                            if (errorSpan) {
                                errorSpan.textContent = 'Please enter a valid email address.';
                                errorSpan.classList.add('is-visible');
                            }
                            if (!firstInvalidField) firstInvalidField = input;
                        }
                    }
                });

                if (!isValid) {
                    if (firstInvalidField) firstInvalidField.focus();
                    return;
                }

                // Generate idempotency deduplication key
                var dedupeKey = 'wpcal_' + Date.now() + '_' + Math.random().toString(36).substring(2, 10);

                var formData = new FormData(form);
                var payload = {
                    popup_id: parseInt(popupId, 10),
                    _wpcal_hp: formData.get('_wpcal_hp') || '',
                    _wpcal_ts: formData.get('_wpcal_ts') || '',
                    _wpcal_nonce: formData.get('_wpcal_nonce') || '',
                    dedupe_key: dedupeKey,
                    email: formData.get('email') || '',
                    first_name: formData.get('first_name') || '',
                    last_name: formData.get('last_name') || '',
                    phone: formData.get('phone') || '',
                    fields: {},
                    consent: {},
                    source_url: window.location.href
                };

                // Populate custom fields and consents
                formData.forEach(function (val, key) {
                    if (key.startsWith('fields[')) {
                        var cleanKey = key.replace(/^fields\[|\]$/g, '');
                        payload.fields[cleanKey] = val;
                    } else if (key.startsWith('consent[')) {
                        var consentKey = key.replace(/^consent\[|\]$/g, '');
                        payload.consent[consentKey] = {
                            checked: true,
                            text: formData.get('consent_text[' + consentKey + ']') || ''
                        };
                    }
                });

                // UI Loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    var spinner = submitBtn.querySelector('.wpcal-btn-spinner');
                    if (spinner) spinner.style.display = 'inline-block';
                }
                if (feedback) {
                    feedback.className = 'wpcal-form-feedback';
                    feedback.textContent = '';
                }

                var configData = window.wpcalPopupsData || {};
                var endpoint = configData.restUrl ? configData.restUrl + 'wpcal-popups/v1/submit' : '/wp-json/wpcal-popups/v1/submit';

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': configData.restNonce || ''
                    },
                    body: JSON.stringify(payload)
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            var spinner = submitBtn.querySelector('.wpcal-btn-spinner');
                            if (spinner) spinner.style.display = 'none';
                        }

                        if (data && data.success) {
                            if (feedback) {
                                feedback.className = 'wpcal-form-feedback is-success';
                                feedback.textContent = form.getAttribute('data-success-msg') || 'Thank you! Your submission has been received.';
                            }

                            form.reset();
                            self.recordConversion(popupId);

                            var campaign = self.campaigns[popupId];
                            var leadSettings = campaign ? (campaign.lead_settings || {}) : {};

                            document.dispatchEvent(new CustomEvent('wpcal:popup:converted', {
                                detail: { popupId: popupId, leadId: data.lead_id }
                            }));

                            if (leadSettings.redirect_url) {
                                window.location.href = leadSettings.redirect_url;
                                return;
                            }

                            if (leadSettings.close_on_submit !== false) {
                                var closeDelay = parseInt(leadSettings.close_delay_ms || 2500, 10);
                                setTimeout(function () {
                                    self.close(popupId);
                                }, closeDelay);
                            }
                        } else {
                            if (feedback) {
                                feedback.className = 'wpcal-form-feedback is-error';
                                feedback.textContent = (data && data.message) ? data.message : 'Submission could not be completed. Please try again.';
                            }
                        }
                    })
                    .catch(function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            var spinner = submitBtn.querySelector('.wpcal-btn-spinner');
                            if (spinner) spinner.style.display = 'none';
                        }
                        if (feedback) {
                            feedback.className = 'wpcal-form-feedback is-error';
                            feedback.textContent = 'A network error occurred. Please check your connection.';
                        }
                    });
            });
        },

        sendAnalyticsImpression: function (popupId) {
            if (this.recordedImpressions[popupId]) return;
            this.recordedImpressions[popupId] = true;

            var configData = window.wpcalPopupsData || {};
            if (configData.isPreview) return;

            var endpoint = configData.restUrl ? configData.restUrl + 'wpcal-popups/v1/analytics/impression' : '/wp-json/wpcal-popups/v1/analytics/impression';

            if (navigator.sendBeacon) {
                var payload = new Blob([JSON.stringify({ popup_id: popupId })], { type: 'application/json' });
                navigator.sendBeacon(endpoint, payload);
            } else {
                fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ popup_id: popupId }),
                    keepalive: true
                }).catch(function () { /* Fail silently */ });
            }
        }
    };

    // Public Manual JavaScript API
    window.wpcalPopups = {
        open: function (id) {
            PopupManager.open(id);
        },
        close: function (id) {
            PopupManager.close(id);
        },
        getActive: function () {
            return PopupManager.activePopupId;
        }
    };

    PopupManager.init();
})(window, document);
