<?php
/**
 * Capability test admin page for SkyLearn Flashcards
 *
 * This dev-only page helps debug capability assignment and access issues.
 * Only shown in debug mode or to administrators.
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin
 */

/**
 * Capability test admin page class.
 *
 * Provides debugging tools for capability assignment and verification.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Capability_Test {

	/**
	 * Initialize the capability test page
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Only load in debug mode or for administrators
		if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', array( $this, 'add_test_page' ), 999 );
			add_action( 'wp_ajax_skylearn_test_capability_fix', array( $this, 'ajax_test_capability_fix' ) );
		}
	}

	/**
	 * Add capability test submenu page
	 *
	 * @since    1.0.0
	 */
	public function add_test_page() {
		add_submenu_page(
			'skylearn-flashcards',
			__( 'Capability Test (Debug)', 'skylearn-flashcards' ),
			__( 'Cap Test', 'skylearn-flashcards' ),
			'manage_options',
			'skylearn-flashcards-capability-test',
			array( $this, 'render_test_page' )
		);
	}

	/**
	 * Render the capability test page
	 *
	 * @since    1.0.0
	 */
	public function render_test_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SkyLearn Flashcards - Capability Test', 'skylearn-flashcards' ); ?></h1>
			
			<?php if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) : ?>
				<div class="notice notice-warning">
					<p><strong><?php esc_html_e( 'Debug Mode Required', 'skylearn-flashcards' ); ?></strong></p>
					<p><?php esc_html_e( 'This page is most useful when WP_DEBUG is enabled in wp-config.php.', 'skylearn-flashcards' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="capability-test-container">
				
				<!-- Current User Capabilities -->
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Current User Capabilities', 'skylearn-flashcards' ); ?></h2>
					<div class="inside">
						<?php $this->render_current_user_capabilities(); ?>
					</div>
				</div>

				<!-- Plugin Capabilities Status -->
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Plugin Capabilities Status', 'skylearn-flashcards' ); ?></h2>
					<div class="inside">
						<?php $this->render_plugin_capabilities_status(); ?>
					</div>
				</div>

				<!-- Role Capabilities -->
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Role Capabilities', 'skylearn-flashcards' ); ?></h2>
					<div class="inside">
						<?php $this->render_role_capabilities(); ?>
					</div>
				</div>

				<!-- Menu Access Test -->
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Menu Access Test', 'skylearn-flashcards' ); ?></h2>
					<div class="inside">
						<?php $this->render_menu_access_test(); ?>
					</div>
				</div>

				<!-- Capability Fix Tools -->
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Fix Tools', 'skylearn-flashcards' ); ?></h2>
					<div class="inside">
						<?php $this->render_fix_tools(); ?>
					</div>
				</div>

				<!-- System Information -->
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'System Information', 'skylearn-flashcards' ); ?></h2>
					<div class="inside">
						<?php $this->render_system_info(); ?>
					</div>
				</div>

			</div>
		</div>

		<style>
		.capability-test-container .postbox {
			margin-bottom: 20px;
		}
		.capability-status {
			display: inline-block;
			padding: 3px 8px;
			border-radius: 3px;
			font-size: 11px;
			font-weight: bold;
			text-transform: uppercase;
		}
		.capability-status.has-cap {
			background: #00a32a;
			color: white;
		}
		.capability-status.no-cap {
			background: #dc3232;
			color: white;
		}
		.capability-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 10px;
			margin: 10px 0;
		}
		.capability-item {
			padding: 10px;
			background: #f9f9f9;
			border-left: 4px solid #ccc;
		}
		.capability-item.has-capability {
			border-left-color: #00a32a;
		}
		.capability-item.no-capability {
			border-left-color: #dc3232;
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			$('#fix-admin-capabilities').on('click', function() {
				var button = $(this);
				button.prop('disabled', true).text('<?php esc_js_e( 'Fixing...', 'skylearn-flashcards' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'skylearn_test_capability_fix',
						nonce: '<?php echo wp_create_nonce( 'skylearn_capability_test' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							alert('<?php esc_js_e( 'Capabilities fixed! Reloading page...', 'skylearn-flashcards' ); ?>');
							location.reload();
						} else {
							alert('<?php esc_js_e( 'Error: ', 'skylearn-flashcards' ); ?>' + response.data.message);
						}
					},
					error: function() {
						alert('<?php esc_js_e( 'AJAX error occurred.', 'skylearn-flashcards' ); ?>');
					},
					complete: function() {
						button.prop('disabled', false).text('<?php esc_js_e( 'Fix Admin Capabilities', 'skylearn-flashcards' ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Render current user capabilities section
	 *
	 * @since    1.0.0
	 */
	private function render_current_user_capabilities() {
		$current_user = wp_get_current_user();

		?>
		<p><strong><?php esc_html_e( 'Current User:', 'skylearn-flashcards' ); ?></strong> 
		   <?php echo esc_html( $current_user->display_name ); ?> (<?php echo esc_html( implode( ', ', $current_user->roles ) ); ?>)</p>

		<div class="notice notice-info">
			<p><strong><?php esc_html_e( 'Simplified Access Model', 'skylearn-flashcards' ); ?></strong></p>
			<p><?php esc_html_e( 'SkyLearn Flashcards now uses a simplified access model where all logged-in users can access and edit flashcard sets. Premium features are controlled by license status only.', 'skylearn-flashcards' ); ?></p>
		</div>

		<h4><?php esc_html_e( 'Basic Access Status', 'skylearn-flashcards' ); ?></h4>
		<div class="capability-grid">
			<?php 
			$is_logged_in = is_user_logged_in();
			$is_premium = skylearn_is_premium();
			?>
			<div class="capability-item <?php echo $is_logged_in ? 'has-capability' : 'no-capability'; ?>">
				<strong><?php esc_html_e( 'Logged In User', 'skylearn-flashcards' ); ?></strong>
				<div class="capability-status <?php echo $is_logged_in ? 'has-cap' : 'no-cap'; ?>">
					<?php echo $is_logged_in ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
				</div>
				<p><small><?php esc_html_e( 'Can access all basic flashcard features', 'skylearn-flashcards' ); ?></small></p>
			</div>
			<div class="capability-item <?php echo $is_premium ? 'has-capability' : 'no-capability'; ?>">
				<strong><?php esc_html_e( 'Premium License', 'skylearn-flashcards' ); ?></strong>
				<div class="capability-status <?php echo $is_premium ? 'has-cap' : 'no-cap'; ?>">
					<?php echo $is_premium ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
				</div>
				<p><small><?php esc_html_e( 'Can access premium features like advanced reporting and unlimited sets', 'skylearn-flashcards' ); ?></small></p>
			</div>
		</div>

		<h4><?php esc_html_e( 'General WordPress Capabilities', 'skylearn-flashcards' ); ?></h4>
		<div class="capability-grid">
			<?php 
			$wp_caps = array( 'manage_options', 'edit_posts', 'publish_posts', 'edit_others_posts', 'delete_posts' );
			foreach ( $wp_caps as $cap ) : 
				$has_cap = current_user_can( $cap );
			?>
				<div class="capability-item <?php echo $has_cap ? 'has-capability' : 'no-capability'; ?>">
					<strong><?php echo esc_html( $cap ); ?></strong>
					<div class="capability-status <?php echo $has_cap ? 'has-cap' : 'no-cap'; ?>">
						<?php echo $has_cap ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render plugin capabilities status section
	 *
	 * @since    1.0.0
	 */
	private function render_plugin_capabilities_status() {
		?>
		<div class="capability-test-results">
			<h4><?php esc_html_e( 'Helper Function Tests', 'skylearn-flashcards' ); ?></h4>
			<ul>
				<li><strong>skylearn_current_user_can_manage():</strong> 
					<span class="capability-status <?php echo skylearn_current_user_can_manage() ? 'has-cap' : 'no-cap'; ?>">
						<?php echo skylearn_current_user_can_manage() ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
					</span>
				</li>
				<li><strong>skylearn_current_user_can_edit():</strong> 
					<span class="capability-status <?php echo skylearn_current_user_can_edit() ? 'has-cap' : 'no-cap'; ?>">
						<?php echo skylearn_current_user_can_edit() ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
					</span>
				</li>
				<li><strong>skylearn_current_user_can_view_analytics():</strong> 
					<span class="capability-status <?php echo skylearn_current_user_can_view_analytics() ? 'has-cap' : 'no-cap'; ?>">
						<?php echo skylearn_current_user_can_view_analytics() ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
					</span>
				</li>
				<li><strong>skylearn_current_user_has_any_capability():</strong> 
					<span class="capability-status <?php echo skylearn_current_user_has_any_capability() ? 'has-cap' : 'no-cap'; ?>">
						<?php echo skylearn_current_user_has_any_capability() ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
					</span>
				</li>
			</ul>

			<h4><?php esc_html_e( 'Set Limits', 'skylearn-flashcards' ); ?></h4>
			<ul>
				<li><strong><?php esc_html_e( 'Can create sets:', 'skylearn-flashcards' ); ?></strong> 
					<span class="capability-status <?php echo skylearn_user_can_create_set() ? 'has-cap' : 'no-cap'; ?>">
						<?php echo skylearn_user_can_create_set() ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
					</span>
				</li>
				<li><strong><?php esc_html_e( 'Current set count:', 'skylearn-flashcards' ); ?></strong> <?php echo skylearn_get_user_set_count(); ?></li>
				<li><strong><?php esc_html_e( 'Is premium:', 'skylearn-flashcards' ); ?></strong> 
					<span class="capability-status <?php echo skylearn_is_premium() ? 'has-cap' : 'no-cap'; ?>">
						<?php echo skylearn_is_premium() ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?>
					</span>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render role capabilities section
	 *
	 * @since    1.0.0
	 */
	private function render_role_capabilities() {
		?>
		<div class="notice notice-info">
			<p><strong><?php esc_html_e( 'No Role-Based Restrictions', 'skylearn-flashcards' ); ?></strong></p>
			<p><?php esc_html_e( 'All logged-in users, regardless of their WordPress role, can access and edit flashcard sets. The plugin no longer uses custom capabilities or role-based restrictions.', 'skylearn-flashcards' ); ?></p>
		</div>
		
		<h4><?php esc_html_e( 'Access Summary by Role', 'skylearn-flashcards' ); ?></h4>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Role', 'skylearn-flashcards' ); ?></th>
					<th><?php esc_html_e( 'Basic Features', 'skylearn-flashcards' ); ?></th>
					<th><?php esc_html_e( 'Premium Features', 'skylearn-flashcards' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
				global $wp_roles;
				foreach ( $wp_roles->roles as $role_name => $role_info ) : 
				?>
					<tr>
						<td><strong><?php echo esc_html( $role_info['name'] ); ?></strong></td>
						<td>
							<span class="capability-status has-cap">
								<?php esc_html_e( 'Full Access', 'skylearn-flashcards' ); ?>
							</span>
						</td>
						<td>
							<span class="capability-status <?php echo skylearn_is_premium() ? 'has-cap' : 'no-cap'; ?>">
								<?php echo skylearn_is_premium() ? esc_html__( 'Available', 'skylearn-flashcards' ) : esc_html__( 'License Required', 'skylearn-flashcards' ); ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render menu access test section
	 *
	 * @since    1.0.0
	 */
	private function render_menu_access_test() {
		$menu_items = array(
			'skylearn-flashcards' => 'read',
			'skylearn-flashcards-new' => 'read',
			'skylearn-flashcards-analytics' => 'read',
			'skylearn-flashcards-settings' => 'read',
		);

		if ( skylearn_is_premium() ) {
			$menu_items['skylearn-flashcards-leads'] = 'read';
			$menu_items['skylearn-flashcards-reports'] = 'read';
		}

		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Menu Page', 'skylearn-flashcards' ); ?></th>
					<th><?php esc_html_e( 'Required Capability', 'skylearn-flashcards' ); ?></th>
					<th><?php esc_html_e( 'Access Status', 'skylearn-flashcards' ); ?></th>
					<th><?php esc_html_e( 'URL', 'skylearn-flashcards' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $menu_items as $page_slug => $required_cap ) : ?>
					<?php $can_access = current_user_can( $required_cap ); ?>
					<tr>
						<td><code><?php echo esc_html( $page_slug ); ?></code></td>
						<td><code><?php echo esc_html( $required_cap ); ?></code></td>
						<td>
							<span class="capability-status <?php echo $can_access ? 'has-cap' : 'no-cap'; ?>">
								<?php echo $can_access ? esc_html__( 'Can Access', 'skylearn-flashcards' ) : esc_html__( 'No Access', 'skylearn-flashcards' ); ?>
							</span>
						</td>
						<td>
							<?php if ( $can_access ) : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page_slug ) ); ?>" target="_blank">
									<?php esc_html_e( 'Test Link', 'skylearn-flashcards' ); ?>
								</a>
							<?php else : ?>
								<em><?php esc_html_e( 'No access', 'skylearn-flashcards' ); ?></em>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render fix tools section
	 *
	 * @since    1.0.0
	 */
	private function render_fix_tools() {
		?>
		<div class="notice notice-info">
			<p><strong><?php esc_html_e( 'No Custom Capabilities Used', 'skylearn-flashcards' ); ?></strong></p>
			<p><?php esc_html_e( 'The plugin now uses WordPress\'s standard "read" capability for all admin menu access. All logged-in users can access the plugin features.', 'skylearn-flashcards' ); ?></p>
		</div>
		
		<h4><?php esc_html_e( 'Troubleshooting Steps', 'skylearn-flashcards' ); ?></h4>
		<ol>
			<li><?php esc_html_e( 'Ensure the user is logged in to WordPress.', 'skylearn-flashcards' ); ?></li>
			<li><?php esc_html_e( 'Clear any caching plugins that might be affecting admin pages.', 'skylearn-flashcards' ); ?></li>
			<li><?php esc_html_e( 'Check browser console for JavaScript errors.', 'skylearn-flashcards' ); ?></li>
			<li><?php esc_html_e( 'Check server error logs for any PHP errors.', 'skylearn-flashcards' ); ?></li>
		</ol>
		<?php
	}

	/**
	 * Render system information section
	 *
	 * @since    1.0.0
	 */
	private function render_system_info() {
		?>
		<table class="wp-list-table widefat fixed">
			<tbody>
				<tr>
					<td><strong><?php esc_html_e( 'WordPress Version', 'skylearn-flashcards' ); ?></strong></td>
					<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'PHP Version', 'skylearn-flashcards' ); ?></strong></td>
					<td><?php echo esc_html( PHP_VERSION ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Plugin Version', 'skylearn-flashcards' ); ?></strong></td>
					<td><?php echo esc_html( SKYLEARN_FLASHCARDS_VERSION ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Debug Mode', 'skylearn-flashcards' ); ?></strong></td>
					<td><?php echo defined( 'WP_DEBUG' ) && WP_DEBUG ? esc_html__( 'Enabled', 'skylearn-flashcards' ) : esc_html__( 'Disabled', 'skylearn-flashcards' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Multisite', 'skylearn-flashcards' ); ?></strong></td>
					<td><?php echo is_multisite() ? esc_html__( 'Yes', 'skylearn-flashcards' ) : esc_html__( 'No', 'skylearn-flashcards' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Active Theme', 'skylearn-flashcards' ); ?></strong></td>
					<td><?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * AJAX handler to fix admin capabilities
	 *
	 * @since    1.0.0
	 */
	public function ajax_test_capability_fix() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_capability_test' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'skylearn-flashcards' ) ) );
		}

		// Try to fix capabilities
		$fixed = skylearn_ensure_admin_capabilities();

		if ( $fixed ) {
			wp_send_json_success( array( 
				'message' => __( 'Admin capabilities have been restored.', 'skylearn-flashcards' ),
				'fixed' => true
			) );
		} else {
			wp_send_json_success( array( 
				'message' => __( 'Admin capabilities were already correct.', 'skylearn-flashcards' ),
				'fixed' => false
			) );
		}
	}
}

// Initialize the capability test page
new SkyLearn_Flashcards_Capability_Test();