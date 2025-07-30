<?php
/**
 * Admin leads management page view
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin/views
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get leads statistics for display
 */
$leads_instance = new SkyLearn_Flashcards_Leads( 'skylearn-flashcards', '1.0.0' );
$stats = $leads_instance->get_lead_statistics();
$leads = $leads_instance->get_leads( array( 'limit' => 50 ) );
?>

<div class="wrap skylearn-admin-page">
    <div class="skylearn-header">
        <div class="skylearn-header-content">
            <img src="<?php echo esc_url( skylearn_get_logo_url( 'horizontal' ) ); ?>" 
                 alt="SkyLearn Flashcards" class="skylearn-logo">
            <h1><?php esc_html_e( 'Lead Management', 'skylearn-flashcards' ); ?></h1>
        </div>
    </div>

    <div class="skylearn-content">
        <div class="skylearn-grid">
            
            <!-- Lead Statistics Cards -->
            <div class="skylearn-stats-grid">
                <div class="skylearn-stat-card">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo esc_html( number_format( $stats['total_leads'] ) ); ?></h3>
                        <p><?php esc_html_e( 'Total Leads', 'skylearn-flashcards' ); ?></p>
                    </div>
                </div>
                
                <div class="skylearn-stat-card">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo esc_html( number_format( $stats['new_today'] ) ); ?></h3>
                        <p><?php esc_html_e( 'New Today', 'skylearn-flashcards' ); ?></p>
                    </div>
                </div>
                
                <div class="skylearn-stat-card">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo esc_html( number_format( $stats['conversion_rate'], 2 ) ); ?>%</h3>
                        <p><?php esc_html_e( 'Conversion Rate', 'skylearn-flashcards' ); ?></p>
                    </div>
                </div>
                
                <div class="skylearn-stat-card">
                    <div class="stat-icon">
                        <span class="dashicons dashicons-email-alt"></span>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo esc_html( number_format( $stats['new_this_week'] ) ); ?></h3>
                        <p><?php esc_html_e( 'This Week', 'skylearn-flashcards' ); ?></p>
                    </div>
                </div>
            </div>

            <!-- Lead Management Tools -->
            <div class="skylearn-panel">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Lead Management Tools', 'skylearn-flashcards' ); ?></h2>
                    <div class="panel-actions">
                        <button type="button" class="button button-primary" id="export-leads">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Export Leads', 'skylearn-flashcards' ); ?>
                        </button>
                        <button type="button" class="button" id="refresh-leads">
                            <span class="dashicons dashicons-update"></span>
                            <?php esc_html_e( 'Refresh', 'skylearn-flashcards' ); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="skylearn-filters">
                    <div class="filter-group">
                        <label for="date-range"><?php esc_html_e( 'Date Range:', 'skylearn-flashcards' ); ?></label>
                        <select id="date-range" class="filter-select">
                            <option value="all"><?php esc_html_e( 'All Time', 'skylearn-flashcards' ); ?></option>
                            <option value="today"><?php esc_html_e( 'Today', 'skylearn-flashcards' ); ?></option>
                            <option value="week"><?php esc_html_e( 'This Week', 'skylearn-flashcards' ); ?></option>
                            <option value="month"><?php esc_html_e( 'This Month', 'skylearn-flashcards' ); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="lead-status"><?php esc_html_e( 'Status:', 'skylearn-flashcards' ); ?></label>
                        <select id="lead-status" class="filter-select">
                            <option value="all"><?php esc_html_e( 'All Statuses', 'skylearn-flashcards' ); ?></option>
                            <option value="new"><?php esc_html_e( 'New', 'skylearn-flashcards' ); ?></option>
                            <option value="contacted"><?php esc_html_e( 'Contacted', 'skylearn-flashcards' ); ?></option>
                            <option value="converted"><?php esc_html_e( 'Converted', 'skylearn-flashcards' ); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search-leads"><?php esc_html_e( 'Search:', 'skylearn-flashcards' ); ?></label>
                        <input type="text" id="search-leads" class="filter-input" 
                               placeholder="<?php esc_attr_e( 'Search by email or name...', 'skylearn-flashcards' ); ?>">
                    </div>
                </div>
            </div>

            <!-- Leads Table -->
            <div class="skylearn-panel">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Recent Leads', 'skylearn-flashcards' ); ?></h2>
                </div>
                
                <div class="skylearn-table-container">
                    <?php if ( empty( $leads ) ) : ?>
                        <div class="skylearn-empty-state">
                            <div class="empty-icon">
                                <span class="dashicons dashicons-email"></span>
                            </div>
                            <h3><?php esc_html_e( 'No Leads Yet', 'skylearn-flashcards' ); ?></h3>
                            <p><?php esc_html_e( 'Lead collection is ready to capture visitor information when they interact with your flashcards.', 'skylearn-flashcards' ); ?></p>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-settings' ) ); ?>" class="button button-primary">
                                <?php esc_html_e( 'Configure Lead Collection', 'skylearn-flashcards' ); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <table class="widefat striped skylearn-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="check-column">
                                        <input type="checkbox" id="select-all-leads">
                                    </th>
                                    <th scope="col"><?php esc_html_e( 'Name', 'skylearn-flashcards' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Email', 'skylearn-flashcards' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Source', 'skylearn-flashcards' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Date', 'skylearn-flashcards' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Status', 'skylearn-flashcards' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Actions', 'skylearn-flashcards' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- TODO: Loop through actual leads data -->
                                <tr>
                                    <td colspan="7" class="skylearn-placeholder">
                                        <?php esc_html_e( 'Lead data will be displayed here when leads are collected.', 'skylearn-flashcards' ); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email Marketing Integration -->
            <div class="skylearn-panel">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Email Marketing Integration', 'skylearn-flashcards' ); ?></h2>
                </div>
                
                <div class="integration-grid">
                    <div class="integration-card">
                        <div class="integration-icon">
                            <img src="<?php echo esc_url( SKYLEARN_FLASHCARDS_ASSETS . 'img/mailchimp-icon.png' ); ?>" 
                                 alt="Mailchimp" class="service-icon">
                        </div>
                        <div class="integration-content">
                            <h4><?php esc_html_e( 'Mailchimp', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( 'Sync leads to your Mailchimp lists automatically.', 'skylearn-flashcards' ); ?></p>
                            <span class="status-badge status-inactive"><?php esc_html_e( 'Not Connected', 'skylearn-flashcards' ); ?></span>
                        </div>
                        <div class="integration-actions">
                            <button type="button" class="button"><?php esc_html_e( 'Configure', 'skylearn-flashcards' ); ?></button>
                        </div>
                    </div>
                    
                    <div class="integration-card">
                        <div class="integration-icon">
                            <img src="<?php echo esc_url( SKYLEARN_FLASHCARDS_ASSETS . 'img/vbout-icon.png' ); ?>" 
                                 alt="Vbout" class="service-icon">
                        </div>
                        <div class="integration-content">
                            <h4><?php esc_html_e( 'Vbout', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( 'Connect with Vbout for advanced automation.', 'skylearn-flashcards' ); ?></p>
                            <span class="status-badge status-inactive"><?php esc_html_e( 'Not Connected', 'skylearn-flashcards' ); ?></span>
                        </div>
                        <div class="integration-actions">
                            <button type="button" class="button"><?php esc_html_e( 'Configure', 'skylearn-flashcards' ); ?></button>
                        </div>
                    </div>
                    
                    <div class="integration-card">
                        <div class="integration-icon">
                            <img src="<?php echo esc_url( SKYLEARN_FLASHCARDS_ASSETS . 'img/sendfox-icon.png' ); ?>" 
                                 alt="SendFox" class="service-icon">
                        </div>
                        <div class="integration-content">
                            <h4><?php esc_html_e( 'SendFox', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( 'Integrate with SendFox for email marketing.', 'skylearn-flashcards' ); ?></p>
                            <span class="status-badge status-inactive"><?php esc_html_e( 'Not Connected', 'skylearn-flashcards' ); ?></span>
                        </div>
                        <div class="integration-actions">
                            <button type="button" class="button"><?php esc_html_e( 'Configure', 'skylearn-flashcards' ); ?></button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Lead Management Modal -->
<div id="lead-modal" class="skylearn-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php esc_html_e( 'Lead Details', 'skylearn-flashcards' ); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- TODO: Add lead detail form -->
            <p><?php esc_html_e( 'Lead management functionality will be implemented here.', 'skylearn-flashcards' ); ?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-primary"><?php esc_html_e( 'Save Changes', 'skylearn-flashcards' ); ?></button>
            <button type="button" class="button modal-close"><?php esc_html_e( 'Cancel', 'skylearn-flashcards' ); ?></button>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // TODO: Implement lead management JavaScript functionality
    console.log('SkyLearn Flashcards Lead Management page loaded');
    
    // Placeholder for future JavaScript functionality
    $('#export-leads').on('click', function() {
        alert('<?php esc_js( esc_html_e( 'Export functionality will be implemented in a future phase.', 'skylearn-flashcards' ) ); ?>');
    });
});
</script>