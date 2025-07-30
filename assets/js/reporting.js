/**
 * SkyLearn Flashcards - Advanced Reporting JavaScript
 * ==================================================
 * 
 * Advanced reporting and analytics JavaScript for SkyLearn Flashcards Premium
 * 
 * @package SkyLearn_Flashcards
 * @subpackage Assets/JS
 * @author Ferdous Khalifa
 * @copyright 2024 Skyian LLC
 */

(function($) {
    'use strict';

    /**
     * Advanced Reporting functionality
     */
    const SkyLearnReporting = {
        
        // Chart instances
        dailyActivityChart: null,
        studyPatternChart: null,
        masteryChart: null,
        
        // Data cache
        dataCache: {},
        
        /**
         * Initialize reporting functionality
         */
        init: function() {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded. Charts will not be available.');
                return;
            }
            
            this.bindEvents();
            this.initCharts();
            this.initFilters();
            this.initExportHandlers();
            this.setupAutoRefresh();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Export functionality
            $(document).on('click', '#export-report', this.showExportModal);
            $(document).on('click', '#confirm-export', this.handleExport);
            $(document).on('click', '.modal-close', this.hideModal);
            
            // Data refresh
            $(document).on('click', '#refresh-data', this.refreshData);
            
            // Filter changes
            $(document).on('change', '#analytics-period', this.handlePeriodChange);
            $(document).on('change', '.report-filter', this.handleFilterChange);
            
            // Modal overlay clicks
            $(document).on('click', '.skylearn-modal', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
        },

        /**
         * Initialize charts
         */
        initCharts: function() {
            // Set Chart.js defaults
            Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            Chart.defaults.color = '#666';
            
            this.initDailyActivityChart();
            this.initStudyPatternChart();
            this.initMasteryChart();
        },

        /**
         * Initialize daily activity chart
         */
        initDailyActivityChart: function() {
            const ctx = document.getElementById('daily-activity-chart');
            if (!ctx) return;
            
            // Get data from the page (injected by PHP)
            const dailyData = window.skylernReportingData?.dailyStats || [];
            
            const labels = dailyData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            
            const viewsData = dailyData.map(item => parseInt(item.views) || 0);
            const completionsData = dailyData.map(item => parseInt(item.completions) || 0);
            
            this.dailyActivityChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: skylearn_admin.i18n.views || 'Views',
                        data: viewsData,
                        borderColor: '#3498db', // Primary color
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: skylearn_admin.i18n.completions || 'Completions',
                        data: completionsData,
                        borderColor: '#f39c12', // Accent color
                        backgroundColor: 'rgba(243, 156, 18, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#3498db',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        },

        /**
         * Initialize study pattern chart
         */
        initStudyPatternChart: function() {
            const ctx = document.getElementById('study-pattern-chart');
            if (!ctx || !window.skylernReportingData?.studyPatterns) return;
            
            const patternData = window.skylernReportingData.studyPatterns;
            const hourLabels = patternData.map(item => item.hour + ':00');
            const sessionData = patternData.map(item => parseInt(item.sessions) || 0);
            
            this.studyPatternChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: hourLabels,
                    datasets: [{
                        label: skylearn_admin.i18n.study_sessions || 'Study Sessions',
                        data: sessionData,
                        backgroundColor: 'rgba(52, 152, 219, 0.8)',
                        borderColor: '#3498db',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        },

        /**
         * Initialize mastery level chart
         */
        initMasteryChart: function() {
            const ctx = document.getElementById('mastery-chart');
            if (!ctx || !window.skylernReportingData?.masteryLevels) return;
            
            const masteryData = window.skylernReportingData.masteryLevels;
            const labels = masteryData.map(item => this.formatMasteryLevel(item.level));
            const counts = masteryData.map(item => parseInt(item.count) || 0);
            const colors = this.getMasteryColors();
            
            this.masteryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },

        /**
         * Initialize filters
         */
        initFilters: function() {
            // Date range picker initialization
            $('.date-range-picker').each(function() {
                // Initialize date picker if library is available
                if (typeof $.datepicker !== 'undefined') {
                    $(this).datepicker({
                        dateFormat: 'yy-mm-dd',
                        maxDate: 0, // Today
                        onSelect: function() {
                            SkyLearnReporting.handleFilterChange();
                        }
                    });
                }
            });
            
            // Select2 initialization for set/user filters
            if (typeof $.fn.select2 !== 'undefined') {
                $('.report-select').select2({
                    placeholder: skylearn_admin.i18n.select_option || 'Select an option',
                    allowClear: true
                });
            }
        },

        /**
         * Initialize export handlers
         */
        initExportHandlers: function() {
            // Add export format validation
            $('#export-format').on('change', function() {
                const format = $(this).val();
                const $typeSelect = $('#export-type');
                
                // Disable certain combinations if needed
                if (format === 'pdf' && $typeSelect.val() === 'raw-data') {
                    $typeSelect.val('overview');
                }
            });
        },

        /**
         * Setup auto-refresh functionality
         */
        setupAutoRefresh: function() {
            // Auto-refresh every 5 minutes if user is active
            let refreshInterval;
            const refreshTime = 5 * 60 * 1000; // 5 minutes
            
            const startAutoRefresh = () => {
                refreshInterval = setInterval(() => {
                    if (document.visibilityState === 'visible') {
                        this.refreshData(true); // Silent refresh
                    }
                }, refreshTime);
            };
            
            const stopAutoRefresh = () => {
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                    refreshInterval = null;
                }
            };
            
            // Start auto-refresh
            startAutoRefresh();
            
            // Stop/start based on page visibility
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    startAutoRefresh();
                } else {
                    stopAutoRefresh();
                }
            });
        },

        /**
         * Show export modal
         */
        showExportModal: function(e) {
            e.preventDefault();
            $('#export-modal').show();
            $('#export-type').focus();
        },

        /**
         * Hide modal
         */
        hideModal: function() {
            $('.skylearn-modal').hide();
        },

        /**
         * Handle data export
         */
        handleExport: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text(skylearn_admin.i18n.exporting || 'Exporting...');
            
            const formData = {
                action: 'skylearn_export_report',
                export_type: $('#export-type').val(),
                format: $('#export-format').val(),
                date_from: $('#export-date-from').val(),
                date_to: $('#export-date-to').val(),
                set_id: $('#export-set-filter').val(),
                nonce: skylearn_admin.nonces.export_report
            };
            
            $.post(ajaxurl, formData)
                .done(function(response) {
                    if (response.success) {
                        SkyLearnReporting.downloadFile(response.data);
                        SkyLearnReporting.hideModal();
                        SkyLearnReporting.showNotice(skylearn_admin.i18n.export_success || 'Export completed successfully!', 'success');
                    } else {
                        SkyLearnReporting.showNotice(response.data.message || 'Export failed.', 'error');
                    }
                })
                .fail(function() {
                    SkyLearnReporting.showNotice(skylearn_admin.i18n.export_error || 'Export request failed.', 'error');
                })
                .always(function() {
                    $button.prop('disabled', false).text(originalText);
                });
        },

        /**
         * Handle period change
         */
        handlePeriodChange: function() {
            // Reload page with new period
            const period = $('#analytics-period').val();
            const url = new URL(window.location);
            url.searchParams.set('period', period);
            window.location.href = url.toString();
        },

        /**
         * Handle filter changes
         */
        handleFilterChange: function() {
            // Collect all filter values
            const filters = {
                date_from: $('#filter-date-from').val(),
                date_to: $('#filter-date-to').val(),
                set_id: $('#filter-set').val(),
                user_id: $('#filter-user').val()
            };
            
            // Update charts with filtered data
            SkyLearnReporting.updateChartsWithFilters(filters);
        },

        /**
         * Refresh data
         */
        refreshData: function(silent = false) {
            if (!silent) {
                $('#refresh-data').prop('disabled', true);
                SkyLearnReporting.showNotice(skylearn_admin.i18n.refreshing || 'Refreshing data...', 'info');
            }
            
            const formData = {
                action: 'skylearn_get_report_data',
                report_type: 'overview',
                nonce: skylearn_admin.nonces.get_report_data
            };
            
            $.post(ajaxurl, formData)
                .done(function(response) {
                    if (response.success) {
                        // Update the page data
                        window.skylernReportingData = response.data;
                        SkyLearnReporting.updateCharts();
                        SkyLearnReporting.updateStatistics(response.data);
                        
                        if (!silent) {
                            SkyLearnReporting.showNotice(skylearn_admin.i18n.refresh_success || 'Data refreshed successfully!', 'success');
                        }
                    } else {
                        if (!silent) {
                            SkyLearnReporting.showNotice(response.data.message || 'Failed to refresh data.', 'error');
                        }
                    }
                })
                .fail(function() {
                    if (!silent) {
                        SkyLearnReporting.showNotice(skylearn_admin.i18n.refresh_error || 'Failed to refresh data.', 'error');
                    }
                })
                .always(function() {
                    $('#refresh-data').prop('disabled', false);
                });
        },

        /**
         * Update charts with new data
         */
        updateCharts: function() {
            if (this.dailyActivityChart) {
                this.updateDailyActivityChart();
            }
            if (this.studyPatternChart) {
                this.updateStudyPatternChart();
            }
            if (this.masteryChart) {
                this.updateMasteryChart();
            }
        },

        /**
         * Update daily activity chart
         */
        updateDailyActivityChart: function() {
            const dailyData = window.skylernReportingData?.dailyStats || [];
            const labels = dailyData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const viewsData = dailyData.map(item => parseInt(item.views) || 0);
            const completionsData = dailyData.map(item => parseInt(item.completions) || 0);
            
            this.dailyActivityChart.data.labels = labels;
            this.dailyActivityChart.data.datasets[0].data = viewsData;
            this.dailyActivityChart.data.datasets[1].data = completionsData;
            this.dailyActivityChart.update();
        },

        /**
         * Update statistics displays
         */
        updateStatistics: function(data) {
            const overview = data.overview || {};
            
            // Update overview cards
            $('.analytics-card h3').each(function(index) {
                const $card = $(this);
                const $parent = $card.closest('.analytics-card');
                const text = $parent.find('p').text().toLowerCase();
                
                if (text.includes('views')) {
                    $card.text(SkyLearnReporting.formatNumber(overview.total_views || 0));
                } else if (text.includes('completion')) {
                    if (text.includes('rate')) {
                        $card.text((overview.completion_rate || 0) + '%');
                    } else {
                        $card.text(SkyLearnReporting.formatNumber(overview.total_completions || 0));
                    }
                } else if (text.includes('accuracy')) {
                    $card.text((overview.average_accuracy || 0) + '%');
                }
            });
        },

        /**
         * Update charts with filters
         */
        updateChartsWithFilters: function(filters) {
            const formData = {
                action: 'skylearn_get_report_data',
                report_type: 'overview',
                ...filters,
                nonce: skylearn_admin.nonces.get_report_data
            };
            
            $.post(ajaxurl, formData)
                .done(function(response) {
                    if (response.success) {
                        window.skylernReportingData = response.data;
                        SkyLearnReporting.updateCharts();
                        SkyLearnReporting.updateStatistics(response.data);
                    }
                });
        },

        /**
         * Download exported file
         */
        downloadFile: function(fileData) {
            const blob = new Blob([fileData.content], { 
                type: fileData.mime_type || 'text/plain' 
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = fileData.filename || 'export.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        },

        /**
         * Show admin notice
         */
        showNotice: function(message, type = 'info') {
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.skylearn-admin-page').prepend($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Format mastery level for display
         */
        formatMasteryLevel: function(level) {
            const levels = {
                'mastered': skylearn_admin.i18n.mastered || 'Mastered',
                'good': skylearn_admin.i18n.good || 'Good',
                'learning': skylearn_admin.i18n.learning || 'Learning',
                'struggling': skylearn_admin.i18n.struggling || 'Struggling'
            };
            return levels[level] || level;
        },

        /**
         * Get mastery level colors
         */
        getMasteryColors: function() {
            return [
                '#27ae60', // Mastered - Green
                '#3498db', // Good - Blue
                '#f39c12', // Learning - Orange
                '#e74c3c'  // Struggling - Red
            ];
        },

        /**
         * Format numbers for display
         */
        formatNumber: function(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            } else if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.skylearn-premium-page').length) {
            SkyLearnReporting.init();
        }
    });

    // Make available globally for debugging
    window.SkyLearnReporting = SkyLearnReporting;

})(jQuery);