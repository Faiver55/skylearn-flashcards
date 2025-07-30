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
	?>
	<div class="wrap skylearn-admin-page">
		<div class="skylearn-upgrade-notice">
			<div class="upgrade-content">
				<h1><?php esc_html_e( 'Bulk Export & Backup - Premium Feature', 'skylearn-flashcards' ); ?></h1>
				<p><?php esc_html_e( 'Export your flashcard sets, analytics data, and user progress in multiple formats with our advanced export tools.', 'skylearn-flashcards' ); ?></p>
				<a href="<?php echo esc_url( SkyLearn_Flashcards_Premium::get_upgrade_url( 'bulk_export' ) ); ?>" 
				   class="button button-primary button-hero" target="_blank">
					<?php esc_html_e( 'Upgrade to Premium', 'skylearn-flashcards' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
	return;
}

/**
 * Get available flashcard sets for export
 */
$flashcard_sets = get_posts( array(
	'post_type'      => 'flashcard_set',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

/**
 * Get recent exports (simulate for now)
 */
$recent_exports = array(); // Would come from database in real implementation
?>

<div class="wrap skylearn-admin-page skylearn-premium-page">
    <div class="skylearn-header">
        <div class="skylearn-header-content">
            <img src="<?php echo esc_url( skylearn_get_logo_url( 'horizontal' ) ); ?>" 
                 alt="SkyLearn Flashcards" class="skylearn-logo">
            <h1>
                <?php esc_html_e( 'Bulk Export & Backup', 'skylearn-flashcards' ); ?>
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
                                <input type="radio" name="export_type" value="progress">
                                <div class="card-content">
                                    <span class="dashicons dashicons-groups"></span>
                                    <h4><?php esc_html_e( 'User Progress', 'skylearn-flashcards' ); ?></h4>
                                    <p><?php esc_html_e( 'Export user learning progress data', 'skylearn-flashcards' ); ?></p>
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
                            <?php if ( empty( $flashcard_sets ) ) : ?>
                                <div class="no-sets-message">
                                    <span class="dashicons dashicons-portfolio"></span>
                                    <p><?php esc_html_e( 'No flashcard sets available for export.', 'skylearn-flashcards' ); ?></p>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-editor' ) ); ?>" class="button button-primary">
                                        <?php esc_html_e( 'Create Your First Set', 'skylearn-flashcards' ); ?>
                                    </a>
                                </div>
                            <?php else : ?>
                                <?php foreach ( $flashcard_sets as $set ) : ?>
                                    <?php 
                                    $card_count = count( get_post_meta( $set->ID, '_skylearn_flashcard_data', true ) ?: array() );
                                    $views = 0; // Would get from analytics in real implementation
                                    ?>
                                    <label class="set-checkbox">
                                        <input type="checkbox" name="selected_sets[]" value="<?php echo esc_attr( $set->ID ); ?>">
                                        <div class="set-info">
                                            <h4><?php echo esc_html( $set->post_title ); ?></h4>
                                            <p><?php printf( 
                                                esc_html__( '%d cards • Created %s', 'skylearn-flashcards' ), 
                                                $card_count,
                                                human_time_diff( strtotime( $set->post_date ) )
                                            ); ?> ago</p>
                                        </div>
                                        <div class="set-stats">
                                            <span class="stat status-<?php echo esc_attr( $set->post_status ); ?>">
                                                <?php echo esc_html( ucfirst( $set->post_status ) ); ?>
                                            </span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Date Range Selection -->
                    <div class="form-section" id="date-range-selection" style="display: none;">
                        <h3><?php esc_html_e( 'Date Range', 'skylearn-flashcards' ); ?></h3>
                        <div class="date-range-controls">
                            <div class="form-group">
                                <label for="export-date-from"><?php esc_html_e( 'From:', 'skylearn-flashcards' ); ?></label>
                                <input type="date" id="export-date-from" name="date_from" class="form-input" 
                                       value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days' ) ) ); ?>">
                            </div>
                            <div class="form-group">
                                <label for="export-date-to"><?php esc_html_e( 'To:', 'skylearn-flashcards' ); ?></label>
                                <input type="date" id="export-date-to" name="date_to" class="form-input" 
                                       value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
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
                            
                            <label class="format-option" id="scorm-option" style="display: none;">
                                <input type="radio" name="export_format" value="scorm">
                                <div class="format-content">
                                    <span class="dashicons dashicons-archive"></span>
                                    <span class="format-name">SCORM</span>
                                    <small><?php esc_html_e( 'SCORM package for LMS', 'skylearn-flashcards' ); ?></small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Export Options -->
                    <div class="form-section">
                        <h3><?php esc_html_e( 'Export Options', 'skylearn-flashcards' ); ?></h3>
                        <div class="export-options">
                            <label class="checkbox-option">
                                <input type="checkbox" name="include_metadata" checked>
                                <span><?php esc_html_e( 'Include metadata (creation dates, authors, etc.)', 'skylearn-flashcards' ); ?></span>
                            </label>
                            
                            <label class="checkbox-option" id="include-stats-option">
                                <input type="checkbox" name="include_statistics">
                                <span><?php esc_html_e( 'Include performance statistics', 'skylearn-flashcards' ); ?></span>
                            </label>
                            
                            <label class="checkbox-option">
                                <input type="checkbox" name="anonymize_data">
                                <span><?php esc_html_e( 'Anonymize user data (remove personal information)', 'skylearn-flashcards' ); ?></span>
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
                    <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'skylearn_bulk_export' ) ); ?>">
                </form>
            </div>

            <!-- Export Status & History -->
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
                            <small><?php esc_html_e( 'Select your export options and click "Start Export"', 'skylearn-flashcards' ); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Export Progress -->
                <div class="export-progress" id="export-progress" style="display: none;">
                    <div class="progress-header">
                        <h4><?php esc_html_e( 'Export Progress', 'skylearn-flashcards' ); ?></h4>
                        <span class="progress-percentage">0%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <div class="progress-details">
                        <span class="current-step"><?php esc_html_e( 'Initializing...', 'skylearn-flashcards' ); ?></span>
                    </div>
                </div>

                <!-- Quick Export Actions -->
                <div class="quick-actions">
                    <h4><?php esc_html_e( 'Quick Actions', 'skylearn-flashcards' ); ?></h4>
                    <div class="quick-action-buttons">
                        <button type="button" class="button button-secondary quick-export" data-type="all_sets">
                            <span class="dashicons dashicons-portfolio"></span>
                            <?php esc_html_e( 'Export All Sets', 'skylearn-flashcards' ); ?>
                        </button>
                        <button type="button" class="button button-secondary quick-export" data-type="recent_analytics">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php esc_html_e( 'Last 30 Days Analytics', 'skylearn-flashcards' ); ?>
                        </button>
                        <button type="button" class="button button-secondary quick-export" data-type="full_backup">
                            <span class="dashicons dashicons-database-export"></span>
                            <?php esc_html_e( 'Full Backup', 'skylearn-flashcards' ); ?>
                        </button>
                    </div>
                </div>

                <!-- Export Tips -->
                <div class="export-tips">
                    <h4><?php esc_html_e( 'Export Tips', 'skylearn-flashcards' ); ?></h4>
                    <ul>
                        <li><?php esc_html_e( 'CSV format is best for spreadsheet applications', 'skylearn-flashcards' ); ?></li>
                        <li><?php esc_html_e( 'JSON format preserves all data structures', 'skylearn-flashcards' ); ?></li>
                        <li><?php esc_html_e( 'Use SCORM for LMS integration', 'skylearn-flashcards' ); ?></li>
                        <li><?php esc_html_e( 'Large exports may take several minutes', 'skylearn-flashcards' ); ?></li>
                    </ul>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Export Preview Modal -->
