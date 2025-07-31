<?php
/**
 * Upgrade flow and premium upsells for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 */

/**
 * Upgrade flow class.
 *
 * Handles premium upgrade prompts, CTAs, and conversion flow.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/premium
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Upgrade {

	/**
	 * Initialize the upgrade flow
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Only show upgrade prompts for non-premium users
		if ( ! skylearn_is_premium() ) {
			add_action( 'admin_notices', array( $this, 'upgrade_notices' ) );
			add_action( 'wp_ajax_skylearn_dismiss_upgrade_notice', array( $this, 'dismiss_upgrade_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_upgrade_scripts' ) );
		}

		// Add upgrade links to plugin actions
		add_filter( 'plugin_action_links_' . SKYLEARN_FLASHCARDS_BASENAME, array( $this, 'add_plugin_action_links' ) );
	}

	/**
	 * Display upgrade notices
	 *
	 * @since    1.0.0
	 */
	public function upgrade_notices() {
		$screen = get_current_screen();
		
		// Only show on plugin pages
		if ( ! $screen || strpos( $screen->id, 'skylearn-flashcards' ) === false ) {
			return;
		}

		// Check if user has dismissed the notice
		$dismissed = get_user_meta( get_current_user_id(), 'skylearn_dismissed_upgrade_notice', true );
		if ( $dismissed ) {
			return;
		}

		// Show different messages based on context
		$context = $this->get_upgrade_context();
		$message = $this->get_upgrade_message( $context );

		if ( ! empty( $message ) ) {
			?>
			<div class="notice notice-info is-dismissible skylearn-upgrade-notice" data-notice="upgrade">
				<div class="skylearn-upgrade-notice-content">
					<div class="skylearn-upgrade-icon">
						<span class="dashicons dashicons-star-filled"></span>
					</div>
					<div class="skylearn-upgrade-text">
						<h3><?php esc_html_e( 'Unlock Premium Features!', 'skylearn-flashcards' ); ?></h3>
						<p><?php echo wp_kses_post( $message ); ?></p>
						<p>
							<a href="<?php echo esc_url( $this->get_upgrade_url( $context ) ); ?>" class="button-primary" target="_blank">
								<?php esc_html_e( 'Upgrade to Premium', 'skylearn-flashcards' ); ?>
							</a>
							<a href="#" class="button-secondary skylearn-dismiss-notice" data-notice="upgrade">
								<?php esc_html_e( 'Maybe Later', 'skylearn-flashcards' ); ?>
							</a>
						</p>
					</div>
				</div>
			</div>
			<style>
			.skylearn-upgrade-notice-content {
				display: flex;
				align-items: flex-start;
				gap: 15px;
			}
			.skylearn-upgrade-icon {
				color: #f39c12;
				font-size: 24px;
				margin-top: 5px;
			}
			.skylearn-upgrade-text h3 {
				margin: 0 0 10px 0;
				color: #23282d;
			}
			.skylearn-upgrade-text p {
				margin: 0 0 15px 0;
			}
			</style>
			<?php
		}
	}

	/**
	 * Get upgrade context based on current page
	 *
	 * @since    1.0.0
	 * @return   string    Upgrade context
	 */
	private function get_upgrade_context() {
		$screen = get_current_screen();
		
		if ( ! $screen ) {
			return 'general';
		}

		if ( strpos( $screen->id, 'analytics' ) !== false ) {
			return 'analytics';
		}

		if ( strpos( $screen->id, 'export' ) !== false ) {
			return 'export';
		}

		if ( strpos( $screen->id, 'leads' ) !== false ) {
			return 'leads';
		}

		if ( strpos( $screen->id, 'settings' ) !== false ) {
			return 'settings';
		}

		return 'general';
	}

	/**
	 * Get upgrade message based on context
	 *
	 * @since    1.0.0
	 * @param    string   $context    Upgrade context
	 * @return   string               Upgrade message
	 */
	private function get_upgrade_message( $context ) {
		$messages = array(
			'analytics' => __( 'Get detailed insights with advanced reporting, user progress tracking, and performance analytics. See which flashcards are most effective and identify learning patterns.', 'skylearn-flashcards' ),
			'export'    => __( 'Export your flashcard sets to PDF, CSV, or other formats. Create printable study materials and share your content across platforms.', 'skylearn-flashcards' ),
			'leads'     => __( 'Collect student information and build your email list. Connect with Mailchimp, SendFox, and other email marketing services.', 'skylearn-flashcards' ),
			'settings'  => __( 'Unlock advanced features like spaced repetition, difficulty adjustment, unlimited flashcard sets, and premium integrations.', 'skylearn-flashcards' ),
			'general'   => __( 'Unlock unlimited flashcard sets, advanced analytics, lead collection, export functionality, and premium support.', 'skylearn-flashcards' )
		);

		return $messages[ $context ] ?? $messages['general'];
	}

	/**
	 * Get upgrade URL with tracking parameters
	 *
	 * @since    1.0.0
	 * @param    string   $context    Upgrade context
	 * @return   string               Upgrade URL
	 */
	private function get_upgrade_url( $context = 'general' ) {
		$base_url = 'https://skyian.com/skylearn-flashcards/premium/';
		
		$utm_params = array(
			'utm_source'   => 'plugin',
			'utm_medium'   => 'upgrade-notice',
			'utm_campaign' => 'skylearn-flashcards',
			'utm_content'  => $context
		);

		return add_query_arg( $utm_params, $base_url );
	}

	/**
	 * Dismiss upgrade notice via AJAX
	 *
	 * @since    1.0.0
	 */
	public function dismiss_upgrade_notice() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_upgrade_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$notice_type = sanitize_text_field( $_POST['notice'] ?? '' );

		if ( $notice_type === 'upgrade' ) {
			update_user_meta( get_current_user_id(), 'skylearn_dismissed_upgrade_notice', time() );
		}

		wp_send_json_success();
	}

	/**
	 * Enqueue upgrade-related scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_upgrade_scripts() {
		$screen = get_current_screen();
		
		// Only enqueue on plugin pages
		if ( ! $screen || strpos( $screen->id, 'skylearn-flashcards' ) === false ) {
			return;
		}

		wp_enqueue_script( 'skylearn-upgrade', SKYLEARN_FLASHCARDS_URL . 'assets/js/upgrade.js', array( 'jquery' ), SKYLEARN_FLASHCARDS_VERSION, true );
		wp_localize_script( 'skylearn-upgrade', 'skylearn_upgrade', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'skylearn_upgrade_nonce' )
		) );
	}

	/**
	 * Add upgrade link to plugin action links
	 *
	 * @since    1.0.0
	 * @param    array    $links    Existing plugin action links
	 * @return   array              Modified plugin action links
	 */
	public function add_plugin_action_links( $links ) {
		if ( ! skylearn_is_premium() ) {
			$upgrade_link = sprintf(
				'<a href="%s" target="_blank" style="color: #f39c12; font-weight: bold;">%s</a>',
				esc_url( $this->get_upgrade_url( 'plugin-list' ) ),
				esc_html__( 'Go Premium', 'skylearn-flashcards' )
			);
			
			array_unshift( $links, $upgrade_link );
		}

		return $links;
	}

	/**
	 * Generate upgrade CTA for feature gates
	 *
	 * @since    1.0.0
	 * @param    string   $feature       Feature name
	 * @param    string   $description   Feature description
	 * @param    bool     $inline        Whether to show inline or as a modal
	 * @return   string                  HTML for upgrade CTA
	 */
	public static function get_feature_upgrade_cta( $feature, $description = '', $inline = true ) {
		if ( skylearn_is_premium() ) {
			return '';
		}

		$upgrade_url = add_query_arg( array(
			'utm_source'   => 'plugin',
			'utm_medium'   => 'feature-gate',
			'utm_campaign' => 'skylearn-flashcards',
			'utm_content'  => $feature
		), 'https://skyian.com/skylearn-flashcards/premium/' );

		if ( $inline ) {
			ob_start();
			?>
			<div class="skylearn-feature-upgrade-cta">
				<div class="skylearn-upgrade-content">
					<span class="dashicons dashicons-lock"></span>
					<div class="skylearn-upgrade-text">
						<h4><?php esc_html_e( 'Premium Feature', 'skylearn-flashcards' ); ?></h4>
						<?php if ( ! empty( $description ) ) : ?>
							<p><?php echo esc_html( $description ); ?></p>
						<?php endif; ?>
						<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button-primary" target="_blank">
							<?php esc_html_e( 'Upgrade to Unlock', 'skylearn-flashcards' ); ?>
						</a>
					</div>
				</div>
			</div>
			<style>
			.skylearn-feature-upgrade-cta {
				background: #f8f9fa;
				border: 2px dashed #ddd;
				border-radius: 8px;
				padding: 20px;
				text-align: center;
				margin: 20px 0;
			}
			.skylearn-upgrade-content {
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 15px;
			}
			.skylearn-upgrade-content .dashicons {
				font-size: 32px;
				color: #f39c12;
			}
			.skylearn-upgrade-text h4 {
				margin: 0 0 10px 0;
				color: #23282d;
			}
			.skylearn-upgrade-text p {
				margin: 0 0 15px 0;
				color: #666;
			}
			</style>
			<?php
			return ob_get_clean();
		} else {
			// Modal version would go here
			return '';
		}
	}

	/**
	 * Show upgrade overlay for premium features
	 *
	 * @since    1.0.0
	 * @param    string   $feature    Feature name
	 * @return   string               HTML for upgrade overlay
	 */
	public static function get_premium_overlay( $feature ) {
		if ( skylearn_is_premium() ) {
			return '';
		}

		$upgrade_url = add_query_arg( array(
			'utm_source'   => 'plugin',
			'utm_medium'   => 'premium-overlay',
			'utm_campaign' => 'skylearn-flashcards',
			'utm_content'  => $feature
		), 'https://skyian.com/skylearn-flashcards/premium/' );

		ob_start();
		?>
		<div class="skylearn-premium-overlay">
			<div class="skylearn-premium-overlay-content">
				<span class="dashicons dashicons-star-filled"></span>
				<h3><?php esc_html_e( 'Premium Feature', 'skylearn-flashcards' ); ?></h3>
				<p><?php esc_html_e( 'This feature is only available in the premium version.', 'skylearn-flashcards' ); ?></p>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button-primary" target="_blank">
					<?php esc_html_e( 'Upgrade Now', 'skylearn-flashcards' ); ?>
				</a>
			</div>
		</div>
		<style>
		.skylearn-premium-overlay {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(255, 255, 255, 0.9);
			display: flex;
			align-items: center;
			justify-content: center;
			z-index: 999;
		}
		.skylearn-premium-overlay-content {
			text-align: center;
			padding: 30px;
			background: white;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		}
		.skylearn-premium-overlay-content .dashicons {
			font-size: 48px;
			color: #f39c12;
			margin-bottom: 15px;
		}
		.skylearn-premium-overlay-content h3 {
			margin: 0 0 15px 0;
			color: #23282d;
		}
		.skylearn-premium-overlay-content p {
			margin: 0 0 20px 0;
			color: #666;
		}
		</style>
		<?php
		return ob_get_clean();
	}

}