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
 * This function ensures that the administrator role always has the
 * 'edit_skylearn_flashcards' capability, which is required to access
 * the plugin's admin pages and menu items. This provides a safety net
 * in case capabilities are removed or not properly set during activation.
 *
 * @since 1.0.0
 */
function skylearn_flashcards_ensure_admin_caps() {
	$role = get_role( 'administrator' );
	if ( $role && ! $role->has_cap( 'edit_skylearn_flashcards' ) ) {
		$role->add_cap( 'edit_skylearn_flashcards' );
	}
}

// Hook into admin_init to ensure capabilities are always present
add_action( 'admin_init', 'skylearn_flashcards_ensure_admin_caps' );