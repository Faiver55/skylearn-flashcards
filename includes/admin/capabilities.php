<?php
/**
 * Admin capability management functionality
 *
 * Ensures that administrators always have the required capabilities
 * to access SkyLearn Flashcards admin pages.
 *
 * @link       https://skyian.com/
 * @since      1.0.0
 *
 * @package    SkyLearn_Flashcards
 * @subpackage SkyLearn_Flashcards/includes/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Ensure administrators always have the required SkyLearn Flashcards capabilities.
 *
 * This function ensures that the administrator role always has all the
 * required capabilities to access the plugin's admin pages and menu items.
 * This provides a safety net in case capabilities are removed or not properly 
 * set during activation, and ensures capabilities are present on every admin load.
 *
 * Enhanced version that uses our capability helper functions for better reliability.
 *
 * @since 1.0.0
 */
function skylearn_flashcards_ensure_admin_caps() {
	// Use our enhanced capability helper
	skylearn_ensure_admin_capabilities();
	
	// Log any issues in debug mode
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! skylearn_current_user_has_any_capability() ) {
		skylearn_log_capability_warning( 'Current admin user lacks SkyLearn Flashcards capabilities after ensure_admin_caps' );
	}
}

// Hook into admin_init to ensure capabilities are always present
add_action( 'admin_init', 'skylearn_flashcards_ensure_admin_caps' );