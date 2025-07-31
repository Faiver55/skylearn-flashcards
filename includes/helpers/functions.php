<?php
/**
 * Helper functions for SkyLearn Flashcards
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/helpers
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize flashcard data
 *
 * @since    1.0.0
 * @param    array    $flashcard_data    Raw flashcard data
 * @return   array                       Sanitized flashcard data
 */
if ( ! function_exists( 'skylearn_sanitize_flashcard_data' ) ) {
function skylearn_sanitize_flashcard_data( $flashcard_data ) {
	
	if ( ! is_array( $flashcard_data ) ) {
		return array();
	}

	$sanitized = array();
	
	foreach ( $flashcard_data as $card ) {
		$sanitized[] = array(
			'question' => wp_kses_post( $card['question'] ?? '' ),
			'answer'   => wp_kses_post( $card['answer'] ?? '' ),
			'hint'     => wp_kses_post( $card['hint'] ?? '' ),
			'difficulty' => sanitize_text_field( $card['difficulty'] ?? 'medium' ),
			'tags'     => array_map( 'sanitize_text_field', $card['tags'] ?? array() ),
		);
	}

	return $sanitized;
}
}

/**
 * Get flashcard set data
 *
 * @since    1.0.0
 * @param    int      $set_id    Flashcard set ID
 * @return   array               Flashcard set data
 */
if ( ! function_exists( 'skylearn_get_flashcard_set' ) ) {
function skylearn_get_flashcard_set( $set_id ) {
	
	$set_id = absint( $set_id );
	
	if ( ! $set_id ) {
		return false;
	}

	$post = get_post( $set_id );
	
	if ( ! $post || $post->post_type !== 'flashcard_set' ) {
		return false;
	}

	$flashcard_data = get_post_meta( $set_id, '_skylearn_flashcard_data', true );
	$settings = get_post_meta( $set_id, '_skylearn_flashcard_settings', true );

	return array(
		'id'          => $set_id,
		'title'       => get_the_title( $set_id ),
		'description' => get_the_content( null, false, $set_id ),
		'cards'       => skylearn_sanitize_flashcard_data( $flashcard_data ?: array() ),
		'settings'    => wp_parse_args( $settings ?: array(), skylearn_get_default_set_settings() ),
		'author'      => get_the_author_meta( 'display_name', $post->post_author ),
		'created'     => $post->post_date,
		'modified'    => $post->post_modified,
		'categories'  => wp_get_post_terms( $set_id, 'flashcard_category', array( 'fields' => 'names' ) ),
		'tags'        => wp_get_post_terms( $set_id, 'flashcard_tag', array( 'fields' => 'names' ) ),
	);
}
}

/**
 * Get default flashcard set settings
 *
 * @since    1.0.0
 * @return   array    Default settings
 */
function skylearn_get_default_set_settings() {
	
	return array(
		'show_progress'      => true,
		'show_hints'         => true,
		'shuffle_cards'      => false,
		'auto_advance'       => false,
		'flip_animation'     => 'flip',
		'theme'              => 'default',
		'primary_color'      => SKYLEARN_FLASHCARDS_COLOR_PRIMARY,
		'accent_color'       => SKYLEARN_FLASHCARDS_COLOR_ACCENT,
		'enable_lead_capture' => false,
		'lead_form_title'    => __( 'Want to learn more?', 'skylearn-flashcards' ),
		'lead_form_message'  => __( 'Enter your details to get more study materials.', 'skylearn-flashcards' ),
		'spaced_repetition'  => false,
		'difficulty_adjustment' => false,
		'max_cards_per_session' => 0, // 0 = unlimited
	);
}

/**
 * Check if premium features are available
 *
 * @since    1.0.0
 * @return   bool    True if premium is active, false otherwise
 */
// Note: skylearn_is_premium() is defined in includes/helpers.php

/**
 * Get plugin settings
 *
 * @since    1.0.0
 * @param    string   $key       Setting key (optional)
 * @param    mixed    $default   Default value if key not found
 * @return   mixed               Setting value or all settings
 */
if ( ! function_exists( 'skylearn_get_setting' ) ) {
function skylearn_get_setting( $key = '', $default = null ) {
	
	$settings = get_option( 'skylearn_flashcards_settings', array() );
	
	if ( empty( $key ) ) {
		return $settings;
	}
	
	return $settings[ $key ] ?? $default;
}
}

