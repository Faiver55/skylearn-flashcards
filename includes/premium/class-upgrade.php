<?php
/**
 * Upgrade management for SkyLearn Flashcards Premium
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 */

/**
 * Upgrade management class.
 *
 * Handles upgrade prompts, premium feature gating, and upgrade flow.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Upgrade {

	/**
	 * Premium features list
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $premium_features = array(
		'advanced_reporting' => 'Advanced Analytics & Reporting',
		'bulk_export'        => 'Bulk Export/Import',
		'unlimited_sets'     => 'Unlimited Flashcard Sets',
		'email_integration'  => 'Email Marketing Integration',
		'priority_support'   => 'Priority Support',
		'custom_branding'    => 'Custom Branding & White Label',
	);

	/**
	 * Free plan limits
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $free_limits = array(
		'max_sets'    => 5,
		'max_cards'   => 100,
		'max_exports' => 0,
	);

	/**
	 * Initialize upgrade management
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		
		// Add upgrade prompts and gates
		add_action( 'admin_notices', array( $this, 'upgrade_notices' ) );
		add_action( 'admin_footer', array( $this, 'upgrade_modal' ) );
		
		// Gate premium features
		add_filter( 'skylearn_can_create_set', array( $this, 'gate_set_creation' ), 10, 2 );
		add_filter( 'skylearn_can_export', array( $this, 'gate_export_feature' ) );
		add_filter( 'skylearn_can_access_reporting', array( $this, 'gate_reporting_feature' ) );
		
		// Add upgrade buttons to admin pages
		add_action( 'skylearn_admin_header', array( $this, 'add_upgrade_button' ) );
		add_action( 'skylearn_settings_tabs', array( $this, 'add_upgrade_tab' ) );
		
		// Handle upgrade actions
		add_action( 'wp_ajax_skylearn_dismiss_upgrade_notice', array( $this, 'dismiss_upgrade_notice' ) );
		
	}

	/**
	 * Display upgrade notices
	 *
	 * @since 1.0.0
	 */
	public function upgrade_notices() {
		
		$screen = get_current_screen();
		
		// Only show on SkyLearn pages
		if ( ! $screen || strpos( $screen->id, 'skylearn' ) === false ) {
			return;
		}
		
		// Don't show if already premium
		if ( skylearn_is_premium() ) {
			return;
		}
		
		// Check if notice was dismissed
		$dismissed = get_user_meta( get_current_user_id(), 'skylearn_upgrade_notice_dismissed', true );
		if ( $dismissed ) {
			return;
		}
		
		// Show upgrade notice
		?>
		<div class="notice notice-info is-dismissible skylearn-upgrade-notice" data-nonce="<?php echo wp_create_nonce( 'skylearn_upgrade_nonce' ); ?>">
			<div class="skylearn-upgrade-notice-content">
				<h3><?php _e( '🚀 Unlock Premium Features!', 'skylearn-flashcards' ); ?></h3>
				<p><?php _e( 'Get advanced analytics, unlimited flashcard sets, bulk export, and priority support with SkyLearn Flashcards Premium.', 'skylearn-flashcards' ); ?></p>
				<p>
					<a href="<?php echo $this->get_upgrade_url(); ?>" class="button button-primary" target="_blank">
						<?php _e( 'Upgrade Now', 'skylearn-flashcards' ); ?>
					</a>
					<a href="#" class="button skylearn-learn-more" data-toggle="skylearn-upgrade-modal">
						<?php _e( 'Learn More', 'skylearn-flashcards' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
		
	}

	/**
	 * Add upgrade modal
	 *
	 * @since 1.0.0
	 */
	public function upgrade_modal() {
		
		$screen = get_current_screen();
		
		// Only show on SkyLearn pages
		if ( ! $screen || strpos( $screen->id, 'skylearn' ) === false ) {
			return;
		}
		
		// Don't show if already premium
		if ( skylearn_is_premium() ) {
			return;
		}
		
		?>
		<div id="skylearn-upgrade-modal" class="skylearn-modal" style="display: none;">
			<div class="skylearn-modal-content">
				<span class="skylearn-modal-close">&times;</span>
				<h2><?php _e( 'SkyLearn Flashcards Premium', 'skylearn-flashcards' ); ?></h2>
				
				<div class="skylearn-premium-features">
					<?php foreach ( $this->premium_features as $feature_key => $feature_name ) : ?>
					<div class="skylearn-feature-item">
						<span class="dashicons dashicons-yes-alt"></span>
						<strong><?php echo esc_html( $feature_name ); ?></strong>
					</div>
					<?php endforeach; ?>
				</div>
				
				<div class="skylearn-pricing">
					<div class="skylearn-price-box">
						<h3><?php _e( 'Premium License', 'skylearn-flashcards' ); ?></h3>
						<div class="skylearn-price">
							<span class="currency">$</span>
							<span class="amount">49</span>
							<span class="period">/year</span>
						</div>
						<ul>
							<li><?php _e( 'Unlimited flashcard sets', 'skylearn-flashcards' ); ?></li>
							<li><?php _e( 'Advanced analytics', 'skylearn-flashcards' ); ?></li>
							<li><?php _e( 'Bulk export/import', 'skylearn-flashcards' ); ?></li>
							<li><?php _e( 'Email integrations', 'skylearn-flashcards' ); ?></li>
							<li><?php _e( 'Priority support', 'skylearn-flashcards' ); ?></li>
						</ul>
						<a href="<?php echo $this->get_upgrade_url(); ?>" class="button button-primary button-large" target="_blank">
							<?php _e( 'Upgrade Now', 'skylearn-flashcards' ); ?>
						</a>
					</div>
				</div>
				
				<p class="skylearn-guarantee">
					<?php _e( '30-day money-back guarantee. Cancel anytime.', 'skylearn-flashcards' ); ?>
				</p>
			</div>
		</div>
		<?php
		
	}

	/**
	 * Gate set creation based on limits
	 *
	 * @since 1.0.0
	 * @param bool $can_create Current permission
	 * @param int $user_id User ID
	 * @return bool Whether user can create set
	 */
	public function gate_set_creation( $can_create, $user_id = null ) {
		
		// Allow if premium
		if ( skylearn_is_premium() ) {
			return $can_create;
		}
		
		// Check free limits
		$user_id = $user_id ?: get_current_user_id();
		$set_count = $this->get_user_set_count( $user_id );
		
		if ( $set_count >= $this->free_limits['max_sets'] ) {
			return false;
		}
		
		return $can_create;
		
	}

	/**
	 * Gate export feature
	 *
	 * @since 1.0.0
	 * @param bool $can_export Current permission
	 * @return bool Whether user can export
	 */
	public function gate_export_feature( $can_export = true ) {
		
		// Allow if premium
		if ( skylearn_is_premium() ) {
			return $can_export;
		}
		
		// Deny for free users
		return false;
		
	}

	/**
	 * Gate reporting feature
	 *
	 * @since 1.0.0
	 * @param bool $can_access Current permission
	 * @return bool Whether user can access reporting
	 */
	public function gate_reporting_feature( $can_access = true ) {
		
		// Allow if premium
		if ( skylearn_is_premium() ) {
			return $can_access;
		}
		
		// Allow basic reporting for free users, deny advanced
		return false;
		
	}

	/**
	 * Add upgrade button to admin header
	 *
	 * @since 1.0.0
	 */
	public function add_upgrade_button() {
		
		// Don't show if already premium
		if ( skylearn_is_premium() ) {
			return;
		}
		
		?>
		<div class="skylearn-upgrade-button-container">
			<a href="<?php echo $this->get_upgrade_url(); ?>" class="button button-primary skylearn-upgrade-button" target="_blank">
				<span class="dashicons dashicons-star-filled"></span>
				<?php _e( 'Upgrade to Premium', 'skylearn-flashcards' ); ?>
			</a>
		</div>
		<?php
		
	}

	/**
	 * Add upgrade tab to settings
	 *
	 * @since 1.0.0
	 * @param array $tabs Current tabs
	 * @return array Updated tabs
	 */
	public function add_upgrade_tab( $tabs ) {
		
		// Don't show if already premium
		if ( skylearn_is_premium() ) {
			return $tabs;
		}
		
		$tabs['upgrade'] = __( 'Upgrade', 'skylearn-flashcards' );
		
		return $tabs;
		
	}

	/**
	 * Dismiss upgrade notice
	 *
	 * @since 1.0.0
	 */
	public function dismiss_upgrade_notice() {
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'skylearn_upgrade_nonce' ) ) {
			wp_die( __( 'Security check failed', 'skylearn-flashcards' ) );
		}
		
		// Save dismissal
		update_user_meta( get_current_user_id(), 'skylearn_upgrade_notice_dismissed', time() );
		
		wp_send_json_success();
		
	}

	/**
	 * Get upgrade URL
	 *
	 * @since 1.0.0
	 * @return string Upgrade URL
	 */
	public function get_upgrade_url() {
		
		$base_url = 'https://skyian.com/skylearn-flashcards/premium/';
		
		$args = array(
			'utm_source'   => 'plugin',
			'utm_medium'   => 'upgrade-link',
			'utm_campaign' => 'skylearn-flashcards',
			'site_url'     => urlencode( home_url() ),
		);
		
		return add_query_arg( $args, $base_url );
		
	}

	/**
	 * Get user's flashcard set count
	 *
	 * @since 1.0.0
	 * @param int $user_id User ID
	 * @return int Number of sets created by user
	 */
	private function get_user_set_count( $user_id ) {
		
		// TODO: Implement actual set counting based on your data structure
		// This is a stub implementation
		
		global $wpdb;
		
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'flashcard_set' 
			AND post_author = %d 
			AND post_status = 'publish'",
			$user_id
		) );
		
		return intval( $count );
		
	}

	/**
	 * Check if feature is available
	 *
	 * @since 1.0.0
	 * @param string $feature Feature key
	 * @return bool Whether feature is available
	 */
	public function is_feature_available( $feature ) {
		
		// All features available for premium users
		if ( skylearn_is_premium() ) {
			return true;
		}
		
		// Check specific feature availability for free users
		$free_features = array( 'basic_flashcards', 'basic_reporting' );
		
		return in_array( $feature, $free_features );
		
	}

	/**
	 * Get feature upgrade message
	 *
	 * @since 1.0.0
	 * @param string $feature Feature key
	 * @return string Upgrade message
	 */
	public function get_upgrade_message( $feature ) {
		
		$messages = array(
			'advanced_reporting' => __( 'Advanced analytics and reporting are available in the Premium version.', 'skylearn-flashcards' ),
			'bulk_export'        => __( 'Bulk export and import features are available in the Premium version.', 'skylearn-flashcards' ),
			'unlimited_sets'     => __( 'Create unlimited flashcard sets with the Premium version.', 'skylearn-flashcards' ),
			'email_integration'  => __( 'Email marketing integrations are available in the Premium version.', 'skylearn-flashcards' ),
		);
		
		$message = isset( $messages[ $feature ] ) ? $messages[ $feature ] : __( 'This feature is available in the Premium version.', 'skylearn-flashcards' );
		
		$message .= ' <a href="' . $this->get_upgrade_url() . '" target="_blank">' . __( 'Upgrade now', 'skylearn-flashcards' ) . '</a>';
		
		return $message;
		
	}

	/**
	 * Display upgrade prompt for gated feature
	 *
	 * @since 1.0.0
	 * @param string $feature Feature key
	 */
	public function show_upgrade_prompt( $feature ) {
		
		if ( skylearn_is_premium() ) {
			return;
		}
		
		?>
		<div class="skylearn-upgrade-prompt">
			<div class="skylearn-upgrade-prompt-content">
				<h4><?php _e( 'Premium Feature', 'skylearn-flashcards' ); ?></h4>
				<p><?php echo $this->get_upgrade_message( $feature ); ?></p>
				<a href="<?php echo $this->get_upgrade_url(); ?>" class="button button-primary" target="_blank">
					<?php _e( 'Upgrade to Premium', 'skylearn-flashcards' ); ?>
				</a>
			</div>
		</div>
		<?php
		
	}

}