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

// Check if premium features are available
if ( ! skylearn_is_premium() ) {
	?>
	<div class="wrap skylearn-admin-page">
		<div class="skylearn-upgrade-notice">
			<div class="upgrade-content">
				<h1><?php esc_html_e( 'Lead Management - Premium Feature', 'skylearn-flashcards' ); ?></h1>
				<p><?php esc_html_e( 'Lead collection and management are premium features. Upgrade to access advanced lead capture, email integrations, and detailed analytics.', 'skylearn-flashcards' ); ?></p>
				<a href="<?php echo esc_url( SkyLearn_Flashcards_Premium::get_upgrade_url( 'lead_management' ) ); ?>" 
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
 * Get leads statistics and data for display
 */
$leads_instance = new SkyLearn_Flashcards_Leads( 'skylearn-flashcards', '1.0.0' );

// Handle bulk actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ( $action && check_admin_referer( 'skylearn_leads_bulk', 'skylearn_nonce' ) ) {
	$lead_ids = $_POST['lead_ids'] ?? array();
	
	switch ( $action ) {
		case 'delete':
			foreach ( array_map( 'absint', $lead_ids ) as $lead_id ) {
				$leads_instance->delete_lead( $lead_id );
			}
			echo '<div class="notice notice-success"><p>' . 
				 sprintf( esc_html__( 'Deleted %d leads.', 'skylearn-flashcards' ), count( $lead_ids ) ) . 
				 '</p></div>';
			break;
			
		case 'mark_contacted':
			foreach ( array_map( 'absint', $lead_ids ) as $lead_id ) {
				$leads_instance->mark_contacted( $lead_id );
			}
			echo '<div class="notice notice-success"><p>' . 
				 sprintf( esc_html__( 'Marked %d leads as contacted.', 'skylearn-flashcards' ), count( $lead_ids ) ) . 
				 '</p></div>';
			break;
	}
}

