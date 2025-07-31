<?php
/**
 * Capability helper functions for SkyLearn Flashcards
 *
 * Provides defensive capability checking and prevents common capability errors.
 * Ensures WordPress 6.1+ compatibility by properly handling map_meta_cap changes.
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
 * Check if current user can manage flashcards (admin-level access)
 *
 * @since    1.0.0
 * @return   bool    True if user is logged in, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_manage' ) ) {
function skylearn_current_user_can_manage() {
	return is_user_logged_in();
}
}

/**
 * Check if current user can edit flashcards (editor-level access)
 *
 * @since    1.0.0
 * @return   bool    True if user is logged in, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_edit' ) ) {
function skylearn_current_user_can_edit() {
	return is_user_logged_in();
}
}

/**
 * Check if current user can view analytics
 *
 * @since    1.0.0
 * @return   bool    True if user is logged in, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_view_analytics' ) ) {
function skylearn_current_user_can_view_analytics() {
	return is_user_logged_in();
}
}

/**
 * Check if current user can manage leads (premium feature)
 *
 * @since    1.0.0
 * @return   bool    True if user is logged in and premium is active, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_manage_leads' ) ) {
function skylearn_current_user_can_manage_leads() {
	return is_user_logged_in() && skylearn_is_premium();
}
}

/**
 * Check if current user can export flashcards
 *
 * @since    1.0.0
 * @return   bool    True if user is logged in, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_export' ) ) {
function skylearn_current_user_can_export() {
	return is_user_logged_in();
}
}

/**
 * Safely check if user can edit a specific post
 *
 * This function provides WordPress 6.1+ compatibility by properly handling
 * the edit_post capability with post ID validation.
 *
 * @since    1.0.0
 * @param    int      $post_id    Post ID to check (optional)
 * @param    string   $post_type  Expected post type (optional)
 * @return   bool                 True if user can edit the post, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_edit_post' ) ) {
function skylearn_current_user_can_edit_post( $post_id = 0, $post_type = '' ) {
	// Must be logged in
	if ( ! is_user_logged_in() ) {
		return false;
	}

	// If no post ID provided, return true for logged-in users
	if ( empty( $post_id ) ) {
		return true;
	}

	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		skylearn_log_capability_warning( "skylearn_current_user_can_edit_post called with invalid post ID: {$post_id}" );
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		skylearn_log_capability_warning( "skylearn_current_user_can_edit_post called with non-existent post ID: {$post_id}" );
		return false;
	}

	// If post type specified, validate it matches
	if ( ! empty( $post_type ) && $post->post_type !== $post_type ) {
		skylearn_log_capability_warning( "skylearn_current_user_can_edit_post: expected post type '{$post_type}', got '{$post->post_type}'" );
		return false;
	}

	// For flashcard sets, check if user owns the post or use standard WordPress capability
	if ( $post->post_type === 'flashcard_set' ) {
		// Check if user owns the post or has edit capability
		if ( $post->post_author == get_current_user_id() || current_user_can( 'edit_others_posts' ) ) {
			return true;
		}
		
		// Fall back to standard capability check with post ID
		return current_user_can( 'edit_post', $post_id );
	}

	// For other post types, use standard WordPress capability check
	return current_user_can( 'edit_post', $post_id );
}
}

/**
 * Safely check if user can delete a specific post
 *
 * @since    1.0.0
 * @param    int      $post_id    Post ID to check
 * @param    string   $post_type  Expected post type (optional)
 * @return   bool                 True if user can delete the post, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_delete_post' ) ) {
function skylearn_current_user_can_delete_post( $post_id = 0, $post_type = '' ) {
	// Must be logged in
	if ( ! is_user_logged_in() ) {
		return false;
	}

	// If no post ID provided, return true for logged-in users
	if ( empty( $post_id ) ) {
		return true;
	}

	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	// If post type specified, validate it matches
	if ( ! empty( $post_type ) && $post->post_type !== $post_type ) {
		return false;
	}

	// For flashcard sets, check if user owns the post or use standard WordPress capability
	if ( $post->post_type === 'flashcard_set' ) {
		// Check if user owns the post or has delete capability
		if ( $post->post_author == get_current_user_id() || current_user_can( 'delete_others_posts' ) ) {
			return true;
		}
		
		// Fall back to standard capability check with post ID
		return current_user_can( 'delete_post', $post_id );
	}

	// For other post types, use standard WordPress capability check
	return current_user_can( 'delete_post', $post_id );
}
}

/**
 * Check if user can create new flashcard sets
 *
 * Checks both logged-in status and set limits for free users.
 *
 * @since    1.0.0
 * @param    int      $user_id    User ID to check (optional, defaults to current user)
 * @return   bool                 True if user can create sets, false otherwise
 */
