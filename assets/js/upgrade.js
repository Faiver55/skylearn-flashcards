/**
 * SkyLearn Flashcards - Upgrade functionality
 *
 * @package SkyLearn_Flashcards
 * @since 1.0.0
 */

(function($) {
    'use strict';

    const SkyLearnUpgrade = {
        
        /**
         * Initialize upgrade functionality
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Dismiss upgrade notices
            $(document).on('click', '.skylearn-dismiss-notice', this.dismissNotice);
            
            // Track upgrade clicks
            $(document).on('click', 'a[href*="skyian.com/skylearn-flashcards/premium"]', this.trackUpgradeClick);
            
            // Handle license activation form
            $(document).on('submit', '#skylearn-license-form', this.handleLicenseForm);
            
            // Handle license activation button
            $(document).on('click', '.skylearn-activate-license', this.activateLicense);
            
            // Handle license deactivation button
            $(document).on('click', '.skylearn-deactivate-license', this.deactivateLicense);
            
            // Handle license check button
            $(document).on('click', '.skylearn-check-license', this.checkLicense);
        },

        /**
         * Dismiss upgrade notice
         */
        dismissNotice: function(e) {
            e.preventDefault();
            
            const $notice = $(this).closest('.skylearn-upgrade-notice');
            const noticeType = $(this).data('notice');
            
            $.ajax({
                url: skylearn_upgrade.ajax_url,
                type: 'POST',
                data: {
                    action: 'skylearn_dismiss_upgrade_notice',
                    notice: noticeType,
                    nonce: skylearn_upgrade.nonce
                },
                success: function() {
                    $notice.fadeOut();
                }
            });
        },

        /**
         * Track upgrade clicks for analytics
         */
        trackUpgradeClick: function(e) {
            const $link = $(this);
            const context = $link.closest('[data-feature]').data('feature') || 'general';
            
            // Track the click (if analytics are available)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'upgrade_click', {
                    'feature_context': context,
                    'link_text': $link.text().trim()
                });
            }
        },

        /**
         * Handle license form submission
         */
        handleLicenseForm: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $button = $form.find('.skylearn-activate-license');
            const licenseKey = $form.find('#skylearn-license-key').val().trim();
            
            if (!licenseKey) {
                SkyLearnUpgrade.showMessage('Please enter a license key.', 'error');
                return;
            }
            
            SkyLearnUpgrade.activateLicense.call($button[0]);
        },

        /**
         * Activate license
         */
        activateLicense: function(e) {
            if (e) e.preventDefault();
            
            const $button = $(this);
            const $input = $('#skylearn-license-key');
            const licenseKey = $input.val().trim();
            
            if (!licenseKey) {
                SkyLearnUpgrade.showMessage('Please enter a license key.', 'error');
                $input.focus();
                return;
            }
            
            // Update button state
            $button.prop('disabled', true).text('Activating...');
            
            $.ajax({
                url: skylearn_upgrade.ajax_url,
                type: 'POST',
                data: {
                    action: 'skylearn_activate_license',
                    license_key: licenseKey,
                    nonce: skylearn_upgrade.nonce
                },
                success: function(response) {
                    if (response.success) {
                        SkyLearnUpgrade.showMessage(response.data.message, 'success');
                        SkyLearnUpgrade.updateLicenseStatus('valid', response.data);
                        
                        // Reload page after successful activation
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        SkyLearnUpgrade.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    SkyLearnUpgrade.showMessage('Connection error. Please try again.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Activate License');
                }
            });
        },

        /**
         * Deactivate license
         */
        deactivateLicense: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to deactivate your license?')) {
                return;
            }
            
            const $button = $(this);
            
            // Update button state
            $button.prop('disabled', true).text('Deactivating...');
            
            $.ajax({
                url: skylearn_upgrade.ajax_url,
                type: 'POST',
                data: {
                    action: 'skylearn_deactivate_license',
                    nonce: skylearn_upgrade.nonce
                },
                success: function(response) {
                    if (response.success) {
                        SkyLearnUpgrade.showMessage(response.data.message, 'success');
                        SkyLearnUpgrade.updateLicenseStatus('inactive');
                        
                        // Reload page after successful deactivation
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        SkyLearnUpgrade.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    SkyLearnUpgrade.showMessage('Connection error. Please try again.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Deactivate License');
                }
            });
        },

        /**
         * Check license status
         */
        checkLicense: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            
            // Update button state
            $button.prop('disabled', true).text('Checking...');
            
            $.ajax({
                url: skylearn_upgrade.ajax_url,
                type: 'POST',
                data: {
                    action: 'skylearn_check_license',
                    nonce: skylearn_upgrade.nonce
                },
                success: function(response) {
                    if (response.success) {
                        SkyLearnUpgrade.showMessage(response.data.message, 'success');
                        SkyLearnUpgrade.updateLicenseStatus('valid', response.data);
                    } else {
                        SkyLearnUpgrade.showMessage(response.data.message, 'error');
                        SkyLearnUpgrade.updateLicenseStatus('invalid');
                    }
                },
                error: function() {
                    SkyLearnUpgrade.showMessage('Connection error. Please try again.', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Check License');
                }
            });
        },

        /**
         * Update license status display
         */
        updateLicenseStatus: function(status, data = {}) {
            const $statusElement = $('.skylearn-license-status');
            const $licenseInfo = $('.skylearn-license-info');
            
            // Update status indicator
            $statusElement
                .removeClass('status-valid status-invalid status-inactive')
                .addClass('status-' + status);
            
            // Update status text
            const statusTexts = {
                'valid': 'Active',
                'invalid': 'Invalid',
                'inactive': 'Inactive'
            };
            
            $statusElement.find('.status-text').text(statusTexts[status] || 'Unknown');
            
            // Update license info
            if (status === 'valid' && data.expires) {
                $licenseInfo
                    .show()
                    .find('.expires-date')
                    .text(data.expires);
                
                if (data.license_type) {
                    $licenseInfo.find('.license-type').text(data.license_type);
                }
            } else {
                $licenseInfo.hide();
            }
            
            // Toggle form visibility
            if (status === 'valid') {
                $('.skylearn-license-form').hide();
                $('.skylearn-license-active').show();
            } else {
                $('.skylearn-license-form').show();
                $('.skylearn-license-active').hide();
            }
        },

        /**
         * Show notification message
         */
        showMessage: function(message, type = 'info') {
            // Remove existing messages
            $('.skylearn-message').remove();
            
            const messageClass = 'skylearn-message notice notice-' + type;
            const messageHtml = `
                <div class="${messageClass} is-dismissible">
                    <p>${message}</p>
                </div>
            `;
            
            // Insert message at the top of the page
            if ($('.wrap h1').length) {
                $('.wrap h1').after(messageHtml);
            } else {
                $('.wrap').prepend(messageHtml);
            }
            
            // Auto-dismiss success messages
            if (type === 'success') {
                setTimeout(() => {
                    $('.skylearn-message').fadeOut();
                }, 5000);
            }
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: $('.skylearn-message').offset().top - 50
            }, 500);
        },

        /**
         * Initialize premium overlays
         */
        initPremiumOverlays: function() {
            $('.skylearn-premium-feature').each(function() {
                const $feature = $(this);
                const featureName = $feature.data('feature');
                
                // Add overlay if not premium
                if (!$feature.hasClass('premium-active')) {
                    $feature.css('position', 'relative');
                    
                    const overlay = SkyLearnUpgrade.createPremiumOverlay(featureName);
                    $feature.append(overlay);
                }
            });
        },

        /**
         * Create premium overlay HTML
         */
        createPremiumOverlay: function(feature) {
            const upgradeUrl = `https://skyian.com/skylearn-flashcards/premium/?utm_source=plugin&utm_medium=premium-overlay&utm_campaign=skylearn-flashcards&utm_content=${feature}`;
            
            return `
                <div class="skylearn-premium-overlay">
                    <div class="skylearn-premium-overlay-content">
                        <span class="dashicons dashicons-lock"></span>
                        <h3>Premium Feature</h3>
                        <p>Upgrade to unlock this feature and more!</p>
                        <a href="${upgradeUrl}" class="button-primary" target="_blank">Upgrade Now</a>
                    </div>
                </div>
            `;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        SkyLearnUpgrade.init();
        SkyLearnUpgrade.initPremiumOverlays();
    });

    // Make available globally
    window.SkyLearnUpgrade = SkyLearnUpgrade;

})(jQuery);