// Handle export request
if ( isset( $_GET['action'] ) && $_GET['action'] === 'export' && check_admin_referer( 'skylearn_export_leads', 'nonce' ) ) {
	$export_args = array(
		'limit'      => -1, // Export all
		'status'     => $_GET['status'] ?? 'any',
		'date_from'  => $_GET['date_from'] ?? '',
		'date_to'    => $_GET['date_to'] ?? '',
	);
	
	$csv_data = $leads_instance->export_leads( $export_args );
	
	if ( $csv_data ) {
		$filename = 'skylearn-leads-' . date( 'Y-m-d' ) . '.csv';
		
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		
		echo $csv_data;
		exit;
	}
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'any';
$search_term = $_GET['search'] ?? '';
$date_range = $_GET['date_range'] ?? 'all';

// Set date filters based on range
$date_from = '';
$date_to = '';
switch ( $date_range ) {
	case 'today':
		$date_from = current_time( 'Y-m-d' ) . ' 00:00:00';
		$date_to = current_time( 'Y-m-d' ) . ' 23:59:59';
		break;
	case 'week':
		$date_from = date( 'Y-m-d', strtotime( 'monday this week' ) ) . ' 00:00:00';
		$date_to = date( 'Y-m-d', strtotime( 'sunday this week' ) ) . ' 23:59:59';
		break;
	case 'month':
		$date_from = date( 'Y-m-01' ) . ' 00:00:00';
		$date_to = date( 'Y-m-t' ) . ' 23:59:59';
		break;
}

// Get leads data
$leads_args = array(
	'limit'     => 50,
	'offset'    => ( $_GET['paged'] ?? 1 - 1 ) * 50,
	'status'    => $status_filter !== 'any' ? $status_filter : null,
	'search'    => $search_term,
	'date_from' => $date_from,
	'date_to'   => $date_to,
);

$stats = $leads_instance->get_lead_statistics();
$leads = $leads_instance->get_leads( $leads_args );
$total_leads = $leads_instance->get_leads_count( $leads_args );

// Get email integration settings
$email_settings = get_option( 'skylearn_flashcards_email_settings', array() );
$active_provider = $email_settings['provider'] ?? '';
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
                        <a href="<?php echo esc_url( wp_nonce_url( 
                            add_query_arg( array( 'action' => 'export', 'status' => $status_filter ), 
                            admin_url( 'admin.php?page=skylearn-leads' ) ), 'skylearn_export_leads', 'nonce' ) ); ?>" 
                           class="button button-primary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Export Leads', 'skylearn-flashcards' ); ?>
                        </a>
                        <button type="button" class="button" onclick="location.reload();">
                            <span class="dashicons dashicons-update"></span>
                            <?php esc_html_e( 'Refresh', 'skylearn-flashcards' ); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Filters -->
                <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="skylearn-filters">
                    <input type="hidden" name="page" value="skylearn-leads">
                    
                    <div class="filter-group">
                        <label for="date-range"><?php esc_html_e( 'Date Range:', 'skylearn-flashcards' ); ?></label>
                        <select id="date-range" name="date_range" class="filter-select">
                            <option value="all" <?php selected( $date_range, 'all' ); ?>><?php esc_html_e( 'All Time', 'skylearn-flashcards' ); ?></option>
                            <option value="today" <?php selected( $date_range, 'today' ); ?>><?php esc_html_e( 'Today', 'skylearn-flashcards' ); ?></option>
                            <option value="week" <?php selected( $date_range, 'week' ); ?>><?php esc_html_e( 'This Week', 'skylearn-flashcards' ); ?></option>
                            <option value="month" <?php selected( $date_range, 'month' ); ?>><?php esc_html_e( 'This Month', 'skylearn-flashcards' ); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="lead-status"><?php esc_html_e( 'Status:', 'skylearn-flashcards' ); ?></label>
                        <select id="lead-status" name="status" class="filter-select">
                            <option value="any" <?php selected( $status_filter, 'any' ); ?>><?php esc_html_e( 'All Statuses', 'skylearn-flashcards' ); ?></option>
                            <option value="new" <?php selected( $status_filter, 'new' ); ?>><?php esc_html_e( 'New', 'skylearn-flashcards' ); ?></option>
                            <option value="contacted" <?php selected( $status_filter, 'contacted' ); ?>><?php esc_html_e( 'Contacted', 'skylearn-flashcards' ); ?></option>
                            <option value="converted" <?php selected( $status_filter, 'converted' ); ?>><?php esc_html_e( 'Converted', 'skylearn-flashcards' ); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search-leads"><?php esc_html_e( 'Search:', 'skylearn-flashcards' ); ?></label>
                        <input type="text" id="search-leads" name="search" class="filter-input" 
                               value="<?php echo esc_attr( $search_term ); ?>"
                               placeholder="<?php esc_attr_e( 'Search by email or name...', 'skylearn-flashcards' ); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="button"><?php esc_html_e( 'Filter', 'skylearn-flashcards' ); ?></button>
                    </div>
                </form>
            </div>

            <!-- Leads Table -->
            <div class="skylearn-panel">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Recent Leads', 'skylearn-flashcards' ); ?></h2>
                    <p class="description">
                        <?php printf( esc_html__( 'Showing %d leads', 'skylearn-flashcards' ), count( $leads ) ); ?>
                        <?php if ( $total_leads > count( $leads ) ) : ?>
                            <?php printf( esc_html__( ' of %d total', 'skylearn-flashcards' ), $total_leads ); ?>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="skylearn-table-container">
                    <?php if ( empty( $leads ) ) : ?>
                        <div class="skylearn-empty-state">
                            <div class="empty-icon">
                                <span class="dashicons dashicons-email"></span>
                            </div>
                            <h3><?php esc_html_e( 'No Leads Found', 'skylearn-flashcards' ); ?></h3>
                            <p><?php esc_html_e( 'Lead collection is ready to capture visitor information when they interact with your flashcards.', 'skylearn-flashcards' ); ?></p>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-settings#leads' ) ); ?>" class="button button-primary">
                                <?php esc_html_e( 'Configure Lead Collection', 'skylearn-flashcards' ); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-leads' ) ); ?>" id="leads-form">
                            <?php wp_nonce_field( 'skylearn_leads_bulk', 'skylearn_nonce' ); ?>
                            
                            <div class="tablenav top">
                                <div class="alignleft actions bulkactions">
                                    <select name="action" id="bulk-action-selector-top">
                                        <option value="-1"><?php esc_html_e( 'Bulk Actions', 'skylearn-flashcards' ); ?></option>
                                        <option value="mark_contacted"><?php esc_html_e( 'Mark as Contacted', 'skylearn-flashcards' ); ?></option>
                                        <option value="delete"><?php esc_html_e( 'Delete', 'skylearn-flashcards' ); ?></option>
                                    </select>
                                    <input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'skylearn-flashcards' ); ?>">
                                </div>
                            </div>
                            
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
                                    <?php foreach ( $leads as $lead ) : ?>
                                        <tr>
                                            <th scope="row" class="check-column">
                                                <input type="checkbox" name="lead_ids[]" value="<?php echo esc_attr( $lead['id'] ); ?>">
                                            </th>
                                            <td>
                                                <strong><?php echo esc_html( $lead['name'] ?: __( '(No name)', 'skylearn-flashcards' ) ); ?></strong>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo esc_attr( $lead['email'] ); ?>"><?php echo esc_html( $lead['email'] ); ?></a>
                                            </td>
                                            <td>
                                                <?php 
                                                $set_title = get_the_title( $lead['set_id'] );
                                                echo esc_html( $set_title ?: __( 'Unknown Set', 'skylearn-flashcards' ) );
                                                ?>
                                                <br><small><?php echo esc_html( $lead['source'] ); ?></small>
                                            </td>
                                            <td>
                                                <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $lead['created_at'] ) ) ); ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo esc_attr( $lead['status'] ); ?>">
                                                    <?php echo esc_html( ucfirst( $lead['status'] ) ); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="button button-small view-lead" data-lead-id="<?php echo esc_attr( $lead['id'] ); ?>">
                                                    <?php esc_html_e( 'View', 'skylearn-flashcards' ); ?>
                                                </button>
                                                <button type="button" class="button button-small delete-lead" data-lead-id="<?php echo esc_attr( $lead['id'] ); ?>">
                                                    <?php esc_html_e( 'Delete', 'skylearn-flashcards' ); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email Marketing Integration Status -->
            <div class="skylearn-panel">
                <div class="panel-header">
                    <h2><?php esc_html_e( 'Email Marketing Integration', 'skylearn-flashcards' ); ?></h2>
                </div>
                
                <div class="integration-grid">
                    <div class="integration-card <?php echo $active_provider === 'mailchimp' ? 'active' : ''; ?>">
                        <div class="integration-icon">
                            <span class="dashicons dashicons-email-alt2"></span>
                        </div>
                        <div class="integration-content">
                            <h4><?php esc_html_e( 'Mailchimp', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( 'Sync leads to your Mailchimp lists automatically.', 'skylearn-flashcards' ); ?></p>
                            <span class="status-badge <?php echo $active_provider === 'mailchimp' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $active_provider === 'mailchimp' ? esc_html__( 'Connected', 'skylearn-flashcards' ) : esc_html__( 'Not Connected', 'skylearn-flashcards' ); ?>
                            </span>
                        </div>
                        <div class="integration-actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-settings#email' ) ); ?>" class="button">
                                <?php esc_html_e( 'Configure', 'skylearn-flashcards' ); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="integration-card <?php echo $active_provider === 'vbout' ? 'active' : ''; ?>">
                        <div class="integration-icon">
                            <span class="dashicons dashicons-email-alt2"></span>
                        </div>
                        <div class="integration-content">
                            <h4><?php esc_html_e( 'Vbout', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( 'Connect with Vbout for advanced automation.', 'skylearn-flashcards' ); ?></p>
                            <span class="status-badge <?php echo $active_provider === 'vbout' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $active_provider === 'vbout' ? esc_html__( 'Connected', 'skylearn-flashcards' ) : esc_html__( 'Not Connected', 'skylearn-flashcards' ); ?>
                            </span>
                        </div>
                        <div class="integration-actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-settings#email' ) ); ?>" class="button">
                                <?php esc_html_e( 'Configure', 'skylearn-flashcards' ); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="integration-card <?php echo $active_provider === 'sendfox' ? 'active' : ''; ?>">
                        <div class="integration-icon">
                            <span class="dashicons dashicons-email-alt2"></span>
                        </div>
                        <div class="integration-content">
                            <h4><?php esc_html_e( 'SendFox', 'skylearn-flashcards' ); ?></h4>
                            <p><?php esc_html_e( 'Integrate with SendFox for email marketing.', 'skylearn-flashcards' ); ?></p>
                            <span class="status-badge <?php echo $active_provider === 'sendfox' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $active_provider === 'sendfox' ? esc_html__( 'Connected', 'skylearn-flashcards' ) : esc_html__( 'Not Connected', 'skylearn-flashcards' ); ?>
                            </span>
                        </div>
                        <div class="integration-actions">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-settings#email' ) ); ?>" class="button">
                                <?php esc_html_e( 'Configure', 'skylearn-flashcards' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Lead Details Modal -->
<div id="lead-modal" class="skylearn-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php esc_html_e( 'Lead Details', 'skylearn-flashcards' ); ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="lead-details-content">
                <!-- Lead details will be loaded here -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-primary" id="update-lead-status">
                <?php esc_html_e( 'Mark as Contacted', 'skylearn-flashcards' ); ?>
            </button>
            <button type="button" class="button modal-close"><?php esc_html_e( 'Close', 'skylearn-flashcards' ); ?></button>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Select all checkbox functionality
    $('#select-all-leads').on('change', function() {
        $('input[name="lead_ids[]"]').prop('checked', this.checked);
    });
    
    // Individual checkbox changes
    $('input[name="lead_ids[]"]').on('change', function() {
        var allChecked = $('input[name="lead_ids[]"]:checked').length === $('input[name="lead_ids[]"]').length;
        $('#select-all-leads').prop('checked', allChecked);
    });
    
    // View lead details
    $('.view-lead').on('click', function() {
        var leadId = $(this).data('lead-id');
        
        // Load lead details via AJAX
        $.post(ajaxurl, {
            action: 'skylearn_get_lead_details',
            lead_id: leadId,
            nonce: '<?php echo esc_js( wp_create_nonce( 'skylearn_lead_details' ) ); ?>'
        }, function(response) {
            if (response.success) {
                $('#lead-details-content').html(response.data.html);
                $('#update-lead-status').data('lead-id', leadId);
                $('#lead-modal').show();
            } else {
                alert(response.data.message || '<?php esc_js( esc_html_e( 'Error loading lead details.', 'skylearn-flashcards' ) ); ?>');
            }
        });
    });
    
    // Update lead status
    $('#update-lead-status').on('click', function() {
        var leadId = $(this).data('lead-id');
        
        $.post(ajaxurl, {
            action: 'skylearn_update_lead_status',
            lead_id: leadId,
            status: 'contacted',
            nonce: '<?php echo esc_js( wp_create_nonce( 'skylearn_update_lead' ) ); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message || '<?php esc_js( esc_html_e( 'Error updating lead status.', 'skylearn-flashcards' ) ); ?>');
            }
        });
    });
    
    // Delete individual lead
    $('.delete-lead').on('click', function() {
        if (!confirm('<?php esc_js( esc_html_e( 'Are you sure you want to delete this lead?', 'skylearn-flashcards' ) ); ?>')) {
            return;
        }
        
        var leadId = $(this).data('lead-id');
        var row = $(this).closest('tr');
        
        $.post(ajaxurl, {
            action: 'skylearn_delete_lead',
            lead_id: leadId,
            nonce: '<?php echo esc_js( wp_create_nonce( 'skylearn_delete_lead' ) ); ?>'
        }, function(response) {
            if (response.success) {
                row.fadeOut(function() {
                    row.remove();
                });
            } else {
                alert(response.data.message || '<?php esc_js( esc_html_e( 'Error deleting lead.', 'skylearn-flashcards' ) ); ?>');
            }
        });
    });
    
    // Modal close functionality
    $('.modal-close').on('click', function() {
        $('#lead-modal').hide();
    });
    
    // Bulk action form validation
    $('#leads-form').on('submit', function(e) {
        var action = $('#bulk-action-selector-top').val();
        var checkedLeads = $('input[name="lead_ids[]"]:checked').length;
        
        if (action === '-1') {
            e.preventDefault();
            alert('<?php esc_js( esc_html_e( 'Please select an action.', 'skylearn-flashcards' ) ); ?>');
            return;
        }
        
        if (checkedLeads === 0) {
            e.preventDefault();
            alert('<?php esc_js( esc_html_e( 'Please select at least one lead.', 'skylearn-flashcards' ) ); ?>');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm('<?php esc_js( esc_html_e( 'Are you sure you want to delete the selected leads?', 'skylearn-flashcards' ) ); ?>')) {
                e.preventDefault();
                return;
            }
        }
    });
    
    console.log('SkyLearn Flashcards Lead Management page loaded');
});
</script>