/**
 * Update plugin setting
 *
 * @since    1.0.0
 * @param    string   $key      Setting key
 * @param    mixed    $value    Setting value
 * @return   bool               True on success, false on failure
 */
function skylearn_update_setting( $key, $value ) {
	
	$settings = get_option( 'skylearn_flashcards_settings', array() );
	$settings[ $key ] = $value;
	
	return update_option( 'skylearn_flashcards_settings', $settings );
}

/**
 * Format time duration for display
 *
 * @since    1.0.0
 * @param    int      $seconds    Duration in seconds
 * @return   string              Formatted duration
 */
function skylearn_format_duration( $seconds ) {
	
	$seconds = absint( $seconds );
	
	if ( $seconds < 60 ) {
		return sprintf( _n( '%d second', '%d seconds', $seconds, 'skylearn-flashcards' ), $seconds );
	}
	
	$minutes = floor( $seconds / 60 );
	$remaining_seconds = $seconds % 60;
	
	if ( $minutes < 60 ) {
		if ( $remaining_seconds > 0 ) {
			return sprintf( 
				__( '%d minutes, %d seconds', 'skylearn-flashcards' ), 
				$minutes, 
				$remaining_seconds 
			);
		}
		return sprintf( _n( '%d minute', '%d minutes', $minutes, 'skylearn-flashcards' ), $minutes );
	}
	
	$hours = floor( $minutes / 60 );
	$remaining_minutes = $minutes % 60;
	
	if ( $remaining_minutes > 0 ) {
		return sprintf( 
			__( '%d hours, %d minutes', 'skylearn-flashcards' ), 
			$hours, 
			$remaining_minutes 
		);
	}
	
	return sprintf( _n( '%d hour', '%d hours', $hours, 'skylearn-flashcards' ), $hours );
}

/**
 * Calculate accuracy percentage
 *
 * @since    1.0.0
 * @param    int      $correct     Number of correct answers
 * @param    int      $total       Total number of attempts
 * @return   float                 Accuracy percentage
 */
function skylearn_calculate_accuracy( $correct, $total ) {
	
	$correct = absint( $correct );
	$total = absint( $total );
	
	if ( $total === 0 ) {
		return 0;
	}
	
	return round( ( $correct / $total ) * 100, 2 );
}

/**
 * Generate random session ID
 *
 * @since    1.0.0
 * @return   string    Random session ID
 */
if ( ! function_exists( 'skylearn_generate_session_id' ) ) {
function skylearn_generate_session_id() {
	
	return wp_generate_uuid4();
}
}

/**
 * Get user's IP address
 *
 * @since    1.0.0
 * @return   string    User's IP address
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
	
	return $_SERVER['REMOTE_ADDR'] ?? '';
}
}

// Capability helper functions moved to capability-helpers.php
// Load the capability helpers
require_once SKYLEARN_FLASHCARDS_PATH . 'includes/helpers/capability-helpers.php';

/**
 * Validate email address
 *
 * @since    1.0.0
 * @param    string   $email    Email address to validate
 * @return   bool               True if valid, false otherwise
 */
function skylearn_is_valid_email( $email ) {
	
	return is_email( $email );
}

/**
 * Get asset URL
 *
 * @since    1.0.0
 * @param    string   $path    Asset path relative to assets directory
 * @return   string            Full asset URL
 */
function skylearn_get_asset_url( $path ) {
	
	return SKYLEARN_FLASHCARDS_ASSETS . ltrim( $path, '/' );
}

/**
 * Get template file path
 *
 * @since    1.0.0
 * @param    string   $template    Template name
 * @param    string   $type        Template type (admin, frontend)
 * @return   string                Template file path
 */
function skylearn_get_template_path( $template, $type = 'frontend' ) {
	
	$template = sanitize_file_name( $template );
	$type = sanitize_key( $type );
	
	$path = SKYLEARN_FLASHCARDS_PATH . "includes/{$type}/views/{$template}.php";
	
	if ( file_exists( $path ) ) {
		return $path;
	}
	
	return false;
}

/**
 * Load template file
 *
 * @since    1.0.0
 * @param    string   $template    Template name
 * @param    array    $args        Arguments to pass to template
 * @param    string   $type        Template type (admin, frontend)
 * @return   void
 */
if ( ! function_exists( 'skylearn_load_template' ) ) {
function skylearn_load_template( $template, $args = array(), $type = 'frontend' ) {
	
	$template_path = skylearn_get_template_path( $template, $type );
	
	if ( $template_path ) {
		// Extract arguments as variables
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}
		
		include $template_path;
	}
}
}

