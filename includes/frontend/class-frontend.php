<?php
/**
 * The public-facing functionality of the plugin
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/frontend
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing side
 * of the site.
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/frontend
 * @author     Ferdous Khalifa <support@skyian.com>
 */
class SkyLearn_Flashcards_Frontend {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 
			$this->plugin_name . '-frontend', 
			SKYLEARN_FLASHCARDS_ASSETS . 'css/frontend.css', 
			array(), 
			$this->version, 
			'all' 
		);

		wp_enqueue_style( 
			$this->plugin_name . '-colors', 
			SKYLEARN_FLASHCARDS_ASSETS . 'css/colors.css', 
			array(), 
			$this->version, 
			'all' 
		);

		// Add dynamic CSS for custom colors
		$this->add_dynamic_styles();

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 
			$this->plugin_name . '-frontend', 
			SKYLEARN_FLASHCARDS_ASSETS . 'js/frontend.js', 
			array( 'jquery' ), 
			$this->version, 
			false 
		);

		wp_enqueue_script( 
			$this->plugin_name . '-flashcard', 
			SKYLEARN_FLASHCARDS_ASSETS . 'js/flashcard.js', 
			array( 'jquery', $this->plugin_name . '-frontend' ), 
			$this->version, 
			false 
		);

		// Localize script for AJAX
		wp_localize_script( $this->plugin_name . '-frontend', 'skylearn_frontend', array(
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'skylearn_frontend_nonce' ),
			'strings'     => array(
				'loading'               => __( 'Loading...', 'skylearn-flashcards' ),
				'error'                 => __( 'An error occurred. Please try again.', 'skylearn-flashcards' ),
				'session_complete'      => __( 'Study session complete!', 'skylearn-flashcards' ),
				'confirm_reset'         => __( 'Are you sure you want to reset your progress?', 'skylearn-flashcards' ),
				'confirm_reset_progress' => __( 'Are you sure you want to reset your progress?', 'skylearn-flashcards' ),
				'all_correct'           => __( 'Excellent! You got all cards correct!', 'skylearn-flashcards' ),
				'study_again'           => __( 'Study Again', 'skylearn-flashcards' ),
				'next_card'             => __( 'Next Card', 'skylearn-flashcards' ),
				'previous_card'         => __( 'Previous Card', 'skylearn-flashcards' ),
				'flip_card'             => __( 'Click to flip', 'skylearn-flashcards' ),
				'skip_to_content'       => __( 'Skip to main content', 'skylearn-flashcards' ),
			),
		) );

	}

	/**
	 * Initialize frontend functionality
	 *
	 * @since    1.0.0
	 */
	public function init() {

		// Register post types and taxonomies
		$this->register_post_types();
		$this->register_taxonomies();

		// Add template redirect for preview functionality
		add_action( 'template_redirect', array( $this, 'handle_preview' ) );

	}

	/**
	 * Register shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {

		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-shortcode.php';
		$shortcode_handler = new SkyLearn_Flashcards_Shortcode( $this->plugin_name, $this->version );

		add_shortcode( 'skylearn_flashcards', array( $shortcode_handler, 'render_shortcode' ) );
		add_shortcode( 'skylearn_flashcard_set', array( $shortcode_handler, 'render_shortcode' ) ); // Alternative name

	}

	/**
	 * Register Gutenberg blocks
	 *
	 * @since    1.0.0
	 */
	public function register_blocks() {

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register flashcard block
		register_block_type( 'skylearn/flashcards', array(
			'attributes' => array(
				'setId' => array(
					'type' => 'number',
					'default' => 0,
				),
				'showProgress' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'shuffle' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'theme' => array(
					'type' => 'string',
					'default' => 'default',
				),
			),
			'render_callback' => array( $this, 'render_block' ),
		) );

	}

	/**
	 * Render Gutenberg block
	 *
	 * @since    1.0.0
	 * @param    array    $attributes    Block attributes
	 * @return   string                  Block HTML
	 */
	public function render_block( $attributes ) {

		$shortcode_atts = array();

		if ( ! empty( $attributes['setId'] ) ) {
			$shortcode_atts['id'] = $attributes['setId'];
		}

		if ( isset( $attributes['showProgress'] ) ) {
			$shortcode_atts['show_progress'] = $attributes['showProgress'] ? 'true' : 'false';
		}

		if ( isset( $attributes['shuffle'] ) ) {
			$shortcode_atts['shuffle'] = $attributes['shuffle'] ? 'true' : 'false';
		}

		if ( ! empty( $attributes['theme'] ) ) {
			$shortcode_atts['theme'] = $attributes['theme'];
		}

		// Use shortcode handler to render
		require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-shortcode.php';
		$shortcode_handler = new SkyLearn_Flashcards_Shortcode( $this->plugin_name, $this->version );

		return $shortcode_handler->render_shortcode( $shortcode_atts );

	}

	/**
	 * Add dynamic CSS based on settings
	 *
	 * @since    1.0.0
	 */
	private function add_dynamic_styles() {

		$settings = get_option( 'skylearn_flashcards_settings', array() );

		$primary_color = $settings['primary_color'] ?? SKYLEARN_FLASHCARDS_COLOR_PRIMARY;
		$accent_color = $settings['accent_color'] ?? SKYLEARN_FLASHCARDS_COLOR_ACCENT;
		$background_color = $settings['background_color'] ?? SKYLEARN_FLASHCARDS_COLOR_BACKGROUND;
		$text_color = $settings['text_color'] ?? SKYLEARN_FLASHCARDS_COLOR_TEXT;

		$custom_css = "
			:root {
				--skylearn-primary: {$primary_color};
				--skylearn-accent: {$accent_color};
				--skylearn-background: {$background_color};
				--skylearn-text: {$text_color};
			}
		";

		wp_add_inline_style( $this->plugin_name . '-frontend', $custom_css );

	}

	/**
	 * Register custom post types
	 *
	 * @since    1.0.0
	 */
	private function register_post_types() {

		// This is already done in the setup class, but we call it here
		// to ensure it's available on the frontend
		SkyLearn_Flashcards_Setup::register_post_types();

	}

	/**
	 * Register custom taxonomies
	 *
	 * @since    1.0.0
	 */
	private function register_taxonomies() {

		// This is already done in the setup class, but we call it here
		// to ensure it's available on the frontend
		SkyLearn_Flashcards_Setup::register_taxonomies();

	}

	/**
	 * Handle preview functionality
	 *
	 * @since    1.0.0
	 */
	public function handle_preview() {

		if ( ! isset( $_GET['skylearn_preview'] ) ) {
			return;
		}

		$set_id = absint( $_GET['skylearn_preview'] );

		if ( ! $set_id ) {
			wp_die( __( 'Invalid flashcard set ID.', 'skylearn-flashcards' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have permission to preview flashcards.', 'skylearn-flashcards' ) );
		}

		$flashcard_set = skylearn_get_flashcard_set( $set_id );

		if ( ! $flashcard_set ) {
			wp_die( __( 'Flashcard set not found.', 'skylearn-flashcards' ) );
		}

		// Load preview template
		$this->load_preview_template( $flashcard_set );
		exit;

	}

	/**
	 * Load preview template
	 *
	 * @since    1.0.0
	 * @param    array    $flashcard_set    Flashcard set data
	 */
	private function load_preview_template( $flashcard_set ) {

		$this->enqueue_styles();
		$this->enqueue_scripts();

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( sprintf( __( 'Preview: %s', 'skylearn-flashcards' ), $flashcard_set['title'] ) ); ?></title>
			<?php wp_head(); ?>
			<style>
				body { 
					font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
					margin: 0; 
					padding: 20px; 
					background: #f5f5f5; 
				}
				.skylearn-preview-header {
					text-align: center;
					margin-bottom: 30px;
					padding: 20px;
					background: white;
					border-radius: 8px;
					box-shadow: 0 2px 10px rgba(0,0,0,0.1);
				}
				.skylearn-preview-close {
					position: fixed;
					top: 20px;
					right: 20px;
					z-index: 9999;
				}
			</style>
		</head>
		<body class="skylearn-preview">
			
			<button onclick="window.close()" class="skylearn-preview-close skylearn-btn">
				<?php esc_html_e( 'Close Preview', 'skylearn-flashcards' ); ?>
			</button>
			
			<div class="skylearn-preview-header">
				<h1><?php echo esc_html( sprintf( __( 'Preview: %s', 'skylearn-flashcards' ), $flashcard_set['title'] ) ); ?></h1>
				<p><?php esc_html_e( 'This is how your flashcard set will appear to users.', 'skylearn-flashcards' ); ?></p>
			</div>
			
			<?php
			// Render the flashcard set
			require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-shortcode.php';
			$shortcode_handler = new SkyLearn_Flashcards_Shortcode( $this->plugin_name, $this->version );
			echo $shortcode_handler->render_shortcode( array( 'id' => $flashcard_set['id'] ) );
			?>
			
			<?php wp_footer(); ?>
		</body>
		</html>
		<?php

	}

	/**
	 * Track card view via AJAX
	 *
	 * @since    1.0.0
	 */
	public function track_card_view() {

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_frontend_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}

		$set_id = absint( $_POST['set_id'] ?? 0 );
		$card_index = absint( $_POST['card_index'] ?? 0 );

		if ( ! $set_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid set ID.', 'skylearn-flashcards' ) ) );
		}

		// Only track if analytics is enabled
		if ( ! skylearn_get_setting( 'enable_analytics', true ) ) {
			wp_send_json_success();
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'skylearn_flashcard_analytics';

		$result = $wpdb->insert(
			$table_name,
			array(
				'set_id'      => $set_id,
				'user_id'     => get_current_user_id(),
				'card_index'  => $card_index,
				'action'      => 'view',
				'session_id'  => skylearn_generate_session_id(),
				'ip_address'  => skylearn_get_user_ip(),
				'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to track view.', 'skylearn-flashcards' ) ) );
		}

	}

	/**
	 * Track completion via AJAX
	 *
	 * @since    1.0.0
	 */
	public function track_completion() {

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_frontend_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}

		$set_id = absint( $_POST['set_id'] ?? 0 );
		$accuracy = floatval( $_POST['accuracy'] ?? 0 );

		if ( ! $set_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid set ID.', 'skylearn-flashcards' ) ) );
		}

		// Only track if analytics is enabled
		if ( ! skylearn_get_setting( 'enable_analytics', true ) ) {
			wp_send_json_success();
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'skylearn_flashcard_analytics';

		$result = $wpdb->insert(
			$table_name,
			array(
				'set_id'      => $set_id,
				'user_id'     => get_current_user_id(),
				'card_index'  => -1, // -1 indicates completion event
				'action'      => 'complete',
				'accuracy'    => $accuracy,
				'session_id'  => skylearn_generate_session_id(),
				'ip_address'  => skylearn_get_user_ip(),
				'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%f', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			// Track completion in LMS if enabled
			if ( class_exists( 'SkyLearn_Flashcards_LMS_Manager' ) ) {
				$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
				$lms_manager->track_completion( $set_id, get_current_user_id(), $accuracy );
			}
			
			// Fire action hook for other integrations
			do_action( 'skylearn_flashcard_completed', get_current_user_id(), $set_id, $accuracy );
			
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to track completion.', 'skylearn-flashcards' ) ) );
		}

	}

	/**
	 * Submit lead via AJAX
	 *
	 * @since    1.0.0
	 */
	public function submit_lead() {

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'skylearn_lead_submit' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skylearn-flashcards' ) ) );
		}

		// Check if premium and lead capture is enabled
		if ( ! skylearn_is_premium() ) {
			wp_send_json_error( array( 
				'message' => __( 'Lead collection is a premium feature.', 'skylearn-flashcards' ),
				'upgrade_url' => SkyLearn_Flashcards_Premium::get_upgrade_url( 'lead_collection' )
			) );
		}

		$set_id = absint( $_POST['set_id'] ?? 0 );
		$name = sanitize_text_field( $_POST['lead_name'] ?? '' );
		$email = sanitize_email( $_POST['lead_email'] ?? '' );
		$consent = isset( $_POST['lead_consent'] ) && $_POST['lead_consent'] === 'on';
		$context = sanitize_text_field( $_POST['context'] ?? 'completion' );

		// Validate required fields
		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'skylearn-flashcards' ) ) );
		}

		if ( ! $consent ) {
			wp_send_json_error( array( 'message' => __( 'You must agree to receive emails to continue.', 'skylearn-flashcards' ) ) );
		}

		if ( ! $set_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid flashcard set.', 'skylearn-flashcards' ) ) );
		}

		// Prepare lead data
		$lead_data = array(
			'set_id'  => $set_id,
			'name'    => $name,
			'email'   => $email,
			'source'  => 'flashcard_' . $context,
			'tags'    => 'flashcard-lead,set-' . $set_id,
		);

		// Collect additional custom fields
		$additional_data = array();
		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, 'skylearn_field_' ) === 0 ) {
				$field_name = str_replace( 'skylearn_field_', '', $key );
				$additional_data[ $field_name ] = sanitize_text_field( $value );
			}
		}

		if ( ! empty( $additional_data ) ) {
			$lead_data['message'] = json_encode( $additional_data );
		}

		// Initialize leads manager
		$leads_manager = new SkyLearn_Flashcards_Leads( $this->plugin_name, $this->version );
		
		// Collect the lead
		$lead_id = $leads_manager->collect_lead( $lead_data );

		if ( $lead_id ) {
			// Generate study results/report
			$results = $this->generate_study_results( $set_id );
			
			// Send email with results (if configured)
			$this->send_study_results_email( $email, $name, $results );
			
			wp_send_json_success( array( 
				'message' => __( 'Thank you! Your study results have been sent to your email.', 'skylearn-flashcards' ),
				'lead_id' => $lead_id,
				'results' => $results
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save your information. Please try again.', 'skylearn-flashcards' ) ) );
		}

	}

	/**
	 * Generate study results for a flashcard set
	 *
	 * @since    1.0.0
	 * @param    int     $set_id    Flashcard set ID
	 * @return   array              Study results data
	 */
	private function generate_study_results( $set_id ) {
		
		global $wpdb;
		
		$analytics_table = $wpdb->prefix . 'skylearn_flashcard_analytics';
		$session_id = skylearn_generate_session_id();
		$user_id = get_current_user_id();
		
		// Get session analytics
		$session_analytics = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$analytics_table} 
			 WHERE set_id = %d AND session_id = %s 
			 ORDER BY created_at ASC",
			$set_id,
			$session_id
		), ARRAY_A );
		
		// Calculate metrics
		$total_cards = count( skylearn_get_flashcard_set( $set_id )['cards'] ?? array() );
		$cards_viewed = count( array_filter( $session_analytics, function( $record ) {
			return $record['action'] === 'view';
		} ) );
		
		$completion_record = array_filter( $session_analytics, function( $record ) {
			return $record['action'] === 'complete';
		} );
		$accuracy = ! empty( $completion_record ) ? array_values( $completion_record )[0]['accuracy'] : 0;
		
		$total_time = array_sum( array_column( $session_analytics, 'time_spent' ) );
		$avg_time_per_card = $cards_viewed > 0 ? $total_time / $cards_viewed : 0;
		
		return array(
			'set_id'             => $set_id,
			'set_title'          => get_the_title( $set_id ),
			'total_cards'        => $total_cards,
			'cards_completed'    => $cards_viewed,
			'completion_rate'    => $total_cards > 0 ? ( $cards_viewed / $total_cards ) * 100 : 0,
			'accuracy'           => $accuracy,
			'total_time'         => $total_time,
			'avg_time_per_card'  => $avg_time_per_card,
			'session_date'       => current_time( 'mysql' ),
			'recommendations'    => $this->generate_study_recommendations( $accuracy, $completion_rate ?? 0 ),
		);
		
	}

	/**
	 * Generate study recommendations based on performance
	 *
	 * @since    1.0.0
	 * @param    float   $accuracy         Accuracy percentage
	 * @param    float   $completion_rate  Completion percentage
	 * @return   array                     Recommendations array
	 */
	private function generate_study_recommendations( $accuracy, $completion_rate ) {
		
		$recommendations = array();
		
		if ( $accuracy < 70 ) {
			$recommendations[] = __( 'Consider reviewing the material again to improve understanding.', 'skylearn-flashcards' );
			$recommendations[] = __( 'Try studying in shorter, more frequent sessions.', 'skylearn-flashcards' );
		} elseif ( $accuracy >= 70 && $accuracy < 85 ) {
			$recommendations[] = __( 'Good progress! Review challenging cards to boost your score.', 'skylearn-flashcards' );
			$recommendations[] = __( 'Focus on the concepts you found most difficult.', 'skylearn-flashcards' );
		} else {
			$recommendations[] = __( 'Excellent work! You have a strong grasp of this material.', 'skylearn-flashcards' );
			$recommendations[] = __( 'Consider moving on to more advanced topics.', 'skylearn-flashcards' );
		}
		
		if ( $completion_rate < 100 ) {
			$recommendations[] = __( 'Try to complete all cards in the set for better results.', 'skylearn-flashcards' );
		}
		
		return $recommendations;
		
	}

	/**
	 * Send study results email to user
	 *
	 * @since    1.0.0
	 * @param    string  $email     User email
	 * @param    string  $name      User name
	 * @param    array   $results   Study results
	 * @return   bool               True if email sent successfully
	 */
	private function send_study_results_email( $email, $name, $results ) {
		
		$subject = sprintf( 
			__( 'Your Study Results for "%s"', 'skylearn-flashcards' ), 
			$results['set_title'] 
		);
		
		$message = $this->get_results_email_template( $name, $results );
		
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);
		
		return wp_mail( $email, $subject, $message, $headers );
		
	}

	/**
	 * Get email template for study results
	 *
	 * @since    1.0.0
	 * @param    string  $name      User name
	 * @param    array   $results   Study results
	 * @return   string             Email HTML content
	 */
	private function get_results_email_template( $name, $results ) {
		
		$template = '
		<html>
		<head>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: %1$s; color: white; padding: 20px; text-align: center; }
				.content { padding: 20px; background: #f9f9f9; }
				.stats { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
				.stat-item { margin: 10px 0; }
				.stat-label { font-weight: bold; color: %1$s; }
				.recommendations { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
				.footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1>Study Results</h1>
					<p>%2$s</p>
				</div>
				
				<div class="content">
					<h2>Hello %3$s!</h2>
					<p>Here are your detailed study results for <strong>%4$s</strong>:</p>
					
					<div class="stats">
						<h3>Performance Summary</h3>
						<div class="stat-item">
							<span class="stat-label">Cards Completed:</span> %5$d / %6$d (%7$.1f%%)
						</div>
						<div class="stat-item">
							<span class="stat-label">Accuracy:</span> %8$.1f%%
						</div>
						<div class="stat-item">
							<span class="stat-label">Study Time:</span> %9$s
						</div>
						<div class="stat-item">
							<span class="stat-label">Average Time per Card:</span> %10$s
						</div>
					</div>
					
					<div class="recommendations">
						<h3>Study Recommendations</h3>
						<ul>%11$s</ul>
					</div>
					
					<p>Keep up the great work with your studies!</p>
				</div>
				
				<div class="footer">
					<p>Powered by SkyLearn Flashcards | <a href="%12$s">%13$s</a></p>
				</div>
			</div>
		</body>
		</html>';
		
		$recommendations_html = '';
		foreach ( $results['recommendations'] as $recommendation ) {
			$recommendations_html .= '<li>' . esc_html( $recommendation ) . '</li>';
		}
		
		return sprintf(
			$template,
			SKYLEARN_FLASHCARDS_COLOR_PRIMARY, // %1$s - primary color
			esc_html( get_bloginfo( 'name' ) ), // %2$s - site name
			esc_html( $name ), // %3$s - user name
			esc_html( $results['set_title'] ), // %4$s - set title
			$results['cards_completed'], // %5$d - cards completed
			$results['total_cards'], // %6$d - total cards
			$results['completion_rate'], // %7$.1f - completion rate
			$results['accuracy'], // %8$.1f - accuracy
			$this->format_time( $results['total_time'] ), // %9$s - total time
			$this->format_time( $results['avg_time_per_card'] ), // %10$s - avg time
			$recommendations_html, // %11$s - recommendations
			esc_url( home_url() ), // %12$s - site URL
			esc_html( get_bloginfo( 'name' ) ) // %13$s - site name
		);
		
	}

	/**
	 * Format time duration for display
	 *
	 * @since    1.0.0
	 * @param    int     $seconds   Time in seconds
	 * @return   string             Formatted time string
	 */
	private function format_time( $seconds ) {
		
		if ( $seconds < 60 ) {
			return sprintf( __( '%d seconds', 'skylearn-flashcards' ), $seconds );
		} elseif ( $seconds < 3600 ) {
			return sprintf( __( '%d minutes', 'skylearn-flashcards' ), floor( $seconds / 60 ) );
		} else {
			$hours = floor( $seconds / 3600 );
			$minutes = floor( ( $seconds % 3600 ) / 60 );
			return sprintf( __( '%d hours %d minutes', 'skylearn-flashcards' ), $hours, $minutes );
		}
		
	}

	/**
	 * Send lead to configured email marketing service
	 *
	 * @since    1.0.0
	 * @param    string   $email    Email address
	 * @param    string   $name     Name
	 */
	private function send_to_email_service( $email, $name ) {

		$email_settings = get_option( 'skylearn_flashcards_email_settings', array() );
		$provider = $email_settings['provider'] ?? '';

		if ( empty( $provider ) || empty( $email_settings['api_key'] ) ) {
			return;
		}

		// Load appropriate integration class
		switch ( $provider ) {
			case 'mailchimp':
				if ( class_exists( 'SkyLearn_Flashcards_Mailchimp' ) ) {
					$integration = new SkyLearn_Flashcards_Mailchimp();
					$integration->add_subscriber( $email, $name, $email_settings );
				}
				break;
			case 'sendfox':
				if ( class_exists( 'SkyLearn_Flashcards_SendFox' ) ) {
					$integration = new SkyLearn_Flashcards_SendFox();
					$integration->add_subscriber( $email, $name, $email_settings );
				}
				break;
			case 'vbout':
				if ( class_exists( 'SkyLearn_Flashcards_Vbout' ) ) {
					$integration = new SkyLearn_Flashcards_Vbout();
					$integration->add_subscriber( $email, $name, $email_settings );
				}
				break;
		}

	}

}