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
function skylearn_sanitize_flashcard_data( $data ) {
	// TODO: Implement flashcard data sanitization logic
	return array();
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
function skylearn_user_can_manage_flashcards() {
	// TODO: Implement capability checking logic
	return current_user_can( 'manage_options' );
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
function skylearn_log( $message, $level = 'info' ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( '[SkyLearn Flashcards %s] %s', strtoupper( $level ), print_r( $message, true ) ) );
	}
}

/**
 * Check if premium features are available
 *
 * @since 1.0.0
 * @return bool True if premium features are available
 */
function skylearn_is_premium() {
	// TODO: Implement premium license checking logic
	return false;
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