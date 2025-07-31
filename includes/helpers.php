<?php
/**
 * Helper and utility functions for SkyLearn Flashcards plugin
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Sanitize and validate flashcard data
 *
 * @since 1.0.0
 * @param array $data Raw flashcard data
 * @return array Sanitized flashcard data
 */
if ( ! function_exists( 'skylearn_sanitize_flashcard_data' ) ) {
function skylearn_sanitize_flashcard_data( $data ) {
	if ( ! is_array( $data ) ) {
		return array();
	}

	$sanitized = array();
	
	foreach ( $data as $card ) {
		if ( ! is_array( $card ) ) {
			continue;
		}
		
		$sanitized_card = array(
			'front' => wp_kses_post( trim( $card['front'] ?? '' ) ),
			'back'  => wp_kses_post( trim( $card['back'] ?? '' ) ),
		);
		
		// Only add cards that have both front and back content
		if ( ! empty( $sanitized_card['front'] ) && ! empty( $sanitized_card['back'] ) ) {
			$sanitized[] = $sanitized_card;
		}
	}
	
	return $sanitized;
}
}

/**
 * Get plugin branding colors
 *
 * @since 1.0.0
 * @return array Array of color values
 */
function skylearn_get_brand_colors() {
	return array(
		'primary'    => SKYLEARN_FLASHCARDS_COLOR_PRIMARY,
		'accent'     => SKYLEARN_FLASHCARDS_COLOR_ACCENT,
		'background' => SKYLEARN_FLASHCARDS_COLOR_BACKGROUND,
		'text'       => SKYLEARN_FLASHCARDS_COLOR_TEXT,
	);
}

/**
 * Get logo image URLs
 *
 * @since 1.0.0
 * @param string $type Logo type: 'horizontal' or 'icon'
 * @return string Logo image URL
 */
function skylearn_get_logo_url( $type = 'horizontal' ) {
	$logo_path = SKYLEARN_FLASHCARDS_LOGO;
	
	switch ( $type ) {
		case 'icon':
			return $logo_path . 'logo-icon.png'; // ![image2](image2)
		case 'horizontal':
		default:
			return $logo_path . 'logo-horiz.png'; // ![image1](image1)
	}
}

/**
 * Check if current user can manage flashcards
 *
 * @since 1.0.0
 * @return bool True if user can manage flashcards
 */
if ( ! function_exists( 'skylearn_current_user_can_manage' ) ) {
function skylearn_current_user_can_manage() {
	return current_user_can( 'manage_skylearn_flashcards' );
}
}

/**
 * Check if current user can edit flashcards
 *
 * @since 1.0.0
 * @return bool True if user can edit flashcards
 */
if ( ! function_exists( 'skylearn_current_user_can_edit' ) ) {
function skylearn_current_user_can_edit() {
	return current_user_can( 'edit_skylearn_flashcards' );
}
}

/**
 * Get flashcard set data by ID
 *
 * @since 1.0.0
 * @param int $set_id Flashcard set ID
 * @return array|false Flashcard set data or false if not found
 */
if ( ! function_exists( 'skylearn_get_flashcard_set' ) ) {
function skylearn_get_flashcard_set( $set_id ) {
	$post = get_post( $set_id );
	
	if ( ! $post || $post->post_type !== 'flashcard_set' ) {
		return false;
	}
	
	$cards = get_post_meta( $set_id, '_skylearn_flashcard_data', true );
	if ( ! is_array( $cards ) ) {
		$cards = array();
	}
	
	return array(
		'id'          => $set_id,
		'title'       => $post->post_title,
		'description' => $post->post_content,
		'author_id'   => $post->post_author,
		'cards'       => $cards,
		'card_count'  => count( $cards ),
		'status'      => $post->post_status,
		'created'     => $post->post_date,
		'modified'    => $post->post_modified,
	);
}
}

/**
 * Get user flashcard set count
 *
 * @since 1.0.0
 * @param int $user_id User ID (default: current user)
 * @return int Number of flashcard sets owned by user
 */
