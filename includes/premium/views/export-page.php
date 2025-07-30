<?php
/**
 * Premium export functionality view
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium/views
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check if premium features are available
if ( ! skylearn_is_premium() ) {
	return;
}

/**
 * Get export data for display
 */
$export_instance = new SkyLearn_Flashcards_Export( 'skylearn-flashcards', '1.0.0' );
$available_sets = $export_instance->get_available_sets();
$export_history = $export_instance->get_export_history();
?>

<div class="wrap skylearn-admin-page skylearn-premium-page">
    <div class="skylearn-header">
        <div class="skylearn-header-content">
            <img src="<?php echo esc_url( skylearn_get_logo_url( 'horizontal' ) ); ?>" 
                 alt="SkyLearn Flashcards" class="skylearn-logo">
            <h1>
                <?php esc_html_e( 'Bulk Export', 'skylearn-flashcards' ); ?>
                <span class="premium-badge"><?php esc_html_e( 'Premium', 'skylearn-flashcards' ); ?></span>
            </h1>
        </div>
    </div>

    <div class="skylearn-content">
        <div class="skylearn-grid">
            
            <!-- Export Configuration -->
            <div class="skylearn-panel two-thirds">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Export Configuration', 'skylearn-flashcards' ); ?></h2>
                </div>
                
                <form id="export-form" class="skylearn-export-form">
                    
                    <!-- Export Type Selection -->
                    <div class="form-section">
                        <h3><?php esc_html_e( 'What would you like to export?', 'skylearn-flashcards' ); ?></h3>
                        <div class="export-type-grid">
                            <label class="export-type-card">
                                <input type="radio" name="export_type" value="flashcards" checked>
                                <div class="card-content">
                                    <span class="dashicons dashicons-portfolio"></span>
                                    <h4><?php esc_html_e( 'Flashcard Sets', 'skylearn-flashcards' ); ?></h4>
                                    <p><?php esc_html_e( 'Export flashcard sets with questions and answers', 'skylearn-flashcards' ); ?></p>
                                </div>
                            </label>
                            
                            <label class="export-type-card">
                                <input type="radio" name="export_type" value="analytics">
                                <div class="card-content">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                    <h4><?php esc_html_e( 'Analytics Data', 'skylearn-flashcards' ); ?></h4>
                                    <p><?php esc_html_e( 'Export performance and usage analytics', 'skylearn-flashcards' ); ?></p>
                                </div>
                            </label>
                            
                            <label class="export-type-card">
                                <input type="radio" name="export_type" value="leads">
                                <div class="card-content">
                                    <span class="dashicons dashicons-groups"></span>
                                    <h4><?php esc_html_e( 'Lead Data', 'skylearn-flashcards' ); ?></h4>
                                    <p><?php esc_html_e( 'Export collected lead information', 'skylearn-flashcards' ); ?></p>
                                </div>
                            </label>
                            
                            <label class="export-type-card">
                                <input type="radio" name="export_type" value="complete">
                                <div class="card-content">
                                    <span class="dashicons dashicons-database-export"></span>
                                    <h4><?php esc_html_e( 'Complete Backup', 'skylearn-flashcards' ); ?></h4>
                                    <p><?php esc_html_e( 'Export everything for backup purposes', 'skylearn-flashcards' ); ?></p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Flashcard Sets Selection -->
                    <div class="form-section" id="flashcard-selection">
                        <h3><?php esc_html_e( 'Select Flashcard Sets', 'skylearn-flashcards' ); ?></h3>
                        <div class="selection-controls">
                            <button type="button" class="button select-all"><?php esc_html_e( 'Select All', 'skylearn-flashcards' ); ?></button>
                            <button type="button" class="button select-none"><?php esc_html_e( 'Select None', 'skylearn-flashcards' ); ?></button>
                            <input type="text" placeholder="<?php esc_attr_e( 'Search sets...', 'skylearn-flashcards' ); ?>" class="search-sets">
                        </div>
                        
                        <div class="sets-selection-list">
                            <?php if ( empty( $available_sets ) ) : ?>
                                <div class="no-sets-message">
                                    <span class="dashicons dashicons-portfolio"></span>
                                    <p><?php esc_html_e( 'No flashcard sets available for export.', 'skylearn-flashcards' ); ?></p>
                                </div>
                            <?php else : ?>
                                <!-- TODO: Loop through actual sets data -->
                                <label class="set-checkbox">
                                    <input type="checkbox" name="selected_sets[]" value="1" checked>
                                    <div class="set-info">
                                        <h4><?php esc_html_e( 'Sample Set 1', 'skylearn-flashcards' ); ?></h4>
                                        <p><?php esc_html_e( '15 cards • Created 2 days ago', 'skylearn-flashcards' ); ?></p>
                                    </div>
                                    <div class="set-stats">
                                        <span class="stat"><?php esc_html_e( '120 views', 'skylearn-flashcards' ); ?></span>
                                    </div>
                                </label>
                                
                                <label class="set-checkbox">
                                    <input type="checkbox" name="selected_sets[]" value="2">
                                    <div class="set-info">
                                        <h4><?php esc_html_e( 'Sample Set 2', 'skylearn-flashcards' ); ?></h4>
                                        <p><?php esc_html_e( '12 cards • Created 1 week ago', 'skylearn-flashcards' ); ?></p>
                                    </div>
                                    <div class="set-stats">
                                        <span class="stat"><?php esc_html_e( '98 views', 'skylearn-flashcards' ); ?></span>
                                    </div>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Date Range Selection -->
                    <div class="form-section" id="date-range-selection" style="display: none;">
                        <h3><?php esc_html_e( 'Date Range', 'skylearn-flashcards' ); ?></h3>
                        <div class="date-range-controls">
                            <div class="form-group">
                                <label for="export-date-from"><?php esc_html_e( 'From:', 'skylearn-flashcards' ); ?></label>
                                <input type="date" id="export-date-from" name="date_from" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="export-date-to"><?php esc_html_e( 'To:', 'skylearn-flashcards' ); ?></label>
                                <input type="date" id="export-date-to" name="date_to" class="form-input">
                            </div>
                        </div>
                    </div>

                    <!-- Export Format -->
                    <div class="form-section">
                        <h3><?php esc_html_e( 'Export Format', 'skylearn-flashcards' ); ?></h3>
                        <div class="format-options">
                            <label class="format-option">
                                <input type="radio" name="export_format" value="csv" checked>
                                <div class="format-content">
                                    <span class="dashicons dashicons-media-spreadsheet"></span>
                                    <span class="format-name">CSV</span>
                                    <small><?php esc_html_e( 'Comma-separated values', 'skylearn-flashcards' ); ?></small>
                                </div>
                            </label>
                            
                            <label class="format-option">
                                <input type="radio" name="export_format" value="json">
                                <div class="format-content">
                                    <span class="dashicons dashicons-media-code"></span>
                                    <span class="format-name">JSON</span>
                                    <small><?php esc_html_e( 'JavaScript Object Notation', 'skylearn-flashcards' ); ?></small>
                                </div>
                            </label>
                            
                            <label class="format-option">
                                <input type="radio" name="export_format" value="xlsx">
                                <div class="format-content">
                                    <span class="dashicons dashicons-media-spreadsheet"></span>
                                    <span class="format-name">XLSX</span>
                                    <small><?php esc_html_e( 'Excel spreadsheet', 'skylearn-flashcards' ); ?></small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Export Options -->
                    <div class="form-section">
                        <h3><?php esc_html_e( 'Export Options', 'skylearn-flashcards' ); ?></h3>
                        <div class="export-options">
                            <label class="checkbox-option">
                                <input type="checkbox" name="include_images" checked>
                                <span><?php esc_html_e( 'Include images and media files', 'skylearn-flashcards' ); ?></span>
                            </label>
                            
                            <label class="checkbox-option">
                                <input type="checkbox" name="include_metadata" checked>
                                <span><?php esc_html_e( 'Include metadata (creation dates, authors, etc.)', 'skylearn-flashcards' ); ?></span>
                            </label>
                            
                            <label class="checkbox-option">
                                <input type="checkbox" name="include_statistics">
                                <span><?php esc_html_e( 'Include performance statistics', 'skylearn-flashcards' ); ?></span>
                            </label>
                            
                            <label class="checkbox-option">
                                <input type="checkbox" name="compress_export">
                                <span><?php esc_html_e( 'Compress export file (ZIP)', 'skylearn-flashcards' ); ?></span>
                            </label>
                        </div>
                    </div>

                    <!-- Export Actions -->
                    <div class="form-actions">
                        <button type="submit" class="button button-primary button-large">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Start Export', 'skylearn-flashcards' ); ?>
                        </button>
                        <button type="button" class="button button-secondary" id="preview-export">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e( 'Preview Export', 'skylearn-flashcards' ); ?>
                        </button>
                    </div>

                    <!-- Hidden fields -->
                    <input type="hidden" name="action" value="skylearn_bulk_export">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'skylearn_export' ) ); ?>">
                </form>
            </div>

            <!-- Export History & Status -->
            <div class="skylearn-panel one-third">
                
                <!-- Export Status -->
                <div class="export-status-card">
                    <div class="status-header">
                        <h3><?php esc_html_e( 'Export Status', 'skylearn-flashcards' ); ?></h3>
                    </div>
                    <div class="status-content" id="export-status">
                        <div class="status-idle">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <p><?php esc_html_e( 'Ready to export', 'skylearn-flashcards' ); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Export History -->
                <div class="panel-header">
                    <h3><?php esc_html_e( 'Recent Exports', 'skylearn-flashcards' ); ?></h3>
                </div>
                
                <div class="export-history">
                    <?php if ( empty( $export_history ) ) : ?>
                        <div class="no-history">
                            <span class="dashicons dashicons-clock"></span>
                            <p><?php esc_html_e( 'No export history yet.', 'skylearn-flashcards' ); ?></p>
                        </div>
                    <?php else : ?>
                        <!-- TODO: Loop through actual export history -->
                        <div class="history-item">
                            <div class="item-icon success">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </div>
                            <div class="item-content">
                                <h4><?php esc_html_e( 'Flashcard Sets Export', 'skylearn-flashcards' ); ?></h4>
                                <p><?php esc_html_e( '3 sets • CSV format', 'skylearn-flashcards' ); ?></p>
                                <small><?php echo esc_html( human_time_diff( strtotime( '-2 hours' ) ) ); ?> ago</small>
                            </div>
                            <div class="item-actions">
                                <button type="button" class="button-link download-export"><?php esc_html_e( 'Download', 'skylearn-flashcards' ); ?></button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Export Limits -->
                <div class="export-limits">
                    <h4><?php esc_html_e( 'Export Limits', 'skylearn-flashcards' ); ?></h4>
                    <div class="limit-info">
                        <div class="limit-item">
                            <span class="limit-label"><?php esc_html_e( 'Monthly Exports:', 'skylearn-flashcards' ); ?></span>
                            <span class="limit-value">2 / 50</span>
                        </div>
                        <div class="limit-item">
                            <span class="limit-label"><?php esc_html_e( 'Max File Size:', 'skylearn-flashcards' ); ?></span>
                            <span class="limit-value">100 MB</span>
                        </div>
                        <div class="limit-item">
                            <span class="limit-label"><?php esc_html_e( 'Storage Used:', 'skylearn-flashcards' ); ?></span>
                            <span class="limit-value">12.5 MB</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // TODO: Implement export functionality
    console.log('Bulk Export page loaded');
    
    var exportForm = $('#export-form');
    var exportStatus = $('#export-status');
    
    // Handle export type changes
    $('input[name="export_type"]').on('change', function() {
        var exportType = $(this).val();
        
        // Show/hide relevant sections
        if (exportType === 'flashcards') {
            $('#flashcard-selection').show();
            $('#date-range-selection').hide();
        } else if (exportType === 'analytics' || exportType === 'leads') {
            $('#flashcard-selection').hide();
            $('#date-range-selection').show();
        } else if (exportType === 'complete') {
            $('#flashcard-selection').show();
            $('#date-range-selection').show();
        }
    });
    
    // Select all/none functionality
    $('.select-all').on('click', function() {
        $('input[name="selected_sets[]"]').prop('checked', true);
    });
    
    $('.select-none').on('click', function() {
        $('input[name="selected_sets[]"]').prop('checked', false);
    });
    
    // Form submission
    exportForm.on('submit', function(e) {
        e.preventDefault();
        
        // TODO: Implement actual export processing
        exportStatus.html('<div class="status-processing"><span class="dashicons dashicons-update spin"></span><p>Processing export...</p></div>');
        
        // Simulate export process
        setTimeout(function() {
            exportStatus.html('<div class="status-complete"><span class="dashicons dashicons-yes-alt"></span><p>Export completed! <a href="#" class="download-link">Download file</a></p></div>');
        }, 3000);
        
        alert('<?php esc_js( esc_html_e( 'Export functionality will be fully implemented in the next development phase.', 'skylearn-flashcards' ) ); ?>');
    });
    
    // Preview export
    $('#preview-export').on('click', function() {
        alert('<?php esc_js( esc_html_e( 'Preview functionality will be implemented in the next phase.', 'skylearn-flashcards' ) ); ?>');
    });
});
</script>