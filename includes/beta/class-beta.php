<?php
/**
 * Beta functionality for SkyLearn Flashcards
 *
 * Handles beta-specific features including feedback collection,
 * enhanced debugging, and beta tester experience.
 *
 * @link       https://skyian.com/
 * @since      1.0.0-beta
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/beta
 */

/**
 * Beta functionality class.
 *
 * Manages beta-specific features, feedback collection, and testing tools.
 *
 * @since      1.0.0-beta
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/beta
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Beta {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0-beta
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_beta_features' ) );
		add_action( 'admin_menu', array( $this, 'add_beta_menu' ) );
		add_action( 'admin_notices', array( $this, 'show_beta_notice' ) );
		add_action( 'wp_ajax_skylearn_beta_feedback', array( $this, 'handle_beta_feedback' ) );
		add_action( 'wp_ajax_skylearn_dismiss_beta_notice', array( $this, 'dismiss_beta_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_beta_scripts' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_beta_dashboard_widget' ) );
	}

	/**
	 * Check if this is a beta version.
	 *
	 * @since 1.0.0-beta
	 * @return bool
	 */
	public function is_beta_version() {
		return strpos( SKYLEARN_FLASHCARDS_VERSION, 'beta' ) !== false;
	}

	/**
	 * Initialize beta-specific features.
	 *
	 * @since 1.0.0-beta
	 */
	public function init_beta_features() {
		if ( ! $this->is_beta_version() ) {
			return;
		}

		// Initialize beta settings
		$this->init_beta_settings();
	}

	/**
	 * Initialize beta settings.
	 *
	 * @since 1.0.0-beta
	 */
	private function init_beta_settings() {
		$beta_settings = get_option( 'skylearn_flashcards_beta_settings', array() );
		
		$default_settings = array(
			'debug_mode' => true,
			'feedback_widget_enabled' => true,
			'performance_monitoring' => true,
			'experimental_features' => false,
			'beta_tester_name' => '',
			'beta_tester_email' => '',
			'feedback_submitted' => false,
		);

		$beta_settings = wp_parse_args( $beta_settings, $default_settings );
		update_option( 'skylearn_flashcards_beta_settings', $beta_settings );
	}

	/**
	 * Add beta menu to admin.
	 *
	 * @since 1.0.0-beta
	 */
	public function add_beta_menu() {
		if ( ! $this->is_beta_version() ) {
			return;
		}

		add_submenu_page(
			'edit.php?post_type=skylearn_flashcard',
			__( 'Beta Feedback', 'skylearn-flashcards' ),
			__( '🧪 Beta Feedback', 'skylearn-flashcards' ),
			'edit_posts',
			'skylearn-beta-feedback',
			array( $this, 'beta_feedback_page' )
		);

		add_submenu_page(
			'edit.php?post_type=skylearn_flashcard',
			__( 'Beta Settings', 'skylearn-flashcards' ),
			__( '⚙️ Beta Settings', 'skylearn-flashcards' ),
			'manage_options',
			'skylearn-beta-settings',
			array( $this, 'beta_settings_page' )
		);
	}

	/**
	 * Show beta notice to users.
	 *
	 * @since 1.0.0-beta
	 */
	public function show_beta_notice() {
		if ( ! $this->is_beta_version() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'skylearn' ) === false ) {
			return;
		}

		// Don't show if user has dismissed it
		if ( get_user_meta( get_current_user_id(), 'skylearn_beta_notice_dismissed', true ) ) {
			return;
		}

		?>
		<div class="notice notice-warning is-dismissible skylearn-beta-notice">
			<div style="display: flex; align-items: center; padding: 10px 0;">
				<div style="flex: 1;">
					<h3 style="margin: 0 0 10px 0; color: #ff6b35;">
						🧪 Beta Testing Mode Active
					</h3>
					<p style="margin: 0 0 10px 0;">
						<strong>Welcome to SkyLearn Flashcards Beta!</strong> You're using version <code><?php echo esc_html( SKYLEARN_FLASHCARDS_VERSION ); ?></code>. 
						Your feedback is crucial for improving this plugin.
					</p>
					<p style="margin: 0;">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=skylearn_flashcard&page=skylearn-beta-feedback' ) ); ?>" class="button button-primary">
							📝 Provide Feedback
						</a>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=skylearn_flashcard&page=skylearn-beta-settings' ) ); ?>" class="button">
							⚙️ Beta Settings
						</a>
						<a href="https://github.com/Faiver55/skylearn-flashcards/blob/main/docs/ONBOARDING.md" target="_blank" class="button">
							📖 Beta Guide
						</a>
					</p>
				</div>
				<div style="margin-left: 20px;">
					<span class="beta-badge" style="background: #ff6b35; color: white; padding: 5px 10px; border-radius: 3px; font-weight: bold; font-size: 12px;">
						BETA VERSION
					</span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Dismiss beta notice.
	 *
	 * @since 1.0.0-beta
	 */
	public function dismiss_beta_notice() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'skylearn_beta_notice' ) ) {
			wp_die( 'Security check failed' );
		}

		update_user_meta( get_current_user_id(), 'skylearn_beta_notice_dismissed', true );
		wp_send_json_success();
	}

	/**
	 * Beta feedback page.
	 *
	 * @since 1.0.0-beta
	 */
	public function beta_feedback_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SkyLearn Flashcards - Beta Feedback', 'skylearn-flashcards' ); ?></h1>
			
			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin: 20px 0;">
				<h2>🧪 Thank You for Beta Testing!</h2>
				<p>Your feedback helps us create the best flashcard plugin for WordPress. Please share your experience, report bugs, and suggest improvements.</p>
			</div>

			<div class="beta-feedback-container" style="display: flex; gap: 20px;">
				<div style="flex: 2;">
					<div class="postbox">
						<h2 class="hndle">Quick Feedback Form</h2>
						<div class="inside">
							<form id="skylearn-beta-feedback-form">
								<?php wp_nonce_field( 'skylearn_beta_feedback', 'beta_feedback_nonce' ); ?>
								
								<table class="form-table">
									<tr>
										<th scope="row">Your Name</th>
										<td><input type="text" name="tester_name" class="regular-text" placeholder="Your name or organization" /></td>
									</tr>
									<tr>
										<th scope="row">Email</th>
										<td><input type="email" name="tester_email" class="regular-text" placeholder="your@email.com" /></td>
									</tr>
									<tr>
										<th scope="row">Overall Experience</th>
										<td>
											<select name="overall_rating">
												<option value="">Select rating...</option>
												<option value="excellent">Excellent</option>
												<option value="good">Good</option>
												<option value="fair">Fair</option>
												<option value="poor">Poor</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row">Feedback Type</th>
										<td>
											<label><input type="checkbox" name="feedback_type[]" value="bug"> Bug Report</label><br>
											<label><input type="checkbox" name="feedback_type[]" value="feature"> Feature Request</label><br>
											<label><input type="checkbox" name="feedback_type[]" value="ui"> UI/UX Feedback</label><br>
											<label><input type="checkbox" name="feedback_type[]" value="performance"> Performance Issue</label><br>
											<label><input type="checkbox" name="feedback_type[]" value="general"> General Feedback</label>
										</td>
									</tr>
									<tr>
										<th scope="row">Your Feedback</th>
										<td>
											<textarea name="feedback_message" rows="8" class="large-text" placeholder="Please share your detailed feedback, bug reports, feature requests, or suggestions..."></textarea>
										</td>
									</tr>
								</table>
								
								<p class="submit">
									<input type="submit" class="button-primary" value="Submit Feedback" />
								</p>
							</form>
						</div>
					</div>
				</div>

				<div style="flex: 1;">
					<div class="postbox">
						<h2 class="hndle">Other Ways to Provide Feedback</h2>
						<div class="inside">
							<h4>📧 Email Support</h4>
							<p><a href="mailto:support@skyian.com?subject=Beta Feedback">support@skyian.com</a></p>
							
							<h4>🐛 GitHub Issues</h4>
							<p><a href="https://github.com/Faiver55/skylearn-flashcards/issues" target="_blank">Report on GitHub</a></p>
							
							<h4>📋 Detailed Template</h4>
							<p><a href="https://github.com/Faiver55/skylearn-flashcards/blob/main/docs/FEEDBACK_TEMPLATE.md" target="_blank">Use our feedback template</a></p>
							
							<h4>📖 Beta Guide</h4>
							<p><a href="https://github.com/Faiver55/skylearn-flashcards/blob/main/docs/ONBOARDING.md" target="_blank">Beta testing guide</a></p>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle">System Information</h2>
						<div class="inside">
							<p><strong>Plugin Version:</strong> <?php echo esc_html( SKYLEARN_FLASHCARDS_VERSION ); ?></p>
							<p><strong>WordPress:</strong> <?php echo esc_html( get_bloginfo( 'version' ) ); ?></p>
							<p><strong>PHP:</strong> <?php echo esc_html( PHP_VERSION ); ?></p>
							<p><strong>Theme:</strong> <?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Beta settings page.
	 *
	 * @since 1.0.0-beta
	 */
	public function beta_settings_page() {
		if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['beta_settings_nonce'], 'skylearn_beta_settings' ) ) {
			$this->save_beta_settings();
		}

		$beta_settings = get_option( 'skylearn_flashcards_beta_settings', array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SkyLearn Flashcards - Beta Settings', 'skylearn-flashcards' ); ?></h1>
			
			<form method="post">
				<?php wp_nonce_field( 'skylearn_beta_settings', 'beta_settings_nonce' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">Debug Mode</th>
						<td>
							<label>
								<input type="checkbox" name="debug_mode" value="1" <?php checked( $beta_settings['debug_mode'] ?? false ); ?> />
								Enable enhanced debugging and logging
							</label>
							<p class="description">Provides detailed logs for troubleshooting issues during beta testing.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Feedback Widget</th>
						<td>
							<label>
								<input type="checkbox" name="feedback_widget_enabled" value="1" <?php checked( $beta_settings['feedback_widget_enabled'] ?? false ); ?> />
								Show feedback widget in admin
							</label>
							<p class="description">Displays a feedback widget on flashcard admin pages.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Performance Monitoring</th>
						<td>
							<label>
								<input type="checkbox" name="performance_monitoring" value="1" <?php checked( $beta_settings['performance_monitoring'] ?? false ); ?> />
								Enable performance monitoring
							</label>
							<p class="description">Tracks performance metrics to help optimize the plugin.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Experimental Features</th>
						<td>
							<label>
								<input type="checkbox" name="experimental_features" value="1" <?php checked( $beta_settings['experimental_features'] ?? false ); ?> />
								Enable experimental features
							</label>
							<p class="description"><strong>Warning:</strong> These features are unstable and for testing only.</p>
						</td>
					</tr>
				</table>
				
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save beta settings.
	 *
	 * @since 1.0.0-beta
	 */
	private function save_beta_settings() {
		$beta_settings = get_option( 'skylearn_flashcards_beta_settings', array() );
		
		$beta_settings['debug_mode'] = isset( $_POST['debug_mode'] );
		$beta_settings['feedback_widget_enabled'] = isset( $_POST['feedback_widget_enabled'] );
		$beta_settings['performance_monitoring'] = isset( $_POST['performance_monitoring'] );
		$beta_settings['experimental_features'] = isset( $_POST['experimental_features'] );
		
		update_option( 'skylearn_flashcards_beta_settings', $beta_settings );
		
		echo '<div class="notice notice-success"><p>Beta settings saved successfully!</p></div>';
	}

	/**
	 * Handle beta feedback submission.
	 *
	 * @since 1.0.0-beta
	 */
	public function handle_beta_feedback() {
		if ( ! wp_verify_nonce( $_POST['beta_feedback_nonce'], 'skylearn_beta_feedback' ) ) {
			wp_die( 'Security check failed' );
		}

		$feedback_data = array(
			'tester_name' => sanitize_text_field( $_POST['tester_name'] ?? '' ),
			'tester_email' => sanitize_email( $_POST['tester_email'] ?? '' ),
			'overall_rating' => sanitize_text_field( $_POST['overall_rating'] ?? '' ),
			'feedback_type' => array_map( 'sanitize_text_field', $_POST['feedback_type'] ?? array() ),
			'feedback_message' => sanitize_textarea_field( $_POST['feedback_message'] ?? '' ),
			'timestamp' => current_time( 'mysql' ),
			'wp_version' => get_bloginfo( 'version' ),
			'php_version' => PHP_VERSION,
			'plugin_version' => SKYLEARN_FLASHCARDS_VERSION,
			'theme' => wp_get_theme()->get( 'Name' ),
		);

		// Save feedback to database
		$feedback_entries = get_option( 'skylearn_flashcards_beta_feedback', array() );
		$feedback_entries[] = $feedback_data;
		update_option( 'skylearn_flashcards_beta_feedback', $feedback_entries );

		// Send email notification
		$this->send_feedback_email( $feedback_data );

		wp_send_json_success( array( 'message' => 'Thank you for your feedback!' ) );
	}

	/**
	 * Send feedback email notification.
	 *
	 * @since 1.0.0-beta
	 * @param array $feedback_data
	 */
	private function send_feedback_email( $feedback_data ) {
		$subject = '[SkyLearn Flashcards Beta] New feedback from ' . $feedback_data['tester_name'];
		
		$message = "New beta feedback received:\n\n";
		$message .= "Tester: " . $feedback_data['tester_name'] . "\n";
		$message .= "Email: " . $feedback_data['tester_email'] . "\n";
		$message .= "Rating: " . $feedback_data['overall_rating'] . "\n";
		$message .= "Types: " . implode( ', ', $feedback_data['feedback_type'] ) . "\n";
		$message .= "Plugin Version: " . $feedback_data['plugin_version'] . "\n";
		$message .= "WordPress: " . $feedback_data['wp_version'] . "\n";
		$message .= "PHP: " . $feedback_data['php_version'] . "\n";
		$message .= "Theme: " . $feedback_data['theme'] . "\n\n";
		$message .= "Feedback:\n" . $feedback_data['feedback_message'];

		wp_mail( 'support@skyian.com', $subject, $message );
	}

	/**
	 * Enqueue beta-specific scripts and styles.
	 *
	 * @since 1.0.0-beta
	 */
	public function enqueue_beta_scripts( $hook ) {
		if ( ! $this->is_beta_version() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'skylearn' ) === false ) {
			return;
		}

		// Add beta-specific JavaScript
		wp_add_inline_script( 'jquery', $this->get_beta_js() );
	}

	/**
	 * Get beta-specific JavaScript.
	 *
	 * @since 1.0.0-beta
	 * @return string
	 */
	private function get_beta_js() {
		return '
		jQuery(document).ready(function($) {
			// Handle beta feedback form submission
			$("#skylearn-beta-feedback-form").on("submit", function(e) {
				e.preventDefault();
				
				var formData = $(this).serialize();
				formData += "&action=skylearn_beta_feedback";
				
				$.post(ajaxurl, formData, function(response) {
					if (response.success) {
						alert("Thank you for your feedback!");
						$("#skylearn-beta-feedback-form")[0].reset();
					} else {
						alert("Error submitting feedback. Please try again.");
					}
				});
			});

			// Handle dismiss beta notice
			$(".skylearn-beta-notice").on("click", ".notice-dismiss", function() {
				$.post(ajaxurl, {
					action: "skylearn_dismiss_beta_notice",
					nonce: "' . wp_create_nonce( 'skylearn_beta_notice' ) . '"
				});
			});
		});
		';
	}

	/**
	 * Log beta events for debugging.
	 *
	 * @since 1.0.0-beta
	 * @param string $message
	 * @param array $context
	 */
	public function log_beta_event( $message, $context = array() ) {
		if ( ! $this->is_beta_version() ) {
			return;
		}

		$beta_settings = get_option( 'skylearn_flashcards_beta_settings', array() );
		if ( ! $beta_settings['debug_mode'] ?? false ) {
			return;
		}

		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'message' => $message,
			'context' => $context,
			'user_id' => get_current_user_id(),
			'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
		);

		error_log( '[SkyLearn Beta] ' . wp_json_encode( $log_entry ) );
	}

	/**
	 * Add beta dashboard widget.
	 *
	 * @since 1.0.0-beta
	 */
	public function add_beta_dashboard_widget() {
		if ( ! $this->is_beta_version() ) {
			return;
		}

		wp_add_dashboard_widget(
			'skylearn_beta_widget',
			'🧪 SkyLearn Flashcards Beta Testing',
			array( $this, 'beta_dashboard_widget' )
		);
	}

	/**
	 * Beta dashboard widget content.
	 *
	 * @since 1.0.0-beta
	 */
	public function beta_dashboard_widget() {
		$beta_settings = get_option( 'skylearn_flashcards_beta_settings', array() );
		$feedback_entries = get_option( 'skylearn_flashcards_beta_feedback', array() );
		$feedback_count = count( $feedback_entries );
		
		// Calculate testing progress
		$checklist_items = array(
			'plugin_activated' => true, // If we're here, it's activated
			'first_set_created' => $this->has_created_flashcard_sets(),
			'settings_configured' => $this->has_configured_settings(),
			'frontend_tested' => $this->has_tested_frontend(),
			'feedback_provided' => $feedback_count > 0,
		);
		
		$completed_items = array_filter( $checklist_items );
		$progress_percentage = round( ( count( $completed_items ) / count( $checklist_items ) ) * 100 );
		
		?>
		<div class="skylearn-beta-dashboard-widget">
			<div style="display: flex; align-items: center; margin-bottom: 15px;">
				<div style="flex: 1;">
					<h4 style="margin: 0; color: var(--skylearn-beta);">
						Welcome, Beta Tester!
					</h4>
					<p style="margin: 5px 0 0 0; color: #666;">
						Version: <?php echo esc_html( SKYLEARN_FLASHCARDS_VERSION ); ?>
					</p>
				</div>
				<div style="text-align: right;">
					<span class="skylearn-beta-badge">BETA</span>
				</div>
			</div>

			<div style="margin-bottom: 15px;">
				<h5 style="margin: 0 0 8px 0;">Testing Progress</h5>
				<div class="skylearn-progress-bar">
					<div class="skylearn-progress-fill" style="width: <?php echo esc_attr( $progress_percentage ); ?>%;"></div>
				</div>
				<small style="color: #666;"><?php echo esc_html( $progress_percentage ); ?>% complete</small>
			</div>

			<div style="margin-bottom: 15px;">
				<h5 style="margin: 0 0 8px 0;">Beta Checklist</h5>
				<ul class="beta-checklist" style="font-size: 13px;">
					<li class="<?php echo $checklist_items['plugin_activated'] ? 'completed' : ''; ?>">
						Plugin activated and running
					</li>
					<li class="<?php echo $checklist_items['first_set_created'] ? 'completed' : ''; ?>">
						Created your first flashcard set
					</li>
					<li class="<?php echo $checklist_items['settings_configured'] ? 'completed' : ''; ?>">
						Configured plugin settings
					</li>
					<li class="<?php echo $checklist_items['frontend_tested'] ? 'completed' : ''; ?>">
						Tested flashcards on frontend
					</li>
					<li class="<?php echo $checklist_items['feedback_provided'] ? 'completed' : ''; ?>">
						Provided beta feedback
					</li>
				</ul>
			</div>

			<div style="margin-bottom: 15px;">
				<h5 style="margin: 0 0 8px 0;">Quick Stats</h5>
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
					<div>
						<strong>Feedback Submitted:</strong><br>
						<?php echo esc_html( $feedback_count ); ?> entries
					</div>
					<div>
						<strong>Debug Mode:</strong><br>
						<?php echo $beta_settings['debug_mode'] ? '✅ Enabled' : '❌ Disabled'; ?>
					</div>
				</div>
			</div>

			<div style="display: flex; gap: 8px; flex-wrap: wrap;">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=skylearn_flashcard&page=skylearn-beta-feedback' ) ); ?>" 
				   class="button button-primary button-small">
					📝 Feedback
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=skylearn_flashcard&page=skylearn-beta-settings' ) ); ?>" 
				   class="button button-small">
					⚙️ Settings
				</a>
				<a href="https://github.com/Faiver55/skylearn-flashcards/blob/main/docs/ONBOARDING.md" 
				   target="_blank" class="button button-small">
					📖 Guide
				</a>
			</div>

			<?php if ( $progress_percentage < 100 ) : ?>
				<div class="beta-warning" style="margin-top: 15px; padding: 10px; font-size: 12px;">
					<h4 style="margin: 0 0 5px 0; font-size: 13px;">Next Steps</h4>
					<p style="margin: 0;">
						<?php if ( ! $checklist_items['first_set_created'] ) : ?>
							Create your first flashcard set to test core functionality.
						<?php elseif ( ! $checklist_items['settings_configured'] ) : ?>
							Explore the settings to customize the plugin for your needs.
						<?php elseif ( ! $checklist_items['frontend_tested'] ) : ?>
							Test your flashcards on the frontend using shortcodes or blocks.
						<?php elseif ( ! $checklist_items['feedback_provided'] ) : ?>
							Share your experience using our feedback form!
						<?php endif; ?>
					</p>
				</div>
			<?php else : ?>
				<div class="beta-success" style="margin-top: 15px; padding: 10px; font-size: 12px;">
					<h4 style="margin: 0 0 5px 0; font-size: 13px;">🎉 Great Job!</h4>
					<p style="margin: 0;">
						You've completed the basic beta testing checklist. Keep exploring and providing feedback!
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Check if user has created flashcard sets.
	 *
	 * @since 1.0.0-beta
	 * @return bool
	 */
	private function has_created_flashcard_sets() {
		$sets = get_posts( array(
			'post_type' => 'flashcard_set',
			'post_status' => array( 'publish', 'draft', 'private' ),
			'numberposts' => 1,
		) );
		return ! empty( $sets );
	}

	/**
	 * Check if user has configured settings.
	 *
	 * @since 1.0.0-beta
	 * @return bool
	 */
	private function has_configured_settings() {
		$settings = get_option( 'skylearn_flashcards_settings', array() );
		return ! empty( $settings );
	}

	/**
	 * Check if user has tested frontend.
	 *
	 * @since 1.0.0-beta
	 * @return bool
	 */
	private function has_tested_frontend() {
		// This could check for shortcode usage, page views, etc.
		// For now, we'll check if settings have been saved (indicating exploration)
		return $this->has_configured_settings();
	}
}