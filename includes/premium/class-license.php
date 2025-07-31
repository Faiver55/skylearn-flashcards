<?php
/**
 * License management for SkyLearn Flashcards Premium
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 */

/**
 * License management class.
 *
 * Handles license validation, activation, and deactivation for premium features.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_License {

	/**
	 * License server URL
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $license_server = 'https://skyian.com/';

	/**
	 * Product ID
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $product_id = 'skylearn-flashcards';

	/**
	 * License option name
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $license_option = 'skylearn_flashcards_license';

	/**
	 * License status option name
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $license_status_option = 'skylearn_flashcards_license_status';

	/**
	 * Initialize license management
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		
		// Add admin hooks
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_notices', array( $this, 'license_notices' ) );
		
		// AJAX handlers for license operations
		add_action( 'wp_ajax_skylearn_activate_license', array( $this, 'activate_license' ) );
		add_action( 'wp_ajax_skylearn_deactivate_license', array( $this, 'deactivate_license' ) );
		add_action( 'wp_ajax_skylearn_check_license', array( $this, 'check_license' ) );
		
		// Periodic license check
		add_action( 'skylearn_daily_license_check', array( $this, 'check_license' ) );
		
		// Schedule daily license check if not already scheduled
		if ( ! wp_next_scheduled( 'skylearn_daily_license_check' ) ) {
			wp_schedule_event( time(), 'daily', 'skylearn_daily_license_check' );
		}
		
	}

	/**
	 * Initialize admin settings
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		
		// Register license settings
		register_setting( 'skylearn_flashcards_license', $this->license_option, 'sanitize_text_field' );
		
		// Add license settings section
		add_settings_section(
			'skylearn_license_section',
			__( 'License Settings', 'skylearn-flashcards' ),
			array( $this, 'license_section_callback' ),
			'skylearn_flashcards_license'
		);
		
		// Add license key field
		add_settings_field(
			'skylearn_license_key',
			__( 'License Key', 'skylearn-flashcards' ),
			array( $this, 'license_key_callback' ),
			'skylearn_flashcards_license',
			'skylearn_license_section'
		);
		
	}

	/**
	 * License section callback
	 *
	 * @since 1.0.0
	 */
	public function license_section_callback() {
		echo '<p>' . __( 'Enter your SkyLearn Flashcards Premium license key to unlock premium features.', 'skylearn-flashcards' ) . '</p>';
	}

	/**
	 * License key field callback
	 *
	 * @since 1.0.0
	 */
	public function license_key_callback() {
		$license = get_option( $this->license_option );
		$status = get_option( $this->license_status_option );
		
		echo '<input type="text" id="skylearn_license_key" name="' . $this->license_option . '" value="' . esc_attr( $license ) . '" class="regular-text" />';
		
		if ( $status === 'valid' ) {
			echo '<span class="skylearn-license-status valid">' . __( '✓ Active', 'skylearn-flashcards' ) . '</span>';
			echo '<button type="button" class="button skylearn-deactivate-license" data-nonce="' . wp_create_nonce( 'skylearn_license_nonce' ) . '">' . __( 'Deactivate', 'skylearn-flashcards' ) . '</button>';
		} else {
			echo '<span class="skylearn-license-status invalid">' . __( '✗ Inactive', 'skylearn-flashcards' ) . '</span>';
			echo '<button type="button" class="button-primary skylearn-activate-license" data-nonce="' . wp_create_nonce( 'skylearn_license_nonce' ) . '">' . __( 'Activate', 'skylearn-flashcards' ) . '</button>';
		}
		
		echo '<p class="description">' . __( 'Enter your license key from your purchase confirmation email.', 'skylearn-flashcards' ) . '</p>';
	}

	/**
	 * Activate license
	 *
	 * @since 1.0.0
	 */
	public function activate_license() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'skylearn_license_nonce' ) ) {
			wp_die( __( 'Security check failed', 'skylearn-flashcards' ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'skylearn-flashcards' ) );
		}
		
		$license_key = sanitize_text_field( $_POST['license_key'] );
		
		// TODO: Implement actual license server communication
		$response = $this->make_license_request( 'activate', $license_key );
		
		if ( $response && $response['success'] ) {
			update_option( $this->license_option, $license_key );
			update_option( $this->license_status_option, 'valid' );
			wp_send_json_success( array( 'message' => __( 'License activated successfully!', 'skylearn-flashcards' ) ) );
		} else {
			$message = isset( $response['message'] ) ? $response['message'] : __( 'License activation failed.', 'skylearn-flashcards' );
			wp_send_json_error( array( 'message' => $message ) );
		}
		
	}

	/**
	 * Deactivate license
	 *
	 * @since 1.0.0
	 */
	public function deactivate_license() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'skylearn_license_nonce' ) ) {
			wp_die( __( 'Security check failed', 'skylearn-flashcards' ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'skylearn-flashcards' ) );
		}
		
		$license_key = get_option( $this->license_option );
		
		// TODO: Implement actual license server communication
		$response = $this->make_license_request( 'deactivate', $license_key );
		
		// Always deactivate locally regardless of server response
		update_option( $this->license_status_option, 'invalid' );
		
		wp_send_json_success( array( 'message' => __( 'License deactivated successfully!', 'skylearn-flashcards' ) ) );
		
	}

	/**
	 * Check license status
	 *
	 * @since 1.0.0
	 */
	public function check_license() {
		
		$license_key = get_option( $this->license_option );
		
		if ( empty( $license_key ) ) {
			update_option( $this->license_status_option, 'invalid' );
			return false;
		}
		
		// TODO: Implement actual license server communication
		$response = $this->make_license_request( 'check', $license_key );
		
		$status = ( $response && $response['success'] ) ? 'valid' : 'invalid';
		update_option( $this->license_status_option, $status );
		
		return $status === 'valid';
		
	}

	/**
	 * Make license request to server
	 *
	 * @since 1.0.0
	 * @param string $action The action to perform (activate, deactivate, check)
	 * @param string $license_key The license key
	 * @return array|false Response from server or false on failure
	 */
	private function make_license_request( $action, $license_key ) {
		
		// TODO: Implement actual license server API communication
		// This is a stub implementation for development
		
		$args = array(
			'body' => array(
				'action'      => $action,
				'license_key' => $license_key,
				'product_id'  => $this->product_id,
				'site_url'    => home_url(),
			),
			'timeout' => 30,
		);
		
		$response = wp_remote_post( $this->license_server . 'wp-json/skylearn/v1/license', $args );
		
		if ( is_wp_error( $response ) ) {
			error_log( 'SkyLearn License Error: ' . $response->get_error_message() );
			return false;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		// For development/testing, always return success
		// Replace with actual server response handling
		return array(
			'success' => true,
			'message' => __( 'License operation completed', 'skylearn-flashcards' )
		);
		
	}

	/**
	 * Display license notices
	 *
	 * @since 1.0.0
	 */
	public function license_notices() {
		
		$screen = get_current_screen();
		
		// Only show on SkyLearn pages
		if ( ! $screen || strpos( $screen->id, 'skylearn' ) === false ) {
			return;
		}
		
		$status = get_option( $this->license_status_option );
		
		if ( $status !== 'valid' ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php _e( 'SkyLearn Flashcards Premium features are not active.', 'skylearn-flashcards' ); ?>
					<a href="<?php echo admin_url( 'admin.php?page=skylearn-flashcards-settings&tab=license' ); ?>">
						<?php _e( 'Activate your license', 'skylearn-flashcards' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
		
	}

	/**
	 * Check if license is valid
	 *
	 * @since 1.0.0
	 * @return bool True if license is valid
	 */
	public function is_valid() {
		return get_option( $this->license_status_option ) === 'valid';
	}

	/**
	 * Get license key
	 *
	 * @since 1.0.0
	 * @return string License key or empty string
	 */
	public function get_license_key() {
		return get_option( $this->license_option, '' );
	}

	/**
	 * Get license status
	 *
	 * @since 1.0.0
	 * @return string License status (valid, invalid, expired, etc.)
	 */
	public function get_status() {
		return get_option( $this->license_status_option, 'invalid' );
	}

}