/**
 * SkyLearn Flashcards - Admin JavaScript
 * =====================================
 * 
 * Admin interface JavaScript for SkyLearn Flashcards plugin
 * 
 * @package SkyLearn_Flashcards
 * @subpackage Assets/JS
 * @author Ferdous Khalifa
 * @copyright 2024 Skyian LLC
 */

(function($) {
    'use strict';

    /**
     * Admin functionality for SkyLearn Flashcards
     */
    const SkyLearnAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initColorPicker();
            this.initTabNavigation();
            this.initFormValidation();
            this.initAjaxHandlers();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Settings form submission
            $(document).on('submit', '.skylearn-settings-form', this.handleSettingsSubmit);
            
            // Flashcard editor events
            $(document).on('click', '.skylearn-add-card', this.addFlashcard);
            $(document).on('click', '.skylearn-remove-card', this.removeFlashcard);
            $(document).on('click', '.skylearn-duplicate-card', this.duplicateFlashcard);
            
            // Import/Export handlers
            $(document).on('click', '.skylearn-export-btn', this.handleExport);
            $(document).on('change', '.skylearn-import-file', this.handleImport);
            
            // Preview functionality
            $(document).on('click', '.skylearn-preview-btn', this.previewFlashcards);
            
            // Bulk actions
            $(document).on('change', '.skylearn-select-all', this.toggleSelectAll);
            $(document).on('click', '.skylearn-bulk-action', this.handleBulkAction);
        },

        /**
         * Initialize color picker for theme customization
         */
        initColorPicker: function() {
            if ($.fn.wpColorPicker) {
                $('.skylearn-color-picker').wpColorPicker({
                    change: function(event, ui) {
                        const element = event.target;
                        const color = ui.color.toString();
                        $(element).trigger('skylearn:colorChanged', [color]);
                    }
                });
            }
        },

        /**
         * Initialize tab navigation
         */
        initTabNavigation: function() {
            $('.skylearn-nav-tabs a').on('click', function(e) {
                e.preventDefault();
                
                const target = $(this).attr('href');
                
                // Update active tab
                $('.skylearn-nav-tabs a').removeClass('active');
                $(this).addClass('active');
                
                // Show/hide tab content
                $('.skylearn-tab-content').hide();
                $(target).show();
                
                // Save active tab to localStorage
                localStorage.setItem('skylearn_active_tab', target);
            });
            
            // Restore active tab
            const activeTab = localStorage.getItem('skylearn_active_tab');
            if (activeTab && $(activeTab).length) {
                $('.skylearn-nav-tabs a[href="' + activeTab + '"]').click();
            }
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            $('.skylearn-required').on('blur', function() {
                const $field = $(this);
                const value = $field.val().trim();
                
                if (value === '') {
                    $field.addClass('error');
                    $field.siblings('.skylearn-error-message').remove();
                    $field.after('<span class="skylearn-error-message">This field is required.</span>');
                } else {
                    $field.removeClass('error');
                    $field.siblings('.skylearn-error-message').remove();
                }
            });
        },

        /**
         * Initialize AJAX handlers
         */
        initAjaxHandlers: function() {
            // Set up AJAX defaults
            $.ajaxSetup({
                beforeSend: function() {
                    $('.skylearn-loading').show();
                },
                complete: function() {
                    $('.skylearn-loading').hide();
                },
                error: function(xhr, status, error) {
                    console.error('SkyLearn AJAX Error:', error);
                    this.showNotice('An error occurred. Please try again.', 'error');
                }.bind(this)
            });
        },

        /**
         * Handle settings form submission
         */
        handleSettingsSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const formData = new FormData($form[0]);
            formData.append('action', 'skylearn_save_settings');
            formData.append('nonce', skylearn_admin.nonce);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        SkyLearnAdmin.showNotice(response.data.message, 'success');
                    } else {
                        SkyLearnAdmin.showNotice(response.data.message, 'error');
                    }
                }
            });
        },

        /**
         * Add new flashcard to editor
         */
        addFlashcard: function(e) {
            e.preventDefault();
            
            const template = $('.skylearn-card-template').html();
            const cardCount = $('.skylearn-flashcard-item').length;
            const newCard = template.replace(/\[INDEX\]/g, cardCount);
            
            $('.skylearn-flashcards-container').append(newCard);
            
            // Focus on the new card's question field
            $('.skylearn-flashcard-item').last().find('.skylearn-question').focus();
        },

        /**
         * Remove flashcard from editor
         */
        removeFlashcard: function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove this flashcard?')) {
                $(this).closest('.skylearn-flashcard-item').remove();
                SkyLearnAdmin.updateCardIndexes();
            }
        },

        /**
         * Duplicate flashcard
         */
        duplicateFlashcard: function(e) {
            e.preventDefault();
            
            const $card = $(this).closest('.skylearn-flashcard-item');
            const $clone = $card.clone();
            
            // Clear IDs and update indexes
            $clone.find('[id]').each(function() {
                $(this).attr('id', '');
            });
            
            $card.after($clone);
            SkyLearnAdmin.updateCardIndexes();
        },

        /**
         * Update card indexes after add/remove operations
         */
        updateCardIndexes: function() {
            $('.skylearn-flashcard-item').each(function(index) {
                $(this).find('[name]').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        const newName = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        },

        /**
         * Handle export functionality
         */
        handleExport: function(e) {
            e.preventDefault();
            
            const format = $(this).data('format') || 'json';
            const setId = $(this).data('set-id');
            
            const params = {
                action: 'skylearn_export_flashcards',
                format: format,
                set_id: setId,
                nonce: skylearn_admin.nonce
            };
            
            window.location.href = ajaxurl + '?' + $.param(params);
        },

        /**
         * Handle import functionality
         */
        handleImport: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'skylearn_import_flashcards');
            formData.append('nonce', skylearn_admin.nonce);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        SkyLearnAdmin.showNotice('Flashcards imported successfully!', 'success');
                        location.reload();
                    } else {
                        SkyLearnAdmin.showNotice(response.data.message, 'error');
                    }
                }
            });
        },

        /**
         * Preview flashcards
         */
        previewFlashcards: function(e) {
            e.preventDefault();
            
            const setId = $(this).data('set-id');
            const previewUrl = skylearn_admin.preview_url.replace('SET_ID', setId);
            
            window.open(previewUrl, 'skylearn_preview', 'width=800,height=600,scrollbars=yes,resizable=yes');
        },

        /**
         * Toggle select all checkboxes
         */
        toggleSelectAll: function() {
            const isChecked = $(this).prop('checked');
            $('.skylearn-select-item').prop('checked', isChecked);
        },

        /**
         * Handle bulk actions
         */
        handleBulkAction: function(e) {
            e.preventDefault();
            
            const action = $('.skylearn-bulk-select').val();
            const selectedItems = $('.skylearn-select-item:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (!action || selectedItems.length === 0) {
                SkyLearnAdmin.showNotice('Please select an action and items.', 'warning');
                return;
            }
            
            if (!confirm('Are you sure you want to perform this action?')) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'skylearn_bulk_action',
                    bulk_action: action,
                    items: selectedItems,
                    nonce: skylearn_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        SkyLearnAdmin.showNotice(response.data.message, 'success');
                        location.reload();
                    } else {
                        SkyLearnAdmin.showNotice(response.data.message, 'error');
                    }
                }
            });
        },

        /**
         * Show admin notice
         */
        showNotice: function(message, type) {
            type = type || 'info';
            
            const notice = $('<div class="skylearn-notice skylearn-notice-' + type + '">')
                .html(message)
                .hide();
            
            $('.skylearn-admin-content').prepend(notice);
            notice.slideDown();
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(function() {
                    notice.slideUp(function() {
                        notice.remove();
                    });
                }, 5000);
            }
        },

        /**
         * Utility function to get URL parameter
         */
        getUrlParameter: function(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        SkyLearnAdmin.init();
    });

    // Make SkyLearnAdmin globally available
    window.SkyLearnAdmin = SkyLearnAdmin;

})(jQuery);

// Placeholder for future admin functionality