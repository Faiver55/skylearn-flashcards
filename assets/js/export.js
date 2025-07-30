/**
 * SkyLearn Flashcards - Export & Import JavaScript
 * 
 * Premium feature functionality for bulk export and import
 *
 * @package    SkyLearn_Flashcards
 * @subpackage Assets/JS
 * @since      1.0.0
 */

(function($) {
    'use strict';

    /**
     * Export Management Class
     */
    class SkyLearnExportManager {
        constructor() {
            this.form = $('#export-form');
            this.statusContainer = $('#export-status');
            this.progressContainer = $('#export-progress');
            this.previewModal = $('#export-preview-modal');
            
            this.init();
        }

        /**
         * Initialize export manager
         */
        init() {
            this.bindEvents();
            this.initializeFileUpload();
            this.setupFormValidation();
        }

        /**
         * Bind all event listeners
         */
        bindEvents() {
            // Export type changes
            $('input[name="export_type"]').on('change', (e) => {
                this.handleExportTypeChange($(e.target).val());
            });

            // Selection controls
            $('.select-all').on('click', () => this.selectAllSets(true));
            $('.select-none').on('click', () => this.selectAllSets(false));

            // Search functionality
            $('.search-sets').on('input', (e) => this.handleSetSearch($(e.target).val()));

            // Quick export actions
            $('.quick-export').on('click', (e) => {
                this.handleQuickExport($(e.target).data('type'));
            });

            // Preview functionality
            $('#preview-export').on('click', () => this.showExportPreview());
            $('#proceed-with-export').on('click', () => this.proceedWithExport());

            // Form submission
            this.form.on('submit', (e) => this.handleFormSubmission(e));

            // Modal controls
            $('.modal-close').on('click', () => this.closeModal());
            $('.skylearn-modal').on('click', (e) => {
                if (e.target === e.currentTarget) {
                    this.closeModal();
                }
            });

            // Import functionality
            $('#import-file').on('change', (e) => this.handleFileSelection(e));
            $('.file-drop-zone').on('click', () => $('#import-file').click());

            // Drag and drop
            this.setupDragAndDrop();
        }

        /**
         * Handle export type changes
         */
        handleExportTypeChange(exportType) {
            const flashcardSelection = $('#flashcard-selection');
            const dateRangeSelection = $('#date-range-selection');
            const scormOption = $('#scorm-option');
            const includeStatsOption = $('#include-stats-option');

            // Reset visibility
            flashcardSelection.hide();
            dateRangeSelection.hide();
            scormOption.hide();
            includeStatsOption.hide();

            // Show relevant sections based on export type
            switch (exportType) {
                case 'flashcards':
                    flashcardSelection.show();
                    scormOption.show();
                    includeStatsOption.show();
                    break;
                case 'analytics':
                case 'progress':
                    dateRangeSelection.show();
                    break;
                case 'complete':
                    flashcardSelection.show();
                    dateRangeSelection.show();
                    scormOption.show();
                    includeStatsOption.show();
                    break;
            }

            // Animate the changes
            this.animateSection(flashcardSelection);
            this.animateSection(dateRangeSelection);
        }

        /**
         * Animate section appearance
         */
        animateSection(element) {
            if (element.is(':visible')) {
                element.addClass('fade-in');
                setTimeout(() => element.removeClass('fade-in'), 400);
            }
        }

        /**
         * Select or deselect all flashcard sets
         */
        selectAllSets(select) {
            $('input[name="selected_sets[]"]').prop('checked', select);
            this.updateSelectionCount();
        }

        /**
         * Update selection count display
         */
        updateSelectionCount() {
            const selectedCount = $('input[name="selected_sets[]"]:checked').length;
            const totalCount = $('input[name="selected_sets[]"]').length;
            
            // Update any selection counter if it exists
            $('.selection-count').text(`${selectedCount} of ${totalCount} selected`);
        }

        /**
         * Handle set search
         */
        handleSetSearch(searchTerm) {
            const term = searchTerm.toLowerCase().trim();
            
            $('.set-checkbox').each(function() {
                const setTitle = $(this).find('h4').text().toLowerCase();
                const isVisible = !term || setTitle.includes(term);
                $(this).toggle(isVisible);
            });

            // Show "no results" message if needed
            const visibleSets = $('.set-checkbox:visible').length;
            $('.no-search-results').remove();
            
            if (visibleSets === 0 && term) {
                $('.sets-selection-list').append(`
                    <div class="no-search-results text-center" style="padding: 20px;">
                        <p>No flashcard sets found matching "${term}"</p>
                    </div>
                `);
            }
        }

        /**
         * Handle quick export actions
         */
        handleQuickExport(type) {
            switch (type) {
                case 'all_sets':
                    $('input[name="export_type"][value="flashcards"]').prop('checked', true).trigger('change');
                    this.selectAllSets(true);
                    break;
                case 'recent_analytics':
                    $('input[name="export_type"][value="analytics"]').prop('checked', true).trigger('change');
                    break;
                case 'full_backup':
                    $('input[name="export_type"][value="complete"]').prop('checked', true).trigger('change');
                    this.selectAllSets(true);
                    break;
            }

            // Scroll to form
            $('html, body').animate({
                scrollTop: this.form.offset().top - 50
            }, 500);
        }

        /**
         * Show export preview modal
         */
        showExportPreview() {
            this.previewModal.show().addClass('fade-in');
            
            // Generate preview content
            setTimeout(() => {
                this.generatePreviewContent();
            }, 500);
        }

        /**
         * Generate preview content
         */
        generatePreviewContent() {
            const exportType = $('input[name="export_type"]:checked').val();
            const format = $('input[name="export_format"]:checked').val();
            const selectedSets = $('input[name="selected_sets[]"]:checked');
            
            let previewHtml = '<div class="preview-summary">';
            previewHtml += '<h4>Export Summary</h4>';
            previewHtml += `<p><strong>Type:</strong> ${this.formatExportType(exportType)}</p>`;
            previewHtml += `<p><strong>Format:</strong> ${format.toUpperCase()}</p>`;
            
            if (exportType === 'flashcards' || exportType === 'complete') {
                previewHtml += `<p><strong>Sets:</strong> ${selectedSets.length} selected</p>`;
                
                if (selectedSets.length > 0) {
                    previewHtml += '<p><strong>Selected Sets:</strong></p><ul>';
                    selectedSets.each(function() {
                        const setName = $(this).closest('.set-checkbox').find('h4').text();
                        previewHtml += `<li>${setName}</li>`;
                    });
                    previewHtml += '</ul>';
                }
            }
            
            if (exportType === 'analytics' || exportType === 'complete') {
                const dateFrom = $('#export-date-from').val();
                const dateTo = $('#export-date-to').val();
                previewHtml += `<p><strong>Date Range:</strong> ${dateFrom} to ${dateTo}</p>`;
            }
            
            previewHtml += `<p><strong>Estimated file size:</strong> ${this.estimateFileSize(exportType, selectedSets.length)} KB</p>`;
            previewHtml += '</div>';
            
            // Add sample output for CSV
            if (format === 'csv') {
                previewHtml += this.generateSampleOutput(exportType);
            }
            
            $('#preview-content').html(previewHtml);
        }

        /**
         * Format export type for display
         */
        formatExportType(type) {
            const types = {
                'flashcards': 'Flashcard Sets',
                'analytics': 'Analytics Data',
                'progress': 'User Progress',
                'complete': 'Complete Backup'
            };
            return types[type] || type;
        }

        /**
         * Estimate file size based on export type and content
         */
        estimateFileSize(exportType, setCount) {
            const baseSizes = {
                'flashcards': setCount * 2, // ~2KB per set
                'analytics': 50, // ~50KB for analytics
                'progress': 30, // ~30KB for progress
                'complete': setCount * 3 + 80 // All combined
            };
            return Math.max(1, Math.ceil(baseSizes[exportType] || 10));
        }

        /**
         * Generate sample output for preview
         */
        generateSampleOutput(exportType) {
            let sampleHtml = '<div class="preview-sample"><h4>Sample Output:</h4><pre>';
            
            switch (exportType) {
                case 'flashcards':
                    sampleHtml += 'Set ID,Set Title,Card Front,Card Back,Card Index\n';
                    sampleHtml += '1,"Sample Set","What is the capital of France?","Paris",0\n';
                    sampleHtml += '1,"Sample Set","What is 2+2?","4",1';
                    break;
                case 'analytics':
                    sampleHtml += 'ID,Set ID,User ID,Action,Time Spent,Accuracy,Created At\n';
                    sampleHtml += '1,1,123,"card_view",15,0.85,"2024-01-15 10:30:00"\n';
                    sampleHtml += '2,1,123,"card_answer",8,1.00,"2024-01-15 10:30:15"';
                    break;
                case 'progress':
                    sampleHtml += 'User ID,Set ID,Card Index,Status,Attempts,Mastery Level\n';
                    sampleHtml += '123,1,0,"completed",3,0.95\n';
                    sampleHtml += '123,1,1,"completed",1,1.00';
                    break;
                default:
                    sampleHtml += 'Export data will be displayed here...';
            }
            
            sampleHtml += '</pre></div>';
            return sampleHtml;
        }

        /**
         * Proceed with export from preview
         */
        proceedWithExport() {
            this.closeModal();
            this.form.trigger('submit');
        }

        /**
         * Close modal
         */
        closeModal() {
            $('.skylearn-modal').hide().removeClass('fade-in');
        }

        /**
         * Setup form validation
         */
        setupFormValidation() {
            // Add real-time validation
            $('input[name="selected_sets[]"]').on('change', () => {
                this.updateSelectionCount();
                this.validateForm();
            });

            $('input[name="export_type"]').on('change', () => {
                this.validateForm();
            });
        }

        /**
         * Validate form before submission
         */
        validateForm() {
            const exportType = $('input[name="export_type"]:checked').val();
            const selectedSets = $('input[name="selected_sets[]"]:checked');
            
            let isValid = true;
            let errorMessage = '';

            if ((exportType === 'flashcards' || exportType === 'complete') && selectedSets.length === 0) {
                isValid = false;
                errorMessage = 'Please select at least one flashcard set to export.';
            }

            // Update UI based on validation
            if (!isValid) {
                this.showValidationError(errorMessage);
            } else {
                this.clearValidationError();
            }

            return isValid;
        }

        /**
         * Show validation error
         */
        showValidationError(message) {
            $('.form-validation-error').remove();
            this.form.prepend(`
                <div class="form-validation-error notice notice-error" style="margin-bottom: 20px;">
                    <p>${message}</p>
                </div>
            `);
        }

        /**
         * Clear validation error
         */
        clearValidationError() {
            $('.form-validation-error').remove();
        }

        /**
         * Handle form submission
         */
        handleFormSubmission(e) {
            e.preventDefault();

            if (!this.validateForm()) {
                return false;
            }

            this.startExport();
        }

        /**
         * Start the export process
         */
        startExport() {
            // Show progress UI
            this.statusContainer.hide();
            this.progressContainer.show().addClass('slide-up');
            
            // Reset progress
            this.updateProgress(0, 'Initializing export...');

            // Collect form data
            const formData = this.collectFormData();

            // Start progress simulation
            this.simulateProgress();

            // Make AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => this.handleExportSuccess(response),
                error: (xhr, status, error) => this.handleExportError(error),
                timeout: 60000 // 60 second timeout
            });
        }

        /**
         * Collect form data for submission
         */
        collectFormData() {
            const exportType = $('input[name="export_type"]:checked').val();
            
            return {
                action: 'skylearn_bulk_export',
                export_type: exportType,
                format: $('input[name="export_format"]:checked').val(),
                items: $('input[name="selected_sets[]"]:checked').map(function() {
                    return $(this).val();
                }).get(),
                date_from: $('#export-date-from').val(),
                date_to: $('#export-date-to').val(),
                include_metadata: $('input[name="include_metadata"]').is(':checked'),
                include_statistics: $('input[name="include_statistics"]').is(':checked'),
                anonymize_data: $('input[name="anonymize_data"]').is(':checked'),
                nonce: $('input[name="nonce"]').val()
            };
        }

        /**
         * Simulate export progress
         */
        simulateProgress() {
            this.progress = 0;
            this.progressInterval = setInterval(() => {
                this.progress += Math.random() * 10;
                if (this.progress > 90) this.progress = 90;

                let message = 'Processing data...';
                if (this.progress > 30) message = 'Generating export file...';
                if (this.progress > 60) message = 'Finalizing export...';
                if (this.progress > 85) message = 'Almost done...';

                this.updateProgress(this.progress, message);
            }, 300);
        }

        /**
         * Update progress display
         */
        updateProgress(percentage, message) {
            const roundedPercentage = Math.round(percentage);
            $('.progress-fill').css('width', roundedPercentage + '%');
            $('.progress-percentage').text(roundedPercentage + '%');
            $('.current-step').text(message);
        }

        /**
         * Handle successful export
         */
        handleExportSuccess(response) {
            clearInterval(this.progressInterval);

            if (response.success) {
                this.updateProgress(100, 'Export completed!');
                
                // Trigger download
                setTimeout(() => {
                    this.downloadFile(response.data);
                    this.showSuccessMessage();
                }, 500);
            } else {
                this.handleExportError(response.data?.message || 'Unknown error occurred');
            }
        }

        /**
         * Handle export error
         */
        handleExportError(error) {
            clearInterval(this.progressInterval);
            
            this.progressContainer.hide();
            this.statusContainer.show().html(`
                <div class="status-error">
                    <span class="dashicons dashicons-warning"></span>
                    <p>Export failed: ${error}</p>
                    <small>Please try again or contact support if the problem persists.</small>
                </div>
            `);
        }

        /**
         * Download export file
         */
        downloadFile(data) {
            const mimeTypes = {
                'csv': 'text/csv',
                'json': 'application/json',
                'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            };

            const blob = new Blob([data.data], { 
                type: mimeTypes[data.format] || 'text/plain'
            });

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = data.filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        /**
         * Show success message
         */
        showSuccessMessage() {
            setTimeout(() => {
                this.progressContainer.hide();
                this.statusContainer.show().html(`
                    <div class="status-complete">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <p>Export completed successfully!</p>
                        <small>Your export file has been downloaded.</small>
                    </div>
                `);

                // Reset to idle after a few seconds
                setTimeout(() => {
                    this.resetToIdle();
                }, 3000);
            }, 1000);
        }

        /**
         * Reset UI to idle state
         */
        resetToIdle() {
            this.statusContainer.html(`
                <div class="status-idle">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <p>Ready to export</p>
                    <small>Select your export options and click "Start Export"</small>
                </div>
            `);
        }

        /**
         * Initialize file upload for import
         */
        initializeFileUpload() {
            // File input change handler is already bound in bindEvents
        }

        /**
         * Setup drag and drop functionality
         */
        setupDragAndDrop() {
            const dropZone = $('.file-drop-zone');

            dropZone.on('dragover', (e) => {
                e.preventDefault();
                dropZone.addClass('dragover');
            });

            dropZone.on('dragleave', (e) => {
                e.preventDefault();
                dropZone.removeClass('dragover');
            });

            dropZone.on('drop', (e) => {
                e.preventDefault();
                dropZone.removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    this.handleFileSelection({ target: { files: files } });
                }
            });
        }

        /**
         * Handle file selection for import
         */
        handleFileSelection(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            const allowedTypes = ['text/csv', 'application/json', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a CSV, JSON, or Excel file.');
                return;
            }

            // Show file info
            this.displayFileInfo(file);
            
            // Parse and preview file
            this.parseImportFile(file);
        }

        /**
         * Display selected file information
         */
        displayFileInfo(file) {
            const fileInfo = `
                <div class="selected-file-info">
                    <h4>Selected File</h4>
                    <p><strong>Name:</strong> ${file.name}</p>
                    <p><strong>Size:</strong> ${this.formatFileSize(file.size)}</p>
                    <p><strong>Type:</strong> ${file.type}</p>
                </div>
            `;
            
            $('.file-drop-zone').after(fileInfo);
        }

        /**
         * Format file size for display
         */
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        /**
         * Parse import file and show preview
         */
        parseImportFile(file) {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    let data;
                    const content = e.target.result;
                    
                    if (file.type === 'application/json') {
                        data = JSON.parse(content);
                    } else if (file.type === 'text/csv') {
                        data = this.parseCSV(content);
                    }
                    
                    this.showImportPreview(data, file.type);
                } catch (error) {
                    alert('Error parsing file: ' + error.message);
                }
            };
            
            reader.readAsText(file);
        }

        /**
         * Parse CSV content
         */
        parseCSV(content) {
            const lines = content.split('\n');
            const headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''));
            const data = [];
            
            for (let i = 1; i < lines.length; i++) {
                if (lines[i].trim()) {
                    const values = lines[i].split(',').map(v => v.trim().replace(/"/g, ''));
                    const row = {};
                    headers.forEach((header, index) => {
                        row[header] = values[index] || '';
                    });
                    data.push(row);
                }
            }
            
            return data;
        }

        /**
         * Show import preview
         */
        showImportPreview(data, fileType) {
            const previewHtml = `
                <div class="import-preview">
                    <h4>Import Preview</h4>
                    <div class="import-stats">
                        <div class="import-stat">
                            <span class="import-stat-number">${Array.isArray(data) ? data.length : Object.keys(data).length}</span>
                            <span class="import-stat-label">Records Found</span>
                        </div>
                        <div class="import-stat">
                            <span class="import-stat-number">${fileType.includes('csv') ? 'CSV' : 'JSON'}</span>
                            <span class="import-stat-label">File Format</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="button button-primary" id="start-import">
                            <span class="dashicons dashicons-upload"></span>
                            Start Import
                        </button>
                        <button type="button" class="button" id="cancel-import">
                            Cancel
                        </button>
                    </div>
                </div>
            `;
            
            $('.import-section .form-section').append(previewHtml);
            
            // Bind import actions
            $('#start-import').on('click', () => this.startImport(data));
            $('#cancel-import').on('click', () => this.cancelImport());
        }

        /**
         * Start import process
         */
        startImport(data) {
            // Implementation for import functionality
            console.log('Starting import with data:', data);
            alert('Import functionality is ready for implementation');
        }

        /**
         * Cancel import
         */
        cancelImport() {
            $('.import-preview').remove();
            $('.selected-file-info').remove();
            $('#import-file').val('');
        }
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Only initialize on export page
        if ($('#export-form').length > 0) {
            new SkyLearnExportManager();
            console.log('SkyLearn Export Manager initialized');
        }
    });

})(jQuery);