if ( ! function_exists( 'skylearn_user_can_create_set' ) ) {
function skylearn_user_can_create_set( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Must be logged in
	if ( ! is_user_logged_in() ) {
		return false;
	}

	// Premium users have no limits
	if ( skylearn_is_premium() ) {
		return true;
	}

	// Check set limit for free users
	return skylearn_get_user_set_count( $user_id ) < 5;
}
}



/**
 * Prevent dangerous capability checks that could break in WP 6.1+
 *
 * This function should be called to validate capability function calls
 * and warn about potentially problematic usage.
 *
 * @since    1.0.0
 * @param    string   $capability    The capability being checked
 * @param    mixed    $args          Additional arguments passed to current_user_can
 * @return   bool                    True if the capability check is safe, false if problematic
 */
function skylearn_validate_capability_check( $capability, $args = null ) {
	// List of capabilities that require object IDs in WP 6.1+
	$object_capabilities = array(
		'edit_post',
		'delete_post',
		'read_post',
		'publish_post',
		'edit_page',
		'delete_page',
		'read_page',
		'publish_page',
	);

	// If it's an object capability but no ID provided, it's problematic
	if ( in_array( $capability, $object_capabilities, true ) && empty( $args ) ) {
		skylearn_log_capability_warning( "Potentially unsafe capability check: '{$capability}' called without object ID" );
		return false;
	}

	return true;
}

/**
 * Log capability warning messages
 *
 * @since    1.0.0
 * @param    string   $message    Warning message to log
 * @return   void
 */
function skylearn_log_capability_warning( $message ) {
	// Only log in debug mode
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}

	$caller = '';
	$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 3 );
	if ( isset( $backtrace[2] ) ) {
		$caller = $backtrace[2]['function'] ?? 'unknown';
		if ( isset( $backtrace[2]['class'] ) ) {
			$caller = $backtrace[2]['class'] . '::' . $caller;
		}
	}

	error_log( sprintf( 
		'[SkyLearn Flashcards Capability Warning] %s (called from: %s)', 
		$message, 
		$caller 
	) );
}

/**
 * Get all SkyLearn Flashcards capabilities (legacy)
 *
 * @since    1.0.0
 * @return   array    Empty array - capabilities no longer used
 */
function skylearn_get_plugin_capabilities() {
	// No longer using custom capabilities - return empty array for backwards compatibility
	return array();
}

/**
 * Check if current user has any SkyLearn Flashcards capabilities (legacy)
 *
 * @since    1.0.0
 * @return   bool    True if user is logged in, false otherwise
 */
function skylearn_current_user_has_any_capability() {
	// Simply check if user is logged in - no more custom capabilities
	return is_user_logged_in();
}

/**
 * Legacy admin capabilities function - no longer needed
 *
 * This is a safety net function that was used to ensure
 * admin users always have access to the plugin.
 *
 * @since    1.0.0
 * @return   bool    Always returns false - no capabilities to add
 */
function skylearn_ensure_admin_capabilities() {
	// No longer adding custom capabilities
	return false;
}

/**
 * Deprecated capability check wrapper with warning
 *
 * Use this to wrap any legacy capability checks and get warnings
 * when they're used improperly.
 *
 * @deprecated Use skylearn_current_user_can_edit_post() instead
 * @since    1.0.0
 * @param    string   $capability    Capability to check
 * @param    mixed    ...$args       Additional arguments
 * @return   bool                    Result of capability check
 */
function skylearn_deprecated_capability_check( $capability, ...$args ) {
	skylearn_validate_capability_check( $capability, $args );
	
	// Call the actual WordPress function
	return current_user_can( $capability, ...$args );
}