function skylearn_get_user_set_count( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	
	$query = new WP_Query( array(
		'post_type'      => 'flashcard_set',
		'author'         => $user_id,
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	
	return $query->found_posts;
}

/**
 * Check if user can create more flashcard sets (free limit: 5)
 *
 * @since 1.0.0
 * @param int $user_id User ID (default: current user)
 * @return bool True if user can create more sets
 */
function skylearn_user_can_create_set( $user_id = 0 ) {
	if ( skylearn_is_premium() ) {
		return true; // Premium users have unlimited sets
	}
	
	$current_count = skylearn_get_user_set_count( $user_id );
	$max_sets = apply_filters( 'skylearn_max_sets_free', 5 );
	
	return $current_count < $max_sets;
}

/**
 * Get plugin setting
 *
 * @since 1.0.0
 * @param string $key Setting key
 * @param mixed  $default Default value
 * @return mixed Setting value
 */
if ( ! function_exists( 'skylearn_get_setting' ) ) {
function skylearn_get_setting( $key, $default = null ) {
	$settings = get_option( 'skylearn_flashcards_settings', array() );
	return $settings[ $key ] ?? $default;
}
}

/**
 * Generate unique session ID for tracking
 *
 * @since 1.0.0
 * @return string Session ID
 */
if ( ! function_exists( 'skylearn_generate_session_id' ) ) {
function skylearn_generate_session_id() {
	if ( ! session_id() ) {
		session_start();
	}
	
	return session_id();
}
}

/**
 * Get user IP address
 *
 * @since 1.0.0
 * @return string IP address
 */
if ( ! function_exists( 'skylearn_get_user_ip' ) ) {
function skylearn_get_user_ip() {
	$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	
	foreach ( $ip_keys as $key ) {
		if ( array_key_exists( $key, $_SERVER ) === true ) {
			foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
				$ip = trim( $ip );
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
					return $ip;
				}
			}
		}
	}
	
	return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
}

/**
 * Load template file
 *
 * @since 1.0.0
 * @param string $template_name Template name (without .php extension)
 * @param array  $args Variables to pass to template
 * @param string $template_path Template subdirectory (admin/frontend)
 */
if ( ! function_exists( 'skylearn_load_template' ) ) {
function skylearn_load_template( $template_name, $args = array(), $template_path = 'frontend' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}
	
	$template_file = SKYLEARN_FLASHCARDS_PATH . "includes/{$template_path}/views/{$template_name}.php";
	
	if ( file_exists( $template_file ) ) {
		include $template_file;
	} else {
		skylearn_log( "Template not found: {$template_file}", 'warning' );
	}
}
}

/**
 * Format flashcard statistics for display
 *
 * @since 1.0.0
 * @param array $stats Raw statistics data
 * @return array Formatted statistics
 */
function skylearn_format_stats( $stats ) {
	// TODO: Implement statistics formatting logic
	return array();
}

/**
 * Get flashcard set permalink
 *
 * @since 1.0.0
 * @param int $set_id Flashcard set ID
 * @return string Permalink URL
 */
function skylearn_get_set_permalink( $set_id ) {
	// TODO: Implement permalink generation logic
	return '';
}

/**
 * Validate email address for lead collection
 *
 * @since 1.0.0
 * @param string $email Email address to validate
 * @return bool True if email is valid
 */
function skylearn_validate_email( $email ) {
	return is_email( $email );
}

/**
 * Get supported LMS plugins
 *
 * @since 1.0.0
 * @return array Array of supported LMS plugins
 */
function skylearn_get_supported_lms() {
	return array(
		'learndash' => array(
			'name'   => 'LearnDash',
			'active' => is_plugin_active( 'sfwd-lms/sfwd_lms.php' ),
		),
		'tutorlms'  => array(
			'name'   => 'TutorLMS',
			'active' => is_plugin_active( 'tutor/tutor.php' ),
		),
	);
}

/**
 * Get current plugin version
 *
 * @since 1.0.0
 * @return string Plugin version
 */
function skylearn_get_version() {
	return SKYLEARN_FLASHCARDS_VERSION;
}

/**
 * Log debug information (only when WP_DEBUG is enabled)
 *
 * @since 1.0.0
 * @param mixed  $message Message to log
 * @param string $level Log level: 'info', 'warning', 'error'
 */
if ( ! function_exists( 'skylearn_log' ) ) {
function skylearn_log( $message, $level = 'info' ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( '[SkyLearn Flashcards %s] %s', strtoupper( $level ), print_r( $message, true ) ) );
	}
}
}