<div id="export-preview-modal" class="skylearn-modal" style="display: none;">
    <div class="modal-content large">
        <div class="modal-header">
            <h3><?php esc_html_e( 'Export Preview', 'skylearn-flashcards' ); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="preview-content">
                <div class="loading-preview">
                    <span class="dashicons dashicons-update spin"></span>
                    <p><?php esc_html_e( 'Generating preview...', 'skylearn-flashcards' ); ?></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-primary" id="proceed-with-export">
                <?php esc_html_e( 'Proceed with Export', 'skylearn-flashcards' ); ?>
            </button>
            <button type="button" class="button modal-close"><?php esc_html_e( 'Close', 'skylearn-flashcards' ); ?></button>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var exportForm = $('#export-form');
    var exportStatus = $('#export-status');
    var exportProgress = $('#export-progress');
    
    // Handle export type changes
    $('input[name="export_type"]').on('change', function() {
        var exportType = $(this).val();
        
        // Show/hide relevant sections
        if (exportType === 'flashcards') {
            $('#flashcard-selection').show();
            $('#date-range-selection').hide();
            $('#scorm-option').show();
            $('#include-stats-option').show();
        } else if (exportType === 'analytics' || exportType === 'progress') {
            $('#flashcard-selection').hide();
            $('#date-range-selection').show();
            $('#scorm-option').hide();
            $('#include-stats-option').hide();
        } else if (exportType === 'complete') {
            $('#flashcard-selection').show();
            $('#date-range-selection').show();
            $('#scorm-option').show();
            $('#include-stats-option').show();
        }
    });
    
    // Select all/none functionality
    $('.select-all').on('click', function() {
        $('input[name="selected_sets[]"]').prop('checked', true);
    });
    
    $('.select-none').on('click', function() {
        $('input[name="selected_sets[]"]').prop('checked', false);
    });
    
    // Search functionality
    $('.search-sets').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.set-checkbox').each(function() {
            var setTitle = $(this).find('h4').text().toLowerCase();
            $(this).toggle(setTitle.indexOf(searchTerm) !== -1);
        });
    });
    
    // Quick export actions
    $('.quick-export').on('click', function() {
        var type = $(this).data('type');
        
        switch(type) {
            case 'all_sets':
                $('input[name="export_type"][value="flashcards"]').prop('checked', true).trigger('change');
                $('.select-all').click();
                break;
            case 'recent_analytics':
                $('input[name="export_type"][value="analytics"]').prop('checked', true).trigger('change');
                break;
            case 'full_backup':
                $('input[name="export_type"][value="complete"]').prop('checked', true).trigger('change');
                $('.select-all').click();
                break;
        }
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: exportForm.offset().top - 50
        }, 500);
    });
    
    // Preview export
    $('#preview-export').on('click', function() {
        $('#export-preview-modal').show();
        
        // Simulate preview generation
        setTimeout(function() {
            var exportType = $('input[name="export_type"]:checked').val();
            var format = $('input[name="export_format"]:checked').val();
            var selectedCount = $('input[name="selected_sets[]"]:checked').length;
            
            var previewHtml = '<div class="preview-summary">';
            previewHtml += '<h4><?php esc_js( esc_html_e( 'Export Summary', 'skylearn-flashcards' ) ); ?></h4>';
            previewHtml += '<p><strong><?php esc_js( esc_html_e( 'Type:', 'skylearn-flashcards' ) ); ?></strong> ' + exportType.charAt(0).toUpperCase() + exportType.slice(1) + '</p>';
            previewHtml += '<p><strong><?php esc_js( esc_html_e( 'Format:', 'skylearn-flashcards' ) ); ?></strong> ' + format.toUpperCase() + '</p>';
            
            if (exportType === 'flashcards') {
                previewHtml += '<p><strong><?php esc_js( esc_html_e( 'Sets:', 'skylearn-flashcards' ) ); ?></strong> ' + selectedCount + ' selected</p>';
            }
            
            previewHtml += '<p><strong><?php esc_js( esc_html_e( 'Estimated file size:', 'skylearn-flashcards' ) ); ?></strong> ' + Math.ceil(selectedCount * 0.5) + ' KB</p>';
            previewHtml += '</div>';
            
            if (format === 'csv') {
                previewHtml += '<div class="preview-sample">';
                previewHtml += '<h4><?php esc_js( esc_html_e( 'Sample Output:', 'skylearn-flashcards' ) ); ?></h4>';
                previewHtml += '<pre>Set ID,Set Title,Card Front,Card Back,Card Index\n1,"Sample Set","What is...","The answer is...",0</pre>';
                previewHtml += '</div>';
            }
            
            $('#preview-content').html(previewHtml);
        }, 1000);
    });
    
    // Proceed with export from preview
    $('#proceed-with-export').on('click', function() {
        $('#export-preview-modal').hide();
        exportForm.trigger('submit');
    });
    
    // Form submission
    exportForm.on('submit', function(e) {
        e.preventDefault();
        
        var exportType = $('input[name="export_type"]:checked').val();
        var selectedItems = [];
        
        if (exportType === 'flashcards' || exportType === 'complete') {
            selectedItems = $('input[name="selected_sets[]"]:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedItems.length === 0) {
                alert('<?php esc_js( esc_html_e( 'Please select at least one flashcard set to export.', 'skylearn-flashcards' ) ); ?>');
                return;
            }
        }
        
        // Show progress
        exportStatus.hide();
        exportProgress.show();
        
        var formData = {
            action: 'skylearn_bulk_export',
            export_type: exportType,
            format: $('input[name="export_format"]:checked').val(),
            items: selectedItems,
            date_from: $('#export-date-from').val(),
            date_to: $('#export-date-to').val(),
            include_metadata: $('input[name="include_metadata"]').is(':checked'),
            include_statistics: $('input[name="include_statistics"]').is(':checked'),
            anonymize_data: $('input[name="anonymize_data"]').is(':checked'),
            nonce: $('input[name="nonce"]').val()
        };
        
        // Simulate progress
        var progress = 0;
        var progressInterval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 95) progress = 95;
            
            $('.progress-fill').css('width', progress + '%');
            $('.progress-percentage').text(Math.round(progress) + '%');
            
            if (progress > 30) $('.current-step').text('<?php esc_js( esc_html_e( 'Processing data...', 'skylearn-flashcards' ) ); ?>');
            if (progress > 60) $('.current-step').text('<?php esc_js( esc_html_e( 'Generating export file...', 'skylearn-flashcards' ) ); ?>');
            if (progress > 85) $('.current-step').text('<?php esc_js( esc_html_e( 'Finalizing...', 'skylearn-flashcards' ) ); ?>');
        }, 200);
        
        // Actual AJAX request
        $.post(ajaxurl, formData, function(response) {
            clearInterval(progressInterval);
            
            if (response.success) {
                $('.progress-fill').css('width', '100%');
                $('.progress-percentage').text('100%');
                $('.current-step').text('<?php esc_js( esc_html_e( 'Export completed!', 'skylearn-flashcards' ) ); ?>');
                
                // Trigger download
                setTimeout(function() {
                    var blob = new Blob([response.data.data], { 
                        type: response.data.format === 'json' ? 'application/json' : 'text/csv' 
                    });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = response.data.filename;
                    a.click();
                    window.URL.revokeObjectURL(url);
                    
                    // Reset UI
                    setTimeout(function() {
                        exportProgress.hide();
                        exportStatus.show();
                        exportStatus.html('<div class="status-complete"><span class="dashicons dashicons-yes-alt"></span><p><?php esc_js( esc_html_e( 'Export completed successfully!', 'skylearn-flashcards' ) ); ?></p></div>');
                        
                        setTimeout(function() {
                            exportStatus.html('<div class="status-idle"><span class="dashicons dashicons-admin-tools"></span><p><?php esc_js( esc_html_e( 'Ready to export', 'skylearn-flashcards' ) ); ?></p></div>');
                        }, 3000);
                    }, 1000);
                }, 500);
                
            } else {
                clearInterval(progressInterval);
                exportProgress.hide();
                exportStatus.show();
                exportStatus.html('<div class="status-error"><span class="dashicons dashicons-warning"></span><p><?php esc_js( esc_html_e( 'Export failed:', 'skylearn-flashcards' ) ); ?> ' + (response.data.message || '<?php esc_js( esc_html_e( 'Unknown error', 'skylearn-flashcards' ) ); ?>') + '</p></div>');
            }
        }).fail(function() {
            clearInterval(progressInterval);
            exportProgress.hide();
            exportStatus.show();
            exportStatus.html('<div class="status-error"><span class="dashicons dashicons-warning"></span><p><?php esc_js( esc_html_e( 'Export request failed. Please try again.', 'skylearn-flashcards' ) ); ?></p></div>');
        });
    });
    
    // Modal close functionality
    $('.modal-close').on('click', function() {
        $('.skylearn-modal').hide();
    });
    
    // Close modal on outside click
    $('.skylearn-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    console.log('Bulk Export page loaded with real functionality');
});
</script>