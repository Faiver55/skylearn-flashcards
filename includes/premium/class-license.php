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
 * Handles license activation, validation, and management.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_License {

	/**
	 * License server URL
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $server_url    The license server URL
	 */
	private $server_url = 'https://skyian.com/api/license/';

	/**
	 * Product name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $product_name    The product name
	 */
	private $product_name = 'skylearn-flashcards';

	/**
	 * Initialize the class
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_skylearn_activate_license', array( $this, 'activate_license' ) );
		add_action( 'wp_ajax_skylearn_deactivate_license', array( $this, 'deactivate_license' ) );
		add_action( 'wp_ajax_skylearn_check_license', array( $this, 'check_license' ) );
		
		// Daily license check
		add_action( 'skylearn_daily_license_check', array( $this, 'daily_license_check' ) );
		
		// Schedule daily check if not already scheduled
		if ( ! wp_next_scheduled( 'skylearn_daily_license_check' ) ) {
			wp_schedule_event( time(), 'daily', 'skylearn_daily_license_check' );
		}
	}

	/**
	 * Activate license via AJAX
	 *
	 * @since    1.0.0
	 */
	public function activate_license() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_license_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$license_key = sanitize_text_field( $_POST['license_key'] ?? '' );

		if ( empty( $license_key ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please enter a license key.', 'skylearn-flashcards' )
			) );
		}

		$result = $this->validate_license( $license_key, 'activate' );

		if ( $result['success'] ) {
			update_option( 'skylearn_flashcards_license_key', $license_key );
			update_option( 'skylearn_flashcards_license_status', 'valid' );
			update_option( 'skylearn_flashcards_license_expires', $result['expires'] ?? '' );
			update_option( 'skylearn_flashcards_license_type', $result['license_type'] ?? 'standard' );

			wp_send_json_success( array(
				'message' => __( 'License activated successfully!', 'skylearn-flashcards' ),
				'license_type' => $result['license_type'] ?? 'standard',
				'expires' => $result['expires'] ?? ''
			) );
		} else {
			wp_send_json_error( array(
				'message' => $result['message'] ?? __( 'License activation failed.', 'skylearn-flashcards' )
			) );
		}
	}

	/**
	 * Deactivate license via AJAX
	 *
	 * @since    1.0.0
	 */
	public function deactivate_license() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_license_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$license_key = get_option( 'skylearn_flashcards_license_key', '' );

		if ( ! empty( $license_key ) ) {
			$this->validate_license( $license_key, 'deactivate' );
		}

		// Clear license data
		delete_option( 'skylearn_flashcards_license_key' );
		delete_option( 'skylearn_flashcards_license_status' );
		delete_option( 'skylearn_flashcards_license_expires' );
		delete_option( 'skylearn_flashcards_license_type' );

		wp_send_json_success( array(
			'message' => __( 'License deactivated successfully.', 'skylearn-flashcards' )
		) );
	}

	/**
	 * Check license status via AJAX
	 *
	 * @since    1.0.0
	 */
	public function check_license() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_license_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$license_key = get_option( 'skylearn_flashcards_license_key', '' );

		if ( empty( $license_key ) ) {
			wp_send_json_error( array(
				'message' => __( 'No license key found.', 'skylearn-flashcards' )
			) );
		}

		$result = $this->validate_license( $license_key, 'check' );

		if ( $result['success'] ) {
			update_option( 'skylearn_flashcards_license_status', 'valid' );
			update_option( 'skylearn_flashcards_license_expires', $result['expires'] ?? '' );

			wp_send_json_success( array(
				'message' => __( 'License is valid.', 'skylearn-flashcards' ),
				'expires' => $result['expires'] ?? '',
				'license_type' => $result['license_type'] ?? 'standard'
			) );
		} else {
			update_option( 'skylearn_flashcards_license_status', 'invalid' );

			wp_send_json_error( array(
				'message' => $result['message'] ?? __( 'License is invalid.', 'skylearn-flashcards' )
			) );
		}
	}

	/**
	 * Validate license with server
	 *
	 * @since    1.0.0
	 * @param    string   $license_key    License key to validate
	 * @param    string   $action         Action to perform (activate, deactivate, check)
	 * @return   array                    Validation result
	 */
	private function validate_license( $license_key, $action = 'check' ) {
		$api_params = array(
			'action'      => $action,
			'license_key' => $license_key,
			'product'     => $this->product_name,
			'domain'      => home_url(),
			'site_name'   => get_bloginfo( 'name' ),
			'version'     => SKYLEARN_FLASHCARDS_VERSION,
		);

		$response = wp_remote_post( $this->server_url, array(
			'timeout'   => 15,
			'sslverify' => true,
			'body'      => $api_params
		) );

		// Handle connection errors
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Unable to connect to license server. Please try again.', 'skylearn-flashcards' )
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			return array(
				'success' => false,
				'message' => __( 'License server returned an error. Please try again.', 'skylearn-flashcards' )
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid response from license server.', 'skylearn-flashcards' )
			);
		}

		return $data;
	}

	/**
	 * Daily license check
	 *
	 * @since    1.0.0
	 */
	public function daily_license_check() {
		$license_key = get_option( 'skylearn_flashcards_license_key', '' );

		if ( empty( $license_key ) ) {
			return;
		}

		$result = $this->validate_license( $license_key, 'check' );

		if ( $result['success'] ) {
			update_option( 'skylearn_flashcards_license_status', 'valid' );
			update_option( 'skylearn_flashcards_license_expires', $result['expires'] ?? '' );
		} else {
			update_option( 'skylearn_flashcards_license_status', 'invalid' );

			// Send notification to admin about invalid license
			$admin_email = get_option( 'admin_email' );
			$subject = sprintf( __( 'License Issue - %s', 'skylearn-flashcards' ), get_bloginfo( 'name' ) );
			$message = sprintf(
				__( 'Your SkyLearn Flashcards premium license is no longer valid. Please check your license status in the WordPress admin.', 'skylearn-flashcards' )
			);

			wp_mail( $admin_email, $subject, $message );
		}
	}

	/**
	 * Get license status information
	 *
	 * @since    1.0.0
	 * @return   array    License status information
	 */
	public static function get_license_info() {
		return array(
			'key'        => get_option( 'skylearn_flashcards_license_key', '' ),
			'status'     => get_option( 'skylearn_flashcards_license_status', 'inactive' ),
			'expires'    => get_option( 'skylearn_flashcards_license_expires', '' ),
			'type'       => get_option( 'skylearn_flashcards_license_type', '' ),
			'is_valid'   => get_option( 'skylearn_flashcards_license_status', 'inactive' ) === 'valid'
		);
	}

	/**
	 * Check if license is about to expire
	 *
	 * @since    1.0.0
	 * @param    int      $days    Days before expiration to check
	 * @return   bool              True if license expires within specified days
	 */
	public static function is_license_expiring( $days = 30 ) {
		$expires = get_option( 'skylearn_flashcards_license_expires', '' );

		if ( empty( $expires ) ) {
			return false;
		}

		$expires_timestamp = strtotime( $expires );
		$warning_timestamp = time() + ( $days * DAY_IN_SECONDS );

		return $expires_timestamp <= $warning_timestamp;
	}

}