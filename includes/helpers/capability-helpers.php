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
 * @return   bool    True if user can manage options, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_manage' ) ) {
function skylearn_current_user_can_manage() {
	return current_user_can( 'manage_options' );
}
}

/**
 * Check if current user can edit flashcards (editor-level access)
 *
 * @since    1.0.0
 * @return   bool    True if user can edit posts, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_edit' ) ) {
function skylearn_current_user_can_edit() {
	return current_user_can( 'edit_posts' );
}
}

/**
 * Check if current user can view analytics
 *
 * @since    1.0.0
 * @return   bool    True if user can edit posts, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_view_analytics' ) ) {
function skylearn_current_user_can_view_analytics() {
	return current_user_can( 'edit_posts' );
}
}

/**
 * Check if current user can manage leads (premium feature)
 *
 * @since    1.0.0
 * @return   bool    True if user can edit posts and premium is active, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_manage_leads' ) ) {
function skylearn_current_user_can_manage_leads() {
	return current_user_can( 'edit_posts' ) && skylearn_is_premium();
}
}

/**
 * Check if current user can export flashcards
 *
 * @since    1.0.0
 * @return   bool    True if user can edit posts, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_export' ) ) {
function skylearn_current_user_can_export() {
	return current_user_can( 'edit_posts' );
}
}

/**
 * Check if user can edit specific posts with proper capability mapping
 *
 * @since    1.0.0
 * @param    int      $post_id    Post ID to check (required for proper capability checking)
 * @param    string   $post_type  Post type (optional, will be determined from post if not provided)
 * @return   bool                 True if user can edit the specific post, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_edit_post' ) ) {
function skylearn_current_user_can_edit_post( $post_id = 0, $post_type = '' ) {
	// If no post ID provided, check general edit_posts capability
	if ( empty( $post_id ) ) {
		return current_user_can( 'edit_posts' );
	}

	// Validate post exists
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	// Use the post-specific edit capability with post ID for proper WordPress 6.1+ compatibility
	return current_user_can( 'edit_post', $post_id );
}
}

/**
 * Check if user can delete specific posts with proper capability mapping
 *
 * @since    1.0.0
 * @param    int      $post_id    Post ID to check (required for proper capability checking)
 * @param    string   $post_type  Post type (optional, will be determined from post if not provided)
 * @return   bool                 True if user can delete the specific post, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_delete_post' ) ) {
function skylearn_current_user_can_delete_post( $post_id = 0, $post_type = '' ) {
	// If no post ID provided, check general delete_posts capability
	if ( empty( $post_id ) ) {
		return current_user_can( 'delete_posts' );
	}

	// Validate post exists
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	// Use the post-specific delete capability with post ID for proper WordPress 6.1+ compatibility
	return current_user_can( 'delete_post', $post_id );
}
}

/**
 * Check if user can create new flashcard sets
 *
 * Checks both capability and set limits for free users.
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

	// Must have edit_posts capability
	if ( ! current_user_can( 'edit_posts' ) ) {
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
 * @return   bool    True if user can edit posts, false otherwise
 */
function skylearn_current_user_has_any_capability() {
	// Check if user has basic editing capability
	return current_user_can( 'edit_posts' );
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

/**
 * Check if user can read specific posts with proper capability mapping
 *
 * @since    1.0.0
 * @param    int      $post_id    Post ID to check (required for proper capability checking)
 * @param    string   $post_type  Post type (optional, will be determined from post if not provided)
 * @return   bool                 True if user can read the specific post, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_read_post' ) ) {
function skylearn_current_user_can_read_post( $post_id = 0, $post_type = '' ) {
	// If no post ID provided, check general read capability
	if ( empty( $post_id ) ) {
		return true; // Public posts are readable by everyone
	}

	// Validate post exists
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	// Use the post-specific read capability with post ID for proper WordPress 6.1+ compatibility
	return current_user_can( 'read_post', $post_id );
}
}