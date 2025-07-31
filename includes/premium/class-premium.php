<?php
/**
 * Premium functionality for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 */

/**
 * Premium functionality class.
 *
 * Handles premium features, licensing, and restrictions.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Premium {

	/**
	 * Initialize premium functionality
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		// Add premium hooks
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_notices', array( $this, 'license_notice' ) );
		
		// Initialize license and upgrade management
		$this->init_license_management();
		$this->init_upgrade_management();
		
	}

	/**
	 * Initialize premium features
	 *
	 * @since    1.0.0
	 */
	public function init() {
		
		// Only proceed if premium is active
		if ( ! skylearn_is_premium() ) {
			return;
		}

		// Load premium modules
		$this->load_premium_modules();
		
	}

	/**
	 * Load premium modules
	 *
	 * @since    1.0.0
	 */
	private function load_premium_modules() {
		
		// Advanced reporting
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-advanced-reporting.php';
		new SkyLearn_Flashcards_Advanced_Reporting();
		
		// Export functionality
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-export.php';
		new SkyLearn_Flashcards_Export();
		
		// LMS integrations (premium features)
		$this->load_lms_integrations();
		
		// Email marketing integrations
		$this->load_email_integrations();
		
	}

	/**
	 * Load LMS integrations
	 *
	 * @since    1.0.0
	 */
	private function load_lms_integrations() {
		
		// LearnDash integration
		if ( class_exists( 'SFWD_LMS' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/lms/class-learndash.php';
		}
		
		// TutorLMS integration
		if ( function_exists( 'tutor' ) ) {
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/lms/class-tutorlms.php';
		}
		
	}

	/**
	 * Load email marketing integrations
	 *
	 * @since    1.0.0
	 */
	private function load_email_integrations() {
		
		$email_settings = get_option( 'skylearn_flashcards_email_settings', array() );
		$provider = $email_settings['provider'] ?? '';
		
		switch ( $provider ) {
			case 'mailchimp':
				require_once SKYLEARN_FLASHCARDS_PATH . 'includes/integrations/class-mailchimp.php';
				break;
			case 'sendfox':
				require_once SKYLEARN_FLASHCARDS_PATH . 'includes/integrations/class-sendfox.php';
				break;
			case 'vbout':
				require_once SKYLEARN_FLASHCARDS_PATH . 'includes/integrations/class-vbout.php';
				break;
		}
		
	}

	/**
	 * Show license notice if needed
	 *
	 * @since    1.0.0
	 */
	public function license_notice() {
		
		// Only show on plugin pages
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'skylearn-flashcards' ) === false ) {
			return;
		}

		$license_status = get_option( 'skylearn_flashcards_license_status', 'inactive' );
		
		if ( $license_status !== 'valid' ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<strong><?php esc_html_e( 'SkyLearn Flashcards Premium', 'skylearn-flashcards' ); ?></strong>
					<?php esc_html_e( 'Enter your license key to unlock premium features like advanced analytics, lead collection, and unlimited flashcard sets.', 'skylearn-flashcards' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=skylearn-flashcards-settings#premium' ) ); ?>"><?php esc_html_e( 'Enter License Key', 'skylearn-flashcards' ); ?></a>
				</p>
			</div>
			<?php
		}
		
	}

	/**
	 * Validate license key
	 *
	 * @since    1.0.0
	 * @param    string   $license_key    License key to validate
	 * @return   bool                     True if valid, false otherwise
	 */
	public function validate_license( $license_key ) {
		
		// Placeholder for license validation logic
		// In a real implementation, this would check against your licensing server
		
		$response = wp_remote_get( 'https://skyian.com/api/validate-license', array(
			'body' => array(
				'license_key' => $license_key,
				'product'     => 'skylearn-flashcards',
				'domain'      => home_url(),
			),
		) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		return isset( $data['valid'] ) && $data['valid'] === true;
		
	}

	/**
	 * Check if feature is available in current license
	 *
	 * @since    1.0.0
	 * @param    string   $feature    Feature name
	 * @return   bool                 True if available, false otherwise
	 */
	public static function is_feature_available( $feature ) {
		
		// If no premium license, only basic features are available
		if ( ! skylearn_is_premium() ) {
			$basic_features = array( 'basic_flashcards', 'basic_analytics', 'shortcode', 'gutenberg_block' );
			return in_array( $feature, $basic_features );
		}
		
		// All features available with premium license
		return true;
		
	}

	/**
	 * Get upgrade URL for specific feature
	 *
	 * @since    1.0.0
	 * @param    string   $feature    Feature name
	 * @return   string               Upgrade URL
	 */
	public static function get_upgrade_url( $feature = '' ) {
		
		$base_url = 'https://skyian.com/skylearn-flashcards/premium/';
		
		$utm_params = array(
			'utm_source'   => 'plugin',
			'utm_medium'   => 'upgrade-link',
			'utm_campaign' => 'skylearn-flashcards',
		);
		
		if ( ! empty( $feature ) ) {
			$utm_params['utm_content'] = $feature;
		}
		
		return add_query_arg( $utm_params, $base_url );
		
	}

	/**
	 * Initialize license management
	 *
	 * @since 1.0.0
	 */
	private function init_license_management() {
		
		// Load license class
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-license.php';
		new SkyLearn_Flashcards_License();
		
	}

	/**
	 * Initialize upgrade management
	 *
	 * @since 1.0.0
	 */
	private function init_upgrade_management() {
		
		// Load upgrade class
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/premium/class-upgrade.php';
		new SkyLearn_Flashcards_Upgrade();
		
	}

}