/**
 * Check if premium features are available
 *
 * @since 1.0.0
 * @return bool True if premium features are available
 */
if ( ! function_exists( 'skylearn_is_premium' ) ) {
function skylearn_is_premium() {
	$license_status = get_option( 'skylearn_flashcards_license_status', 'inactive' );
	$license_key = get_option( 'skylearn_flashcards_license_key', '' );
	
	// Check if we have a valid license
	if ( $license_status === 'valid' && ! empty( $license_key ) ) {
		return true;
	}
	
	// Allow developers to override for testing
	if ( defined( 'SKYLEARN_FORCE_PREMIUM' ) && SKYLEARN_FORCE_PREMIUM ) {
		return true;
	}
	
	return false;
}
}

/**
 * Get default flashcard settings
 *
 * @since 1.0.0
 * @return array Default plugin settings
 */
function skylearn_get_default_settings() {
	return array(
		'primary_color'     => SKYLEARN_FLASHCARDS_COLOR_PRIMARY,
		'accent_color'      => SKYLEARN_FLASHCARDS_COLOR_ACCENT,
		'background_color'  => SKYLEARN_FLASHCARDS_COLOR_BACKGROUND,
		'text_color'        => SKYLEARN_FLASHCARDS_COLOR_TEXT,
		'enable_analytics'  => true,
		'enable_leads'      => false,
		'cards_per_page'    => 10,
		'show_progress'     => true,
		'auto_advance'      => false,
		'shuffle_cards'     => false,
	);
}

/**
 * Check if user has access to flashcard set based on LMS enrollment
 *
 * @since 1.0.0
 * @param int $flashcard_set_id Flashcard set ID
 * @param int $user_id User ID (optional, defaults to current user)
 * @return bool True if user has access
 */
function skylearn_user_has_lms_access( $flashcard_set_id, $user_id = null ) {
	if ( ! class_exists( 'SkyLearn_Flashcards_LMS_Manager' ) ) {
		return true; // No LMS integration, allow access
	}
	
	$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
	return $lms_manager->user_has_access( $flashcard_set_id, $user_id );
}

/**
 * Get LMS integrations status
 *
 * @since 1.0.0
 * @return array LMS status information
 */
function skylearn_get_lms_status() {
	if ( ! class_exists( 'SkyLearn_Flashcards_LMS_Manager' ) ) {
		return array(
			'enabled'      => false,
			'detected_lms' => array(),
		);
	}
	
	$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
	return array(
		'enabled'      => $lms_manager->is_lms_integration_enabled(),
		'detected_lms' => $lms_manager->get_detected_lms(),
	);
}

/**
 * Check if LearnDash is active and available
 *
 * @since 1.0.0
 * @return bool True if LearnDash is available
 */
function skylearn_is_learndash_available() {
	return class_exists( 'SFWD_LMS' );
}

/**
 * Check if TutorLMS is active and available
 *
 * @since 1.0.0
 * @return bool True if TutorLMS is available
 */
function skylearn_is_tutorlms_available() {
	return function_exists( 'tutor' );
}

/**
 * Track flashcard completion in LMS
 *
 * @since 1.0.0
 * @param int   $flashcard_set_id Flashcard set ID
 * @param int   $user_id User ID
 * @param float $accuracy Accuracy percentage
 */
function skylearn_track_lms_completion( $flashcard_set_id, $user_id, $accuracy ) {
	if ( ! class_exists( 'SkyLearn_Flashcards_LMS_Manager' ) ) {
		return;
	}
	
	$lms_manager = new SkyLearn_Flashcards_LMS_Manager();
	$lms_manager->track_completion( $flashcard_set_id, $user_id, $accuracy );
}

/**
 * Get flashcard sets linked to an LMS course or lesson
 *
 * @since 1.0.0
 * @param int    $lms_content_id Course or lesson ID
 * @param string $lms_type       LMS type ('learndash' or 'tutorlms')
 * @param string $content_type   Content type ('course' or 'lesson')
 * @return array Flashcard set IDs
 */
function skylearn_get_lms_linked_sets( $lms_content_id, $lms_type = 'learndash', $content_type = 'lesson' ) {
	$meta_key = '_skylearn_flashcard_sets';
	
	// For courses, we might use a different meta key
	if ( $content_type === 'course' && $lms_type === 'tutorlms' ) {
		$meta_key = '_skylearn_flashcard_sets';
	}
	
	$linked_sets = get_post_meta( $lms_content_id, $meta_key, true );
	
	if ( ! is_array( $linked_sets ) ) {
		return array();
	}
	
	// Filter out invalid set IDs
	return array_filter( array_map( 'absint', $linked_sets ) );
}

