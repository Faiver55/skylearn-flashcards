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
 * @return   bool    True if user can manage flashcards, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_manage' ) ) {
function skylearn_current_user_can_manage() {
	return current_user_can( 'manage_skylearn_flashcards' ) || current_user_can( 'manage_options' );
}
}

/**
 * Check if current user can edit flashcards (editor-level access)
 *
 * @since    1.0.0
 * @return   bool    True if user can edit flashcards, false otherwise
 */
if ( ! function_exists( 'skylearn_current_user_can_edit' ) ) {
function skylearn_current_user_can_edit() {
	return current_user_can( 'edit_skylearn_flashcards' ) || skylearn_current_user_can_manage();
}
}

/**
 * Check if current user can view analytics
 *
 * @since    1.0.0
 * @return   bool    True if user can view analytics, false otherwise
 */
function skylearn_current_user_can_view_analytics() {
	return current_user_can( 'view_skylearn_analytics' ) || skylearn_current_user_can_manage();
}

/**
 * Check if current user can manage leads (premium feature)
 *
 * @since    1.0.0
 * @return   bool    True if user can manage leads, false otherwise
 */
function skylearn_current_user_can_manage_leads() {
	return current_user_can( 'manage_skylearn_leads' ) || skylearn_current_user_can_manage();
}

/**
 * Check if current user can export flashcards
 *
 * @since    1.0.0
 * @return   bool    True if user can export flashcards, false otherwise
 */
function skylearn_current_user_can_export() {
	return current_user_can( 'export_skylearn_flashcards' ) || skylearn_current_user_can_manage();
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
function skylearn_current_user_can_edit_post( $post_id = 0, $post_type = '' ) {
	// If no post ID provided, check general editing capability
	if ( empty( $post_id ) ) {
		skylearn_log_capability_warning( 'skylearn_current_user_can_edit_post called without post ID' );
		return skylearn_current_user_can_edit();
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

	// For flashcard sets, check our custom capability first
	if ( $post->post_type === 'flashcard_set' ) {
		// Check if user owns the post or has manage capability
		if ( $post->post_author == get_current_user_id() || skylearn_current_user_can_manage() ) {
			return true;
		}
		
		// Fall back to standard capability check with post ID
		return current_user_can( 'edit_post', $post_id );
	}

	// For other post types, use standard WordPress capability check
	return current_user_can( 'edit_post', $post_id );
}

/**
 * Safely check if user can delete a specific post
 *
 * @since    1.0.0
 * @param    int      $post_id    Post ID to check
 * @param    string   $post_type  Expected post type (optional)
 * @return   bool                 True if user can delete the post, false otherwise
 */
function skylearn_current_user_can_delete_post( $post_id = 0, $post_type = '' ) {
	// If no post ID provided, check general capability
	if ( empty( $post_id ) ) {
		skylearn_log_capability_warning( 'skylearn_current_user_can_delete_post called without post ID' );
		return current_user_can( 'delete_skylearn_flashcards' ) || skylearn_current_user_can_manage();
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

	// For flashcard sets, check our custom capability first
	if ( $post->post_type === 'flashcard_set' ) {
		// Check if user owns the post or has manage capability
		if ( $post->post_author == get_current_user_id() || skylearn_current_user_can_manage() ) {
			return true;
		}
		
		// Fall back to standard capability check with post ID
		return current_user_can( 'delete_post', $post_id );
	}

	// For other post types, use standard WordPress capability check
	return current_user_can( 'delete_post', $post_id );
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
function skylearn_user_can_create_set( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Check basic capability
	if ( ! skylearn_current_user_can_edit() ) {
		return false;
	}

	// Premium users have no limits
	if ( skylearn_is_premium() ) {
		return true;
	}

	// Check set limit for free users
	return skylearn_get_user_set_count( $user_id ) < 5;
}

/**
 * Get count of flashcard sets for a user
 *
 * @since    1.0.0
 * @param    int      $user_id    User ID (optional, defaults to current user)
 * @return   int                  Number of flashcard sets owned by user
 */
function skylearn_get_user_set_count( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$count = get_posts( array(
		'post_type'      => 'flashcard_set',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'author'         => $user_id,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	return is_array( $count ) ? count( $count ) : 0;
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
 * Get all SkyLearn Flashcards capabilities
 *
 * @since    1.0.0
 * @return   array    Array of capability names and descriptions
 */
function skylearn_get_plugin_capabilities() {
	return array(
		'manage_skylearn_flashcards' => __( 'Manage Flashcards (full admin access)', 'skylearn-flashcards' ),
		'edit_skylearn_flashcards'   => __( 'Edit Flashcards (create/edit sets)', 'skylearn-flashcards' ),
		'delete_skylearn_flashcards' => __( 'Delete Flashcards (delete sets)', 'skylearn-flashcards' ),
		'read_skylearn_flashcards'   => __( 'Read Flashcards (view private sets)', 'skylearn-flashcards' ),
		'view_skylearn_analytics'    => __( 'View Analytics (access reports)', 'skylearn-flashcards' ),
		'export_skylearn_flashcards' => __( 'Export Flashcards (download data)', 'skylearn-flashcards' ),
		'manage_skylearn_leads'      => __( 'Manage Leads (premium feature)', 'skylearn-flashcards' ),
	);
}

/**
 * Check if current user has any SkyLearn Flashcards capabilities
 *
 * @since    1.0.0
 * @return   bool    True if user has any plugin capabilities, false otherwise
 */
function skylearn_current_user_has_any_capability() {
	$capabilities = array_keys( skylearn_get_plugin_capabilities() );
	
	foreach ( $capabilities as $cap ) {
		if ( current_user_can( $cap ) ) {
			return true;
		}
	}
	
	// Check for general WordPress capabilities that should also work
	$general_caps = array( 'manage_options', 'edit_posts', 'publish_posts' );
	foreach ( $general_caps as $cap ) {
		if ( current_user_can( $cap ) ) {
			return true;
		}
	}
	
	return false;
}

/**
 * Ensure admin users have all required capabilities
 *
 * This is a safety net function that can be called to ensure
 * admin users always have access to the plugin.
 *
 * @since    1.0.0
 * @return   bool    True if capabilities were added, false if not needed
 */
function skylearn_ensure_admin_capabilities() {
	$admin_role = get_role( 'administrator' );
	if ( ! $admin_role ) {
		return false;
	}

	$capabilities = array_keys( skylearn_get_plugin_capabilities() );
	$added_any = false;

	foreach ( $capabilities as $cap ) {
		if ( ! $admin_role->has_cap( $cap ) ) {
			$admin_role->add_cap( $cap );
			$added_any = true;
			skylearn_log( "Added missing capability '{$cap}' to administrator role" );
		}
	}

	return $added_any;
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