/**
 * Log debug message
 *
 * @since    1.0.0
 * @param    string   $message    Message to log
 * @param    string   $level      Log level (info, warning, error)
 * @return   void
 */
if ( ! function_exists( 'skylearn_log' ) ) {
function skylearn_log( $message, $level = 'info' ) {
	
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}
	
	$timestamp = current_time( 'mysql' );
	$log_message = "[{$timestamp}] SkyLearn Flashcards [{$level}]: {$message}";
	
	error_log( $log_message );
}
}

/**
 * Array of supported file types for import/export
 *
 * @since    1.0.0
 * @return   array    Supported file types
 */
function skylearn_get_supported_import_types() {
	
	return array(
		'json' => array(
			'extension' => 'json',
			'mime_type' => 'application/json',
			'label'     => __( 'JSON File', 'skylearn-flashcards' ),
		),
		'csv' => array(
			'extension' => 'csv',
			'mime_type' => 'text/csv',
			'label'     => __( 'CSV File', 'skylearn-flashcards' ),
		),
		'txt' => array(
			'extension' => 'txt',
			'mime_type' => 'text/plain',
			'label'     => __( 'Text File', 'skylearn-flashcards' ),
		),
	);
}

/**
 * Convert flashcard data to specific format
 *
 * @since    1.0.0
 * @param    array    $flashcards    Flashcard data
 * @param    string   $format        Export format (json, csv, txt)
 * @return   string                  Formatted data
 */
function skylearn_export_flashcards_format( $flashcards, $format = 'json' ) {
	
	switch ( $format ) {
		case 'csv':
			$output = "Question,Answer,Hint,Difficulty\n";
			foreach ( $flashcards as $card ) {
				$output .= sprintf(
					'"%s","%s","%s","%s"' . "\n",
					str_replace( '"', '""', $card['question'] ?? '' ),
					str_replace( '"', '""', $card['answer'] ?? '' ),
					str_replace( '"', '""', $card['hint'] ?? '' ),
					$card['difficulty'] ?? 'medium'
				);
			}
			return $output;
			
		case 'txt':
			$output = '';
			foreach ( $flashcards as $index => $card ) {
				$output .= sprintf(
					"Card %d:\nQ: %s\nA: %s\n",
					$index + 1,
					$card['question'] ?? '',
					$card['answer'] ?? ''
				);
				if ( ! empty( $card['hint'] ) ) {
					$output .= "Hint: " . $card['hint'] . "\n";
				}
				$output .= "\n";
			}
			return $output;
			
		case 'json':
		default:
			return wp_json_encode( $flashcards, JSON_PRETTY_PRINT );
	}
}

/**
 * Schedule cleanup tasks
 *
 * @since    1.0.0
 * @return   void
 */
function skylearn_schedule_cleanup() {
	
	if ( ! wp_next_scheduled( 'skylearn_flashcards_daily_cleanup' ) ) {
		wp_schedule_event( time(), 'daily', 'skylearn_flashcards_daily_cleanup' );
	}
}

/**
 * Cleanup expired data
 *
 * @since    1.0.0
 * @return   void
 */
function skylearn_cleanup_expired_data() {
	
	global $wpdb;
	
	// Clean up old analytics data (older than 90 days)
	$analytics_table = $wpdb->prefix . 'skylearn_flashcard_analytics';
	$wpdb->query( 
		$wpdb->prepare(
			"DELETE FROM {$analytics_table} WHERE created_at < %s",
			gmdate( 'Y-m-d H:i:s', strtotime( '-90 days' ) )
		)
	);
	
	// Clean up orphaned progress data
	$progress_table = $wpdb->prefix . 'skylearn_flashcard_progress';
	$wpdb->query(
		"DELETE p FROM {$progress_table} p 
		 LEFT JOIN {$wpdb->posts} posts ON p.set_id = posts.ID 
		 WHERE posts.ID IS NULL"
	);
	
	skylearn_log( 'Expired data cleanup completed' );
}

// Schedule cleanup on plugin load
add_action( 'skylearn_flashcards_daily_cleanup', 'skylearn_cleanup_expired_data' );

/**
 * Get memory usage info
 *
 * @since    1.0.0
 * @return   array    Memory usage statistics
 */
function skylearn_get_memory_usage() {
	
	return array(
		'current' => memory_get_usage( true ),
		'peak'    => memory_get_peak_usage( true ),
		'limit'   => ini_get( 'memory_limit' ),
	);
}