/**
 * Check if current installation has premium license
 *
 * @since 1.0.0
 * @return bool True if premium license is active
 */
if ( ! function_exists( 'skylearn_is_premium' ) ) {
function skylearn_is_premium() {
	// Check if license class exists and is valid
	if ( class_exists( 'SkyLearn_Flashcards_License' ) ) {
		$license = new SkyLearn_Flashcards_License();
		return $license->is_valid();
	}
	
	// Fallback: Check license status option directly
	$license_status = get_option( 'skylearn_flashcards_license_status', 'invalid' );
	return $license_status === 'valid';
}
}

/**
 * Check if specific premium feature is available
 *
 * @since 1.0.0
 * @param string $feature Feature name to check
 * @return bool True if feature is available
 */
if ( ! function_exists( 'skylearn_is_feature_available' ) ) {
function skylearn_is_feature_available( $feature ) {
	// Free features always available
	$free_features = array(
		'basic_flashcards',
		'shortcode_display',
		'gutenberg_block',
		'basic_analytics',
		'responsive_design'
	);
	
	if ( in_array( $feature, $free_features ) ) {
		return true;
	}
	
	// Premium features require valid license
	return skylearn_is_premium();
}
}

/**
 * Get premium upgrade URL for a specific feature
 *
 * @since 1.0.0
 * @param string $feature Feature name for tracking
 * @return string Upgrade URL
 */
if ( ! function_exists( 'skylearn_get_upgrade_url' ) ) {
function skylearn_get_upgrade_url( $feature = '' ) {
	$base_url = 'https://skyian.com/skylearn-flashcards/premium/';
	
	$utm_params = array(
		'utm_source'   => 'plugin',
		'utm_medium'   => 'upgrade-link',
		'utm_campaign' => 'skylearn-flashcards',
		'site_url'     => urlencode( home_url() ),
	);
	
	if ( ! empty( $feature ) ) {
		$utm_params['utm_content'] = sanitize_key( $feature );
	}
	
	return add_query_arg( $utm_params, $base_url );
}
}

/**
 * Display premium feature gate message
 *
 * @since 1.0.0
 * @param string $feature Feature name
 * @param string $context Context where the gate is shown
 */
if ( ! function_exists( 'skylearn_show_premium_gate' ) ) {
function skylearn_show_premium_gate( $feature, $context = 'general' ) {
	if ( skylearn_is_premium() ) {
		return;
	}
	
	$messages = array(
		'advanced_reporting' => __( 'Advanced analytics and detailed reporting are available in the Premium version.', 'skylearn-flashcards' ),
		'bulk_export'        => __( 'Bulk export and import functionality is available in the Premium version.', 'skylearn-flashcards' ),
		'unlimited_sets'     => __( 'Create unlimited flashcard sets with the Premium version.', 'skylearn-flashcards' ),
		'email_integration'  => __( 'Email marketing integrations are available in the Premium version.', 'skylearn-flashcards' ),
		'custom_branding'    => __( 'Custom branding and white-label options are available in the Premium version.', 'skylearn-flashcards' ),
	);
	
	$message = isset( $messages[ $feature ] ) ? $messages[ $feature ] : __( 'This feature is available in the Premium version.', 'skylearn-flashcards' );
	$upgrade_url = skylearn_get_upgrade_url( $feature );
	
	?>
	<div class="skylearn-premium-gate">
		<div class="skylearn-premium-gate-content">
			<h4><span class="dashicons dashicons-star-filled"></span> <?php _e( 'Premium Feature', 'skylearn-flashcards' ); ?></h4>
			<p><?php echo esc_html( $message ); ?></p>
			<p>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" target="_blank">
					<?php _e( 'Upgrade to Premium', 'skylearn-flashcards' ); ?>
				</a>
				<a href="https://skyian.com/skylearn-flashcards/features/" class="button" target="_blank">
					<?php _e( 'Learn More', 'skylearn-flashcards' ); ?>
				</a>
			</p>
		</div>
	</div>
	<?php
}
}