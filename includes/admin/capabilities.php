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
 * Legacy admin capability management functionality - no longer needed.
 *
 * The plugin now uses simple logged-in user checks instead of custom capabilities.
 * This function is kept for backwards compatibility but does nothing.
 *
 * @since 1.0.0
 */
function skylearn_flashcards_ensure_admin_caps() {
	// No longer using custom capabilities - all logged-in users can access features
	// Premium features are controlled by skylearn_is_premium() only
}

// Hook into admin_init to ensure capabilities are always present
add_action( 'admin_init', 'skylearn_flashcards_ensure_